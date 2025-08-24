<?php
// ==== Connexion à la base de données ====
session_start();
require_once './includes/db.php'; // Connexion PDO $pdo

// Sécurité : redirige si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php'); // ou login.php selon ton fichier
    exit();
}

// Récupération rôle utilisateur
$role = $_SESSION['user_role'] ?? 'user';

// ==== Export CSV (avec filtres) ====
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $mois  = $_GET['mois']  ?? '';
    $annee = $_GET['annee'] ?? '';

    $where  = [];
    $params = [];

    if ($mois !== '') {
        $where[] = 'MONTH(c.date_commande) = :mois';
        $params[':mois'] = (int)$mois;
    }
    if ($annee !== '') {
        $where[] = 'YEAR(c.date_commande) = :annee';
        $params[':annee'] = (int)$annee;
    }

    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    $sql = "SELECT c.*, cl.nom AS client_nom, cat.nom AS categorie_nom
            FROM commandes c
            JOIN clients cl ON c.client_id = cl.id
            JOIN categories cat ON c.categorie_id = cat.id
            $whereSql
            ORDER BY c.date_commande DESC";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v, PDO::PARAM_INT);
    }
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="commandes.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Date', 'Client', 'Catégorie', 'Quantité', 'PU', 'Remise', 'Total', 'Utilisateur', 'Description']);
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['date_commande'],
            $r['client_nom'],
            $r['categorie_nom'],
            $r['quantite'],
            $r['prix_unitaire'],
            $r['remise'],
            $r['total'],
            $r['utilisateur'],
            $r['description']
        ]);
    }
    fclose($out);
    exit;
}

// ==== Ajout d'une commande ====
if (isset($_POST['ajouter_commande'])) {
    $client = $_POST['client'];
    $categorie = $_POST['categorie'];
    $quantite = $_POST['quantite'];
    $prix_unitaire = $_POST['prix_unitaire'];
    $remise = $_POST['remise'];
    $utilisateur = $_SESSION['username'] ?? 'inconnu';
    $description = $_POST['description'];

    $total = ($prix_unitaire * $quantite) - $remise;

    $stmt = $pdo->prepare("INSERT INTO commandes (client_id, categorie_id, quantite, prix_unitaire, remise, total, utilisateur, description) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$client, $categorie, $quantite, $prix_unitaire, $remise, $total, $utilisateur, $description]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// ==== Suppression ====
if (isset($_GET['supprimer'])) {
    $id = $_GET['supprimer'];
    $pdo->prepare("DELETE FROM commandes WHERE id = ?")->execute([$id]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// ==== Modification ====
if (isset($_POST['modifier_commande'])) {
    $id = $_POST['id'];
    $client = $_POST['client'];
    $categorie = $_POST['categorie'];
    $quantite = $_POST['quantite'];
    $prix_unitaire = $_POST['prix_unitaire'];
    $remise = $_POST['remise'];
    $description = $_POST['description'];
    $total = ($prix_unitaire * $quantite) - $remise;

    $stmt = $pdo->prepare("UPDATE commandes SET client_id=?, categorie_id=?, quantite=?, prix_unitaire=?, remise=?, total=?, description=? WHERE id=?");
    $stmt->execute([$client, $categorie, $quantite, $prix_unitaire, $remise, $total, $description, $id]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// ==== Filtres ====
$mois  = $_GET['mois']  ?? '';
$annee = $_GET['annee'] ?? '';

$moisPadded = ($mois !== '') ? str_pad((int)$mois, 2, '0', STR_PAD_LEFT) : '';
$moisNoms = [
    '01' => 'Janvier',
    '02' => 'Février',
    '03' => 'Mars',
    '04' => 'Avril',
    '05' => 'Mai',
    '06' => 'Juin',
    '07' => 'Juillet',
    '08' => 'Août',
    '09' => 'Septembre',
    '10' => 'Octobre',
    '11' => 'Novembre',
    '12' => 'Décembre'
];

// ==== Pagination ====
$parPage = 4;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $parPage;

// Construit le WHERE pour filtres
$where  = [];
$params = [];
if ($mois !== '') {
    $where[] = 'MONTH(c.date_commande) = :mois';
    $params[':mois'] = (int)$mois;
}
if ($annee !== '') {
    $where[] = 'YEAR(c.date_commande)  = :annee';
    $params[':annee'] = (int)$annee;
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Total pour pagination
$sqlCount = "SELECT COUNT(*) FROM commandes c $whereSql";
$stmtCount = $pdo->prepare($sqlCount);
foreach ($params as $k => $v) {
    $stmtCount->bindValue($k, $v, PDO::PARAM_INT);
}
$stmtCount->execute();
$totalRows = (int)$stmtCount->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $parPage));

// Requête paginée
$sql = "SELECT c.*, cl.nom AS client_nom, cat.nom AS categorie_nom
        FROM commandes c
        JOIN clients cl ON c.client_id = cl.id
        JOIN categories cat ON c.categorie_id = cat.id
        $whereSql
        ORDER BY c.date_commande DESC
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v, PDO::PARAM_INT);
}
$stmt->bindValue(':limit',  $parPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
$stmt->execute();
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Données annexes
$clients = $pdo->query("SELECT * FROM clients")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/commande.css">
    <link rel="shortcut icon" href="./assets/icons/logo.ico" type="image/x-icon">
    <title>Liste des Commandes</title>

    <?php
    // on definit le titre a afficher dans l'onglet
    $titre = "Liste des Commandes";
    $currentPage = 'commandes';
    // insertion du header
    @include("./includes/header.php");
    ?>
    <div class="dashboard">
        <!-- Top Bar -->
        <?php
        // insertion du sidebar
        @include('./includes/sidebar.php');
        ?>

        <style>
            #exportType {
                background-color: #a3f3dfff;
                color: green;
                padding: 8px 14px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }

            a {
                text-decoration: none;
            }

            #export {

                background-color: #a6dce5ff;
                color: #2c7be5;
                padding: 8px 14px;
                border: none;
                border-radius: 4px;
                cursor: pointer;

            }

            .main h1 {

                margin: 20px 0;
            }

            .actions {
                align-items: center;
            }

            .filters {
                display: flex;
                gap: 15px;
            }

            .total {
                color: red;
                background-color: #fdd5d2ff;
                border: none;
                padding: 8px 14px;
                border-radius: 5px;
            }


            #desc {
                width: 100%;
                padding: 10px;
                margin-top: 5px;
                margin-bottom: 10px;
                border-radius: 5px;
                border: 1px solid #ccc;
                font-size: 14px;
            }

            .form-both {
                display: flex;
                justify-content: space-between;
                gap: 10px;
            }

            .details-data {
                padding: 10px 0;

            }

            .grise {
                color: gray;
            }

            .popup-form h3 {
                font-family: 'PlusJakartaSans-Medium';
                color: #2c7be5;
                font-size: 25px;

            }

            .popup-form {
                display: none;
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 10px;
                padding: 15px;
                width: 500px;
                /*largeur fixe ici*/
                z-index: 999;
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            }

            .popup-content{
                background:transparent;
            }

           

            .popup-form label {
                font-weight: bold;
                font-family: 'PlusJakartaSans-Medium';
                display: block;
                margin-top: 10px;
            }

            .popup-form input,
            .popup-form select {
                width: 100%;
                padding: 10px;
                margin-top: 5px;
                margin-bottom: 10px;
                border-radius: 5px;
                border: 1px solid #ccc;
                font-size: 14px;
                outline: hsl(203, 41%, 72%);
                font-family: 'PlusJakartaSans-Medium';
            }

       

            .price {
                display: flex;
                justify-content: space-between;
                gap: 10px;
            }

            .price-1 {
                flex: 1;
                margin: 0;
               
            }

            .form-buttons {
                text-align: right;
                margin-top: 10px;
            }

            .form-buttons button {
                padding: 8px 14px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }

            .details-data {
                padding: 10px 0;

            }

            .popup-content{
                width: 100%;
            }

            .popup-content h3 {
                margin-bottom: 20px;
            }

            .btn-cancel {
                background-color: #f44336;
                color: white;
                padding: 8px 10px;
                border: none;
                border-radius: 4px;
                margin-right: 10px;
                cursor: pointer;
            }

            .btn-submit {
                background-color: #2c7be5;
                color: white;
                padding: 8px 10px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }

            .btn-cancel:hover {
                background-color: #d32f2f;
            }

            .btn-submit:hover {
                background-color: #1b60c9;
            }

            .btn-add {
                background-color: #2c7be5;
                color: white;
                border: none;
                cursor: pointer;
                padding: 8px 10px;
                border-radius: 5px;
            }

            .btn-add:hover {
                background-color: #1a5ecd;
            }

            #add {
                color: #2c7be5;

            }

            label {
                color: #023544;
            }

            .table-commandes {
                min-width: 1000px;
            }

            /* Pagination */
            .pagination {
                display: flex;
                justify-content: center;
                gap: 6px;
                margin-top: 20px;
                flex-wrap: wrap;
            }

            .pagination a,
            .pagination span {
                padding: 6px 12px;
                border: 1px solid #ccc;
                border-radius: 5px;
                text-decoration: none;
                color: #333;
            }

            .pagination span.active {
                background: #2c7be5;
                color: #fff;
                font-weight: bold;
            }

            select {
                padding: 8px 0;
                border: none;
                border-radius: 4px;
                background-color: #f1f1f1ff;
            }
            .show p{
                padding: 10px 0;
            }
        </style>
</head>

<body>
    <div class="dashboard">


        <main class="main">
            <h1><?= $titre ?> </h1>
            <div class="actions">
                <button class="btn-add" onclick="document.getElementById('ajoutPopup').style.display='block'">+ Nouvelle commande</button>

                <div class="filters">
                    <label for="">Flitre les commandes :</label>
                    <select id="filterMonth">
                        <option value="">Tous les mois</option>
                        <?php
                        foreach ($moisNoms as $num => $label) {
                            $selected = ($moisPadded === $num) ? 'selected' : '';
                            echo "<option value=\"$num\" $selected>$label</option>";
                        }
                        ?>
                    </select>

                    <select id="filterYear">
                        <option value="">Toutes les années</option>
                        <?php
                        for ($y = 2024; $y <= date('Y'); $y++) {
                            $sel = ($annee == $y) ? 'selected' : '';
                            echo "<option value=\"$y\" $sel>$y</option>";
                        }
                        ?>
                    </select>
                </div>





                <p class="total">Total commandes : <strong><?= $totalRows ?></strong></p>
                <?php if ($role === 'admin'): ?>
                    <a href="?export=csv&mois=<?= urlencode($mois) ?>&annee=<?= urlencode($annee) ?>" id="export">Exporter CSV</a>
                <?php endif; ?>

            </div>


            <!-- Tableau -->
<div class="table-section">
    <table class="table-commandes">
        <thead>
            <tr>
                <th>Date</th>
                <th>Client</th>
                <th>Catégorie</th>
                <th>Quantité</th>
                <th>PU</th>
                <th>Remise</th>
                <th>Total</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($commandes as $cmd): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($cmd['date_commande'])) ?></td>
                <td><?= htmlspecialchars($cmd['client_nom']) ?></td>
                <td><?= htmlspecialchars($cmd['categorie_nom']) ?></td>
                <td><?= $cmd['quantite'] ?></td>
                <td><?= $cmd['prix_unitaire'] ?></td>
                <td><?= $cmd['remise'] ?></td>
                <td><?= $cmd['total'] ?></td>
                <td class="action-btn">
                    <a href="#" class="action-show" onclick="ouvrirPopupVoir(<?= $cmd['id'] ?>)">Afficher</a>

                    <?php if ($role === 'admin'): ?>
                    <a href="#" class="action-edit" onclick="ouvrirPopupModifier(<?= $cmd['id'] ?>)">Modifier</a>
                    <a href="?supprimer=<?= $cmd['id'] ?>" class="action-delete" onclick="return confirm('Confirmer ?')">Supprimer</a>
                    <?php else: ?>
                    <a href="#" class="action-edit" onclick="return alert('Vous ne pouvez pas modifier cette commande; Contactez votre administrateur...')">Modifier</a>
                    <a href="#" class="action-delete" onclick="return alert('Vous ne pouvez pas supprimer cette commande; Contactez votre administrateur...')">Supprimer</a>
                    <?php endif; ?>
                </td>
            </tr>

            <!-- Popup Modifier -->
            <div class="popup-form" id="modifierPopup<?= $cmd['id'] ?>">
                <form method="POST">
                    <h3>Modifier commande</h3>
                    <input type="hidden" name="id" value="<?= $cmd['id'] ?>">

                    <div class="price">
                        <div class="price-1">
                            <label>Client</label>
                            <select name="client" required>
                                <?php foreach ($clients as $cl): ?>
                                <option value="<?= $cl['id'] ?>" <?= ($cmd['client_id'] == $cl['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cl['nom']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="price-1">
                            <label>Catégorie</label>
                            <select name="categorie" onchange="updatePrixUnitaireEdit(this,'pu_edit<?= $cmd['id'] ?>')" required>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" data-prix="<?= $cat['prix_unitaire'] ?>" <?= ($cmd['categorie_id'] == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nom']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <label>Description</label>
                    <textarea id="desc" name="description"><?= htmlspecialchars($cmd['description']) ?></textarea>

                    <div class="price">
                        <div class="price-1">
                            <label>Quantité</label>
                            <input type="number" name="quantite" value="<?= $cmd['quantite'] ?>" required>
                        </div>
                        <div class="price-1">
                            <label>Prix unitaire</label>
                            <input type="number" id="pu_edit<?= $cmd['id'] ?>" name="prix_unitaire" value="<?= $cmd['prix_unitaire'] ?>" readonly required>
                        </div>
                    </div>

                    <label>Remise</label>
                    <input type="number" name="remise" value="<?= $cmd['remise'] ?>">

                    <div class="form-buttons">
                        <button type="button" class="btn-cancel" onclick="fermerPopup('modifierPopup<?= $cmd['id'] ?>')">Annuler</button>
                        <button type="submit" name="modifier_commande" class="btn-submit">📝 Modifier</button>
                    </div>
                </form>
            </div>

            <!-- Popup Voir -->
            <div class="popup-form" id="voirPopup<?= $cmd['id'] ?>">
                <div class="show">
                    <h3>Détails commande</h3>
                    <p><strong>Client :</strong> <?= htmlspecialchars($cmd['client_nom']) ?></p>
                    <p><strong>Catégorie :</strong> <?= htmlspecialchars($cmd['categorie_nom']) ?></p>
                    <p><strong>Quantité :</strong> <?= $cmd['quantite'] ?></p>
                    <p><strong>Prix unitaire :</strong> <?= $cmd['prix_unitaire'] ?></p>
                    <p><strong>Remise :</strong> <?= $cmd['remise'] ?></p>
                    <p><strong>Total :</strong> <?= $cmd['total'] ?></p>
                    <p><strong>Description :</strong> <?= htmlspecialchars($cmd['description']) ?></p>
                    <div class="form-buttons">
                        <button type="button" class="btn-cancel" onclick="fermerPopup('voirPopup<?= $cmd['id'] ?>')">❌ Fermer</button>
                    </div>
                </div>
            </div>

            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="pagination">
        <?php
        $baseParams = [];
        if ($mois !== '') $baseParams['mois'] = $mois;
        if ($annee !== '') $baseParams['annee'] = $annee;

        // Précédent
        if ($page > 1) {
            $p = $page - 1;
            $params = array_merge($baseParams, ['page' => $p]);
            echo "<a href='?" . http_build_query($params) . "'>« Précédent</a>";
        }
        // Pages
        $start = max(1, $page - 2);
        $end = min($totalPages, $page + 2);
        if ($start > 1) echo "<a href='?" . http_build_query(array_merge($baseParams, ['page' => 1])) . "'>1</a> ... ";
        for ($i = $start; $i <= $end; $i++) {
            if ($i == $page) echo "<span class='active'>$i</span>";
            else echo "<a href='?" . http_build_query(array_merge($baseParams, ['page' => $i])) . "'>$i</a>";
        }
        if ($end < $totalPages) echo " ... <a href='?" . http_build_query(array_merge($baseParams, ['page' => $totalPages])) . "'>$totalPages</a>";
        // Suivant
        if ($page < $totalPages) {
            $p = $page + 1;
            $params = array_merge($baseParams, ['page' => $p]);
            echo "<a href='?" . http_build_query($params) . "'>Suivant »</a>";
        }
        ?>
    </div>
</div>


    <!-- Popups et JS pour prix et filtres -->

    <!-- ==== POPUP AJOUTER COMMANDE ==== -->
    <div class="popup-form" id="ajoutPopup">
        <form method="POST" >
            <h3>Nouvelle commande</h3>

            <div class="price">
                <div class="price-1">
                    <label>Client</label>
                    <select name="client" required>
                        <option value="">--Sélectionner--</option>
                        <?php foreach ($clients as $cl): ?>
                            <option value="<?= $cl['id'] ?>"><?= htmlspecialchars($cl['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="price-1">
                    <label>Catégorie</label>
                    <select name="categorie" onchange="updatePrixUnitaire(this)" required>
                        <option value="">--Sélectionner--</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" data-prix="<?= $cat['prix_unitaire'] ?>">
                                <?= htmlspecialchars($cat['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <label>Description</label>
            <textarea id="desc" name="description" placeholder="Saisir une description..."> </textarea>

            <div class="price">
                <div class="price-1">
                    <label>Quantité</label>
                    <input type="number" name="quantite" required>
                </div>
                <div class="price-1">
                    <label>Prix unitaire</label>
                    <input type="number" name="prix_unitaire" id="pu" readonly required>
                </div>
            </div>


            <div class="price">
                <div class="price-1">
                    <label>Remise</label>
                    <input type="number" name="remise" value="0">
                </div>
                <div class="price-1">
                    <label>Utilisateur</label>
                    <input type="text" value="<?= $_SESSION['username'] ?>" readonly>


                </div>
            </div>

            <div class="form-buttons">

                <button type="button" class="btn-cancel" onclick="document.getElementById('ajoutPopup').style.display='none'">❌ Annuler</button>
                <button type="submit" class="btn-submit" name="ajouter_commande">✅ Enregistrer...</button>
            </div>

        </form>
    </div>




    <!-- Popup Voir -->
    <div  class="popup-form"  id="voirPopup<?= $cmd['id'] ?>">
        <div class="show">
            <h3>Détails commande</h3>
            <br>
            <p><strong>Client :</strong> <?= htmlspecialchars($cmd['client_nom']) ?></p>
            <p><strong>Catégorie :</strong> <?= htmlspecialchars($cmd['categorie_nom']) ?></p>
            <p><strong>Quantité :</strong> <?= $cmd['quantite'] ?></p>
            <p><strong>Prix unitaire :</strong> <?= $cmd['prix_unitaire'] ?></p>
            <p><strong>Remise :</strong> <?= $cmd['remise'] ?></p>
            <p><strong>Total :</strong> <?= $cmd['total'] ?></p>
            <p><strong>Description :</strong> <?= htmlspecialchars($cmd['description']) ?></p>
            <div class="form-buttons">
                <button type="button" class="btn-cancel" onclick="fermerPopup('voirPopup<?= $cmd['id'] ?>')">❌ Fermer</button>
            </div>
        </div>
    </div>



    <script>
        function updatePrixUnitaire(sel) {
            document.getElementById('pu').value = sel.options[sel.selectedIndex].dataset.prix;
        }

        function updatePrixUnitaireEdit(sel, id) {
            document.getElementById(id).value = sel.options[sel.selectedIndex].dataset.prix;
        }

        function ouvrirPopupVoir(id) {
            document.getElementById('voirPopup' + id).style.display = 'block';
        }

        function ouvrirPopupModifier(id) {
            document.getElementById('modifierPopup' + id).style.display = 'block';
        }

        function fermerPopup(id) {
            document.getElementById(id).style.display = 'none';
        }

        (function initFilters() {
            const m = "<?= htmlspecialchars($moisPadded) ?>";
            const y = "<?= htmlspecialchars($annee) ?>";
            if (m) document.getElementById('filterMonth').value = m;
            if (y) document.getElementById('filterYear').value = y;
        })();

        function applyFilters() {
            const sm = document.getElementById('filterMonth').value; // "01".."12" ou ""
            const sy = document.getElementById('filterYear').value; // "2024".."YYYY" ou ""
            const params = new URLSearchParams(window.location.search);
            params.set('page', '1'); // reset page
            if (sm) params.set('mois', sm);
            else params.delete('mois');
            if (sy) params.set('annee', sy);
            else params.delete('annee');
            window.location.search = params.toString();
        }
        document.getElementById('filterMonth').addEventListener('change', applyFilters);
        document.getElementById('filterYear').addEventListener('change', applyFilters);
    </script>
</body>

</html>