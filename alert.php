<?php
// ==== Connexion à la base de données ====
session_start();
require_once './includes/db.php'; // Connexion PDO $pdo

// Sécurité : redirection si non connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$role = $_SESSION['user_role'] ?? 'user';
$message = "";

// ==== Traitement - Ajout d'une commande ====
if (isset($_POST['ajouter_commande'])) {
    $client = $_POST['client'];
    $categorie = $_POST['categorie'];
    $quantite = $_POST['quantite'];
    $prix_unitaire = $_POST['prix_unitaire'];
    $remise = $_POST['remise'];
    $description = $_POST['description'];
    $total = ($prix_unitaire * $quantite) - $remise;
    $utilisateur = $_SESSION['username'] ?? 'inconnu';

    $stmt = $pdo->prepare("INSERT INTO commandes (client_id, categorie_id, quantite, prix_unitaire, remise, total, utilisateur, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$client, $categorie, $quantite, $prix_unitaire, $remise, $total, $utilisateur, $description]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// ==== Traitement - Modification ====
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

// ==== Traitement - Suppression ====
if (isset($_GET['supprimer'])) {
    $id = $_GET['supprimer'];
    $pdo->prepare("DELETE FROM commandes WHERE id=?")->execute([$id]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// ==== Filtrage ====
$filterMonth = $_GET['month'] ?? '';
$filterYear = $_GET['year'] ?? '';

$where = [];
$params = [];
if ($filterMonth) { $where[] = "MONTH(c.date_commande)=?"; $params[] = $filterMonth; }
if ($filterYear) { $where[] = "YEAR(c.date_commande)=?"; $params[] = $filterYear; }
$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

// ==== Pagination ====
$page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// ==== Récupération commandes paginées ====
$stmt = $pdo->prepare("
    SELECT c.*, cl.nom AS client_nom, cat.nom AS categorie_nom
    FROM commandes c
    JOIN clients cl ON c.client_id = cl.id
    JOIN categories cat ON c.categorie_id = cat.id
    $whereSql
    ORDER BY c.date_commande DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$commandes = $stmt->fetchAll();

// ==== Nombre total pour pagination ====
$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM commandes c $whereSql");
$stmtTotal->execute($params);
$total = $stmtTotal->fetchColumn();
$totalPages = ceil($total/$perPage);

// ==== Récupération clients et catégories ====
$clients = $pdo->query("SELECT * FROM clients")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="./css/style.css">
<link rel="stylesheet" href="./css/commande.css">
<link rel="shortcut icon" href="./assets/icons/icon.png" type="image/x-icon">
<title>Liste des Commandes</title>
<style>
/* Styles simplifiés pour popups et tableau */
.popup-form { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:#fff; border:1px solid #ddd; border-radius:10px; padding:15px; width:500px; z-index:999; box-shadow:0 0 15px rgba(0,0,0,0.2);}
.btn-add, .btn-submit, .btn-cancel { padding:8px 14px; border:none; border-radius:5px; cursor:pointer; }
.btn-add { background:#2c7be5; color:#fff; }
.btn-submit { background:#2c7be5; color:#fff; }
.btn-cancel { background:#f44336; color:#fff; }
.actions { display:flex; gap:10px; align-items:center; margin-bottom:15px;}
.filters { display:flex; gap:10px;}
.table-commandes { width:100%; border-collapse:collapse; margin-top:10px;}
.table-commandes th, .table-commandes td { border:1px solid #ccc; padding:8px; text-align:left;}
.pagination a { margin:0 5px; text-decoration:none;}
.pagination a[style*="font-weight:bold"] { font-weight:bold; }
</style>
</head>
<body>
<div class="dashboard">
<main class="main">
<h1>Liste des Commandes</h1>

<div class="actions">
<button class="btn-add" onclick="document.getElementById('ajoutPopup').style.display='block'">+ Nouvelle commande</button>

<div class="filters">
<div>
<label for="filterMonth">Mois :</label>
<select id="filterMonth">
<option value="">Tous</option>
<?php for($m=1;$m<=12;$m++):
$mo = str_pad($m,2,'0',STR_PAD_LEFT); 
$sel = ($filterMonth==$mo)?'selected':'';?>
<option value="<?= $mo ?>" <?= $sel ?>><?= $mo ?></option>
<?php endfor; ?>
</select>
</div>
<div>
<label for="filterYear">Année :</label>
<select id="filterYear">
<option value="">Toutes</option>
<?php for($y=2023;$y<=date('Y');$y++): 
$sel = ($filterYear==$y)?'selected':'';?>
<option value="<?= $y ?>" <?= $sel ?>><?= $y ?></option>
<?php endfor; ?>
</select>
</div>
<button onclick="applyFilter()">Filtrer</button>
</div>

<p>Total commandes: <strong><?= $total ?></strong></p>

<?php if($role==='admin'): ?>
<button onclick="exportCSV()">Exporter CSV</button>
<?php endif; ?>
</div>

<table class="table-commandes">
<thead>
<tr><th>Date</th><th>Client</th><th>Catégorie</th><th>Qté</th><th>PU</th><th>Remise</th><th>Total</th><th>Actions</th></tr>
</thead>
<tbody>
<?php foreach($commandes as $cmd): ?>
<tr>
<td><?= date('d/m/Y', strtotime($cmd['date_commande'])) ?></td>
<td><?= htmlspecialchars($cmd['client_nom']) ?></td>
<td><?= htmlspecialchars($cmd['categorie_nom']) ?></td>
<td><?= $cmd['quantite'] ?></td>
<td><?= $cmd['prix_unitaire'] ?></td>
<td><?= $cmd['remise'] ?></td>
<td><?= $cmd['total'] ?></td>
<td>
<a href="#" onclick="ouvrirPopupVoir(<?= $cmd['id'] ?>)">Afficher</a>
<a href="#" onclick="ouvrirPopupModifier(<?= $cmd['id'] ?>)">Modifier</a>
<a href="?supprimer=<?= $cmd['id'] ?>" onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
</td>
</tr>

<!-- Popup Voir -->
<div class="popup-form" id="voirPopup<?= $cmd['id'] ?>">
<div>
<h3>Détails</h3>
<p><strong>Client:</strong> <?= htmlspecialchars($cmd['client_nom']) ?></p>
<p><strong>Catégorie:</strong> <?= htmlspecialchars($cmd['categorie_nom']) ?></p>
<p><strong>Qté:</strong> <?= $cmd['quantite'] ?></p>
<p><strong>PU:</strong> <?= $cmd['prix_unitaire'] ?></p>
<p><strong>Remise:</strong> <?= $cmd['remise'] ?></p>
<p><strong>Total:</strong> <?= $cmd['total'] ?></p>
<p><strong>Description:</strong> <?= htmlspecialchars($cmd['description']) ?></p>
<p><strong>Utilisateur:</strong> <?= htmlspecialchars($cmd['utilisateur']) ?></p>
<p><strong>Date:</strong> <?= $cmd['date_commande'] ?></p>
<button onclick="window.print()">Imprimer</button>
<button class="btn-cancel" onclick="fermerPopup('voirPopup<?= $cmd['id'] ?>')">Fermer</button>
</div>
</div>

<!-- Popup Modifier -->
<div class="popup-form" id="modifierPopup<?= $cmd['id'] ?>">
<form method="POST">
<h3>Modifier</h3>
<input type="hidden" name="id" value="<?= $cmd['id'] ?>">
<label>Client</label>
<select name="client">
<?php foreach($clients as $cl): ?>
<option value="<?= $cl['id'] ?>" <?= ($cl['id']==$cmd['client_id'])?'selected':'' ?>><?= htmlspecialchars($cl['nom']) ?></option>
<?php endforeach; ?>
</select>
<label>Catégorie</label>
<select name="categorie">
<?php foreach($categories as $cat): ?>
<option value="<?= $cat['id'] ?>" <?= ($cat['id']==$cmd['categorie_id'])?'selected':'' ?> data-prix="<?= $cat['prix_unitaire'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
<?php endforeach; ?>
</select>
<label>Quantité</label>
<input type="number" name="quantite" value="<?= $cmd['quantite'] ?>" required>
<label>PU</label>
<input type="number" name="prix_unitaire" value="<?= $cmd['prix_unitaire'] ?>" readonly>
<label>Remise</label>
<input type="number" name="remise" value="<?= $cmd['remise'] ?>">
<label>Description</label>
<textarea name="description"><?= htmlspecialchars($cmd['description']) ?></textarea>
<div>
<button type="button" class="btn-cancel" onclick="fermerPopup('modifierPopup<?= $cmd['id'] ?>')">Annuler</button>
<button type="submit" class="btn-submit" name="modifier_commande">Modifier</button>
</div>
</form>
</div>

<?php endforeach; ?>
</tbody>
</table>

<!-- Pagination -->
<div class="pagination">
<?php for($p=1;$p<=$totalPages;$p++): ?>
<a href="?page=<?= $p ?>&month=<?= $filterMonth ?>&year=<?= $filterYear ?>" <?= ($p==$page)?'style="font-weight:bold"':'' ?>><?= $p ?></a>
<?php endfor; ?>
</div>

<!-- Popup Ajouter -->
<div class="popup-form" id="ajoutPopup">
<form method="POST">
<h3>Nouvelle commande</h3>
<label>Client</label>
<select name="client" required>
<option value="">--Sélectionner--</option>
<?php foreach($clients as $cl): ?>
<option value="<?= $cl['id'] ?>"><?= htmlspecialchars($cl['nom']) ?></option>
<?php endforeach; ?>
</select>
<label>Catégorie</label>
<select name="categorie" onchange="updatePrixUnitaire(this)" required>
<option value="">--Sélectionner--</option>
<?php foreach($categories as $cat): ?>
<option value="<?= $cat['id'] ?>" data-prix="<?= $cat['prix_unitaire'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
<?php endforeach; ?>
</select>
<label>Quantité</label>
<input type="number" name="quantite" required>
<label>PU</label>
<input type="number" name="prix_unitaire" id="pu" readonly required>
<label>Remise</label>
<input type="number" name="remise" value="0">
<label>Description</label>
<textarea name="description"></textarea>
<div>
<button type="button" class="btn-cancel" onclick="document.getElementById('ajoutPopup').style.display='none'">Annuler</button>
<button type="submit" class="btn-submit" name="ajouter_commande">Ajouter</button>
</div>
</form>
</div>

</main>
</div>

<script>
// Filtrage
function applyFilter(){
    const month = document.getElementById('filterMonth').value;
    const year = document.getElementById('filterYear').value;
    window.location.href = `?month=${month}&year=${year}`;
}

// Export CSV
function exportCSV(){
    const month = document.getElementById('filterMonth').value;
    const year = document.getElementById('filterYear').value;
    window.location.href = `?export_csv=1&month=${month}&year=${year}`;
}

// Popups
function ouvrirPopupVoir(id){ document.getElementById('voirPopup'+id).style.display='block'; }
function ouvrirPopupModifier(id){ document.getElementById('modifierPopup'+id).style.display='block'; }
function fermerPopup(id){ document.getElementById(id).style.display='none'; }

// PU dynamique
function updatePrixUnitaire(select){ document.getElementById('pu').value = select.options[select.selectedIndex].dataset.prix; }

// ==== Export CSV si demandé ====
<?php
if(isset($_GET['export_csv']) && $role==='admin'){
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=commandes.csv');
    $stmt = $pdo->prepare("
        SELECT c.*, cl.nom AS client_nom, cat.nom AS categorie_nom
        FROM commandes c
        JOIN clients cl ON c.client_id = cl.id
        JOIN categories cat ON c.categorie_id = cat.id
        $whereSql
        ORDER BY c.date_commande DESC
    ");
    $stmt->execute($params);
    $cmds = $stmt->fetchAll();

    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF"); // BOM UTF-8
    fputcsv($output, ['Date','Client','Catégorie','Quantité','PU','Remise','Total','Utilisateur','Description']);
    foreach($cmds as $c){
        fputcsv($output, [$c['date_commande'],$c['client_nom'],$c['categorie_nom'],$c['quantite'],$c['prix_unitaire'],$c['remise'],$c['total'],$c['utilisateur'],$c['description']]);
    }
    fclose($output);
    exit;
}
?>
</script>
</body>
</html>
