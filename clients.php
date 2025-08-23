<?php
session_start();
require_once './includes/db.php'; // Connexion PDO $pdo


// Sécurité : redirige si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
  header('Location: index.php'); // ou login.php selon ton fichier
  exit();
}



// --- Export CSV ---
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
  $stmt = $pdo->query("SELECT * FROM clients ORDER BY id DESC");
  $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename="clients.csv"');

  $output = fopen('php://output', 'w');
  fputcsv($output, ['ID', 'Type', 'Nom', 'Adresse', 'Email', 'Contact', 'Date création']); // entêtes

  foreach ($clients as $client) {
    fputcsv($output, [
      $client['id'],
      $client['type'],
      $client['nom'],
      $client['adresse'],
      $client['email'],
      $client['contact'],
      $client['date_creation']
    ]);
  }

  fclose($output);
  exit;
}


// Finnnnnnnnnnn exporter

$message = "";
$type = "";

// --- Ajouter un client ---
if (isset($_POST['ajouter_client'])) {
  $type_client = $_POST['clientType'] ?? '';
  $nom = $_POST['clientName'] ?? '';
  $adresse = $_POST['clientAddress'] ?? '';
  $description = $_POST['description'] ?? '';
  $email = $_POST['clientEmail'] ?? '';
  $contact = $_POST['clientPhone'] ?? '';

  if ($type_client && $nom && $contact) {
    $stmt = $pdo->prepare("INSERT INTO clients (type, nom, adresse, description, email, contact, date_creation) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    if ($stmt->execute([$type_client, $nom, $adresse, $description, $email, $contact])) {
      $message = "Client ajouté avec succès !";
      $type = "success";
      header("Location: clients.php?success=1");
      exit();
    } else {
      $message = "Erreur lors de l'ajout du client.";
      $type = "error";
    }
  } else {
    $message = "Veuillez remplir tous les champs obligatoires (type, nom, téléphone).";
    $type = "error";
  }
}

// --- Modifier un client ---
if (isset($_POST['modifier_client'])) {
  $id = (int)$_POST['id'];
  $type_client = $_POST['clientType'] ?? '';
  $nom = $_POST['clientName'] ?? '';
  $adresse = $_POST['clientAddress'] ?? '';
  $description = $_POST['description'] ?? '';
  $email = $_POST['clientEmail'] ?? '';
  $contact = $_POST['clientPhone'] ?? '';

  if ($type_client && $nom && $contact) {
    $stmt = $pdo->prepare("UPDATE clients SET type=?, nom=?, adresse=?, description=?, email=?, contact=? WHERE id=?");
    if ($stmt->execute([$type_client, $nom, $adresse, $description, $email, $contact, $id])) {
      $message = "Client modifié avec succès !";
      $type = "success";
      header("Location: clients.php?modif=1");
      exit();
    } else {
      $message = "Erreur lors de la modification.";
      $type = "error";
    }
  } else {
    $message = "Veuillez remplir tous les champs obligatoires (type, nom, téléphone).";
    $type = "error";
  }
}

// --- Supprimer un client ---
if (isset($_GET['supprimer'])) {
  $id = (int)$_GET['supprimer'];
  $stmt = $pdo->prepare("DELETE FROM clients WHERE id=?");
  if ($stmt->execute([$id])) {
    $message = "Client supprimé.";
    $type = "success";
    header("Location: clients.php");
  } else {
    $message = "Erreur lors de la suppression.";
    $type = "error";
  }
}



// --- Recherche + Pagination (remplace l’ancien bloc de recherche) ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_sql = '%' . $search . '%';

// Construire dynamiquement le WHERE et les paramètres
$where  = ($search !== '')
  ? "WHERE nom LIKE ? OR email LIKE ? OR contact LIKE ?"
  : "";
$params = ($search !== '')
  ? [$search_sql, $search_sql, $search_sql]
  : [];

// Pagination
$parPage = 5; // <- ajuste si tu veux
$page    = (isset($_GET['page']) && ctype_digit($_GET['page']) && (int)$_GET['page'] >= 1)
  ? (int)$_GET['page']
  : 1;
$debut   = ($page - 1) * $parPage;

// Compter le total (avec filtre si présent)
$sqlCount = "SELECT COUNT(*) FROM clients $where";
$countStmt = $pdo->prepare($sqlCount);
$countStmt->execute($params);
$totalClients = (int)$countStmt->fetchColumn();

// Nombre total de pages (au moins 1)
$pagesTotales = max(1, (int)ceil($totalClients / $parPage));

// Normaliser la page courante si elle dépasse
if ($page > $pagesTotales) {
  $page = $pagesTotales;
  $debut = ($page - 1) * $parPage;
}

// Récupérer la liste paginée
$sqlList = "SELECT * FROM clients $where ORDER BY id DESC LIMIT :offset, :limit";
$stmt = $pdo->prepare($sqlList);

// Binder d’abord les paramètres du WHERE (s’il y en a)
foreach ($params as $k => $v) {
  // paramètres positionnels 1,2,3...
  $stmt->bindValue($k + 1, $v, PDO::PARAM_STR);
}
// Binder offset/limit en INT
$stmt->bindValue(':offset', (int)$debut, PDO::PARAM_INT);
$stmt->bindValue(':limit',  (int)$parPage, PDO::PARAM_INT);

$stmt->execute();
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);


// --- Récupération client pour modifier ---
$clientToEdit = null;
if (isset($_GET['modifier'])) {
  $id = (int)$_GET['modifier'];
  $stmt = $pdo->prepare("SELECT * FROM clients WHERE id=?");
  $stmt->execute([$id]);
  $clientToEdit = $stmt->fetch(PDO::FETCH_ASSOC);
}

// --- Récupération client pour afficher ---
$clientToShow = null;
if (isset($_GET['afficher'])) {
  $id = (int)$_GET['afficher'];
  $stmt = $pdo->prepare("SELECT * FROM clients WHERE id=?");
  $stmt->execute([$id]);
  $clientToShow = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />

  <link rel="stylesheet" href="./css/style.css">
  <link rel="stylesheet" href="./css/clients.css.">
  <link rel="shortcut icon" href="./assets/icons/icon.png" type="image/x-icon">

  <?php
  // on definit le titre a afficher dans l'onglet
  $titre = "Liste des Clients";
  $currentPage = 'clients';
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
      .export-wrapper {
        display: flex;
        justify-content: space-between;
        gap: 5px;
      }

      #exportType {
        background-color: #a3f3dfff;
        color: green;
        padding: 8px 14px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
      }

      #export {

        background-color: #a6dce5ff;
        color: #2c7be5;
        padding: 8px 14px;
        border: none;
        border-radius: 4px;
        cursor: pointer;


      }

      #export:hover {
        background-color: #90c7d1ff;


      }

      .main h1 {

        margin: 20px 0;
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
        z-index: 999;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
      }

      #clientsTable {
        min-width: 1000px;
      }

      .total {
        color: red;
        /* background-color:#fdd5d2ff; */
        border: none;
        padding: 8px 14px;
        border-radius: 5px;
      }

      button a {
        text-decoration: none;
      }

      .pagination a {
        padding: 6px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-decoration: none;
        margin: 0 3px;
        color: #333;
      }

      .pagination a:hover {
        background: #f0f0f0;
      }

      .pagination strong {
        padding: 6px 12px;
        border: 1px solid #333;
        border-radius: 4px;
        background: #2c7be5;
        color: white;
      }
    </style>
</head>

<body>
  <div class="dashboard">



    <!-- Main -->
    <main class="main">
      <h1><?= $titre ?> </h1>


      <?php if ($message): ?>
        <div class="<?= $type === 'success' ? 'alert-success' : 'alert-error' ?>" style="margin-bottom:10px; padding:10px; border-radius:5px; background:<?= $type === 'success' ? '#d4edda' : '#f8d7da' ?>; color:<?= $type === 'success' ? '#155724' : '#721c24' ?>">
          <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>

      <div class="actions">

        <button onclick="openForm()">+ Nouveau client</button>
        <p class="total">Total clients : <strong><?= count($clients) ?></strong></p>

        <input type="text" id="searchInput" placeholder="🔍 Rechercher un client...">



        <?php if ($role === 'admin'): ?>
          <div class="export-wrapper">
            <!-- <select id="exportType">
        <option value="pdf">📄 PDF</option>
        <option value="excel">📊 Excel</option>
      </select> -->

            <button id="export"> <a href="clients.php?export=csv"> 📊 Exporter CSV</a></button>
          </div>
        <?php else: ?>
          <span style="color: grey;">---</span>
        <?php endif; ?>

      </div>
      <div class="table-section">
        <table id="clientsTable">
          <thead>
            <tr>
              <th>Nom</th>
              <th>Adresse</th>
              <th>Email</th>
              <th>Contact</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($clients): foreach ($clients as $client): ?>
                <tr>
                  <td><?= htmlspecialchars($client['nom']) ?></td>
                  <td><?= htmlspecialchars($client['adresse']) ?></td>
                  <td><?= htmlspecialchars($client['email']) ?></td>
                  <td><?= htmlspecialchars($client['contact']) ?></td>

                  <!-- -->
                  <td class="action-btn">
                    <a href="?afficher=<?= $client['id'] ?>" class="action-show">Afficher</a>

                    <?php if ($role === 'admin'): ?>
                      <a href="?modifier=<?= $client['id'] ?>" class="action-edit">Modifier</a>

                      <a href="?supprimer=<?= $client['id'] ?>" class="action-delete" onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>

                    <?php else: ?>

                      <!-- <a href="#" class="action-delete">Vérouillé</a> -->
                      <a href="#" class="action-delete" class="action-delete" onclick="return alert('Vous ne pouvez pas supprimer cet client; Contactez votre administrateur...')">Supprimer</a>
                    <?php endif; ?>

                  </td>
                </tr>
              <?php endforeach;
            else: ?>
              <tr>
                <td colspan="9" style="text-align:center;">Aucun client trouvé.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    

  <!-- Pagination -->
  <div class="pagination" style="margin-top:15px; text-align:center;">
    <?php if ($pagesTotales > 1): ?>
      <!-- Bouton précédent -->
      <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">&laquo; Précédent</a>
      <?php endif; ?>

      <!-- Liens numérotés -->
      <?php for ($i = 1; $i <= $pagesTotales; $i++): ?>
        <?php if ($i == $page): ?>
          <strong style="margin:0 5px; color:white;"><?= $i ?></strong>
        <?php else: ?>
          <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" style="margin:0 5px;"><?= $i ?></a>
        <?php endif; ?>
      <?php endfor; ?>

      <!-- Bouton suivant -->
      <?php if ($page < $pagesTotales): ?>
        <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Suivant &raquo;</a>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  </main>
  </div>

  <!-- Popup Form -->
  <div class="overlay" id="overlay"></div>
  <div class="popup-form" id="clientForm" style="display:none;">
    <h3><?= $clientToEdit ? "Modifier un client" : "Ajouter un client" ?></h3>
    <form method="POST" style="margin-top:15px;">
      <?php if ($clientToEdit): ?>
        <input type="hidden" name="id" value="<?= $clientToEdit['id'] ?>" />
      <?php endif; ?>

      <label for="clientType">Type</label>
      <select name="clientType" id="clientType" required>
        <option value="">Sélectionner un type.......</option>
        <option value="Particulier" <?= ($clientToEdit && $clientToEdit['type'] === 'Particulier') ? 'selected' : '' ?>>Particulier</option>
        <option value="Entreprise" <?= ($clientToEdit && $clientToEdit['type'] === 'Entreprise') ? 'selected' : '' ?>>Entreprise</option>
        <option value="Entreprise" <?= ($clientToEdit && $clientToEdit['type'] === 'ONG') ? 'selected' : '' ?>>ONG</option>
        <option value="Entreprise" <?= ($clientToEdit && $clientToEdit['type'] === 'Association') ? 'selected' : '' ?>>Association</option>
        <option value="Entreprise" <?= ($clientToEdit && $clientToEdit['type'] === 'Cabinet') ? 'selected' : '' ?>>Cabinet</option>
        <option value="Entreprise" <?= ($clientToEdit && $clientToEdit['type'] === 'Complexe scolaire / Ecole') ? 'selected' : '' ?>>Complexe scolaire / Ecole</option>
        <option value="Entreprise" <?= ($clientToEdit && $clientToEdit['type'] === 'Atelier') ? 'selected' : '' ?>>Atelier</option>
        <option value="Entreprise" <?= ($clientToEdit && $clientToEdit['type'] === 'Autre...') ? 'selected' : '' ?>>Autre...</option>
      </select>

      <div class="form-both">
        <div class="left">
          <label for="clientName">Nom</label>
          <input type="text" name="clientName" id="clientName" placeholder="Nom..." value="<?= htmlspecialchars($clientToEdit['nom'] ?? '') ?>" required />
        </div>
        <div class="right">
          <label for="clientAddress">Adresse</label>
          <input type="text" name="clientAddress" id="clientAddress" placeholder="Adresse..." value="<?= htmlspecialchars($clientToEdit['adresse'] ?? '') ?>" />
        </div>
      </div>

      <label>Description</label>
      <textarea name="description" id="desc" placeholder="Description de la structure..."><?= htmlspecialchars($clientToEdit['description'] ?? '') ?></textarea>

      <div class="form-both">
        <div class="left">
          <label for="clientEmail">Email</label>
          <input type="email" name="clientEmail" id="clientEmail" placeholder="exemple@gmail.com..." value="<?= htmlspecialchars($clientToEdit['email'] ?? '') ?>" />
        </div>
        <div class="right">
          <label for="clientPhone">Contact</label>
          <input type="tel" name="clientPhone" id="clientPhone" placeholder="Téléphone..." value="<?= htmlspecialchars($clientToEdit['contact'] ?? '') ?>" required />
        </div>
      </div>

      <div class="form-buttons">
        <button type="button" class="btn-cancel" onclick="closeForm()">Annuler</button>
        <button type="submit" class="btn-submit" name="<?= $clientToEdit ? 'modifier_client' : 'ajouter_client' ?>">Enregistrer</button>
      </div>
    </form>
  </div>

  <!-- Popup affichage détails -->
  <?php if ($clientToShow): ?>
    <div class="overlay" id="overlayShow" style="display:block;"></div>
    <div class="popup-form" id="clientDetails" style="display:block;">
      <h3>Détails du client</h3>
      <p class="details-data"><strong>Type :</strong> <?= htmlspecialchars($clientToShow['type']) ?></p>
      <p class="details-data"><strong>Nom :</strong> <?= htmlspecialchars($clientToShow['nom']) ?></p>
      <p class="details-data"><strong>Adresse :</strong> <?= htmlspecialchars($clientToShow['adresse']) ?></p>
      <p class="details-data"><strong>Description :</strong> <?= nl2br(htmlspecialchars($clientToShow['description'])) ?></p>
      <p class="details-data"><strong>Email :</strong> <?= htmlspecialchars($clientToShow['email']) ?></p>
      <p class="details-data"><strong>Contact :</strong> <?= htmlspecialchars($clientToShow['contact']) ?></p>
      <p class="details-data"><strong>Date de création :</strong> <span class="grise"><?= htmlspecialchars($clientToShow['date_creation']) ?></span></p>
      <div class="form-buttons">
        <button type="button" class="btn-cancel" onclick="window.location.href='clients.php'">Fermer</button>
      </div>
    </div>
  <?php endif; ?>

  <script>
    // JS filtre instantané côté client
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
      searchInput.addEventListener('keyup', () => {
        const filter = searchInput.value.toLowerCase();
        const rows = document.querySelectorAll('#clientsTable tbody tr');
        rows.forEach(row => {
          row.style.display = row.innerText.toLowerCase().includes(filter) ? '' : 'none';
        });
        document.getElementById('totalClients').innerText = [...rows].filter(r => r.style.display !== 'none').length;
      });
    }

    // Popup gestion
    function openForm() {
      document.getElementById('clientForm').style.display = 'block';
      document.getElementById('overlay').style.display = 'block';
    }

    function closeForm() {
      document.getElementById('clientForm').style.display = 'none';
      document.getElementById('overlay').style.display = 'none';
      // Rechargement pour réinitialiser le formulaire
      window.location.href = 'clients.php';
    }

    <?php if ($clientToEdit): ?>
      // Si on est en modification, on ouvre automatiquement la popup
      window.onload = openForm;
    <?php endif; ?>
  </script>
</body>

</html>