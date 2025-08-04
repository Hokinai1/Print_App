<?php
session_start();
require_once './includes/db.php';



// Sécurité : redirige si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php'); // ou login.php selon ton fichier
    exit();
}


$message = "";
$type = "";

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $type = $_SESSION['message_type'];
    unset($_SESSION['message'], $_SESSION['message_type']);
}



// ─────────── Insertion d'un client ───────────
if (isset($_POST['ajouter_client'])) {
    $stmt = $pdo->prepare("INSERT INTO clients (type, nom, adresse, description, email, telephone) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['type'], $_POST['nom'], $_POST['adresse'],
        $_POST['description'], $_POST['email'], $_POST['telephone']
    ]);
    header("Location: clients.php");
    exit;
}

// ─────────── Modification d'un client ───────────
if (isset($_POST['modifier_client'])) {
    $stmt = $pdo->prepare("UPDATE clients SET type = ?, nom = ?, adresse = ?, description = ?, email = ?, telephone = ? WHERE id = ?");
    $stmt->execute([
        $_POST['type'], $_POST['nom'], $_POST['adresse'],
        $_POST['description'], $_POST['email'], $_POST['telephone'], $_POST['id']
    ]);
    header("Location: clients.php");
    exit;
}

// ─────────── Suppression d'un client ───────────
if (isset($_GET['supprimer'])) {
    $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
    $stmt->execute([$_GET['supprimer']]);
    header("Location: clients.php");
    exit;
}

// ─────────── Affichage des clients ───────────
$clients = $pdo->query("SELECT * FROM clients ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// ─────────── Récupération d'un client à modifier ───────────
$clientModifier = null;
if (isset($_GET['modifier'])) {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute([$_GET['modifier']]);
    $clientModifier = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ─────────── Récupération d'un client à afficher ───────────
$clientAfficher = null;
if (isset($_GET['afficher'])) {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute([$_GET['afficher']]);
    $clientAfficher = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>


<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">

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
      #exportType{
    background-color: #a3f3dfff;
     color: green;
     padding: 8px 14px;
     border: none;
     border-radius: 4px;
     cursor: pointer;
      }

      #export{
        
        background-color: #76bcc9ff;
        color: #2c7be5;
        padding: 8px 14px;
     border: none;
     border-radius: 4px;
     cursor: pointer;

      }
      .main h1 {
     
        margin: 20px 0;
      }

      .total{
    color: red;
    background-color:#fdd5d2ff;
      border: none;
      padding: 8px 14px;
      border-radius: 5px;
    }

 
#desc{
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    margin-bottom: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
    font-size: 14px;
}

.form-both{
display: flex;
justify-content: space-between;
gap: 10px;
}
     </style>

  <!-- Main -->
  <main class="main">
     <h1><?= $titre ?> </h1>

     <?php if (!empty($message)): ?>
  <div class="alert <?= $type ?>" style="padding: 10px; background-color: <?= $type === 'success' ? '#d4edda' : '#f8d7da' ?>; color: <?= $type === 'success' ? '#155724' : '#721c24' ?>; border: 1px solid <?= $type === 'success' ? '#c3e6cb' : '#f5c6cb' ?>; border-radius: 5px; margin-bottom: 15px;">
    <?= htmlspecialchars($message) ?>
  </div>
<?php endif; ?>

     

    <div class="actions">
    
      <button onclick="openForm()">+ Nouveau client</button>
      <p>Total clients : <strong><?= count($clients) ?></strong></p>

      <input type="text" id="searchInput" placeholder="🔍 Rechercher un client...">
      
      

       <?php if ($role === 'admin'): ?>
    <div class="export-wrapper">
      <select id="exportType">
        <option value="pdf">📄 PDF</option>
        <option value="excel">📊 Excel</option>
      </select>
      <button id="export" onclick="exportCategories()">Exporter</button>
    </div>
    <?php else: ?>
    <span style="color: grey;">---</span>
            <?php endif; ?>

    </div>

    <div class="table-section">
      <table id="clientsTable">
        <thead>
          <tr>
            <th>#</th>
            <th>Type d'Entreprise</th>
            <th>Nom...</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($clients as $client): ?>
          <tr>
              <td><?= htmlspecialchars($client['nom']) ?></td>
      <td><?= htmlspecialchars($client['type']) ?></td>
      <td><?= htmlspecialchars($client['email']) ?></td>
      <td><?= htmlspecialchars($client['telephone']) ?></td>
             <td class="action-btn">
              <a href="?afficher=<?= $client['id'] ?>" class="btn btn-show">Afficher</a>
        <a href="?modifier=<?= $client['id'] ?>" class="btn btn-edit">Modifier</a>
        <a href="?supprimer=<?= $client['id'] ?>" class="btn btn-delete" onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
            </td>
          </tr>
            <?php endforeach; ?>

        </tbody>
      </table>
    </div>
  </main>
</div>




<!-- Popup Form -->
<!-- Popup Form -->
<div class="overlay" id="overlay"></div>
<div class="popup-form" id="clientForm">
  <h3>Ajouter un client</h3>
  
  <label for="clientType">Type</label>
  <select id="clientType">
    <option value="particulier">........Sélectionner..........</option>
    <option value="particulier">Particulier</option>
    <option value="entreprise">Entreprise</option>
    <option value="entreprise">ONG</option>
    <option value="entreprise">Association</option>
    <option value="entreprise">Cabinet</option>
    <option value="entreprise">Complexe scolaire / Ecole</option>
    <option value="entreprise">Atelier</option>
    <option value="entreprise">Autre...</option>
  </select>

  <!-- <label for="clientName">Nom (Entreprise ou Particulier)</label>
  <input type="text" id="clientName" placeholder="Nom..."> -->

  <div class="form-both">
    <div class="left">
 <label for="clientName">Nom </label>
  <input type="text" id="clientName" placeholder="Nom...">
    </div>
   
<div class="right">
 <label for="clientAddress">Adresse</label>
  <input type="text" id="clientAddress" placeholder="Adresse...">
</div>

  </div>


  <label>Description</label>
  <textarea id="desc" name="description">Description de la structure...</textarea>

  <!-- <label for="clientAddress">Adresse</label>
  <input type="text" id="clientAddress" placeholder="Adresse..."> -->

   <!-- <label for="clientEmail">Email</label>
  <input type="email" id="clientEmail" placeholder="exemple@gmail.com...">

  <label for="clientPhone">Contact</label>
  <input type="tel" id="clientPhone" placeholder="Téléphone..."> -->

  <div class="form-both">
    <div class="left">
 <label for="clientEmail">Email</label>
  <input type="email" id="clientEmail" placeholder="exemple@gmail.com...">
    </div>
   
<div class="right">
        <label for="clientPhone">Contact</label>
  <input type="tel" id="clientPhone" placeholder="Téléphone...">
</div>

  </div>

  <div class="form-buttons">
    <button class="btn-cancel" onclick="closeForm()">Annuler</button>
    <button class="btn-submit">Enregistrer</button>
  </div>
</div>

<script>
  // Filtrage dynamique
  const searchInput = document.getElementById('searchInput');
  searchInput.addEventListener('keyup', function() {
    const filter = searchInput.value.toLowerCase();
    const rows = document.querySelectorAll('#clientsTable tbody tr');

    rows.forEach(row => {
      const text = row.innerText.toLowerCase();
      row.style.display = text.includes(filter) ? '' : 'none';
    });
  });

  // Compteur de clients
  function updateClientCount() {
    const rows = document.querySelectorAll('#clientsTable tbody tr');
    document.getElementById('totalClients').innerText = rows.length;
  }

  // Pop-up
  function openForm() {
    document.getElementById('clientForm').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
  }

  function closeForm() {
    document.getElementById('clientForm').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
  }

  // Initialisation
  window.onload = updateClientCount;
</script>
</body>
</html>










<td><?= $client['id'] ?></td>
            <td><?= htmlspecialchars($client['type']) ?></td>
            <td><?= htmlspecialchars($client['nom']) ?></td>
            <td><?= htmlspecialchars($client['adresse']) ?></td>
            <td><?= htmlspecialchars($client['description']) ?></td>
            <td><?= htmlspecialchars($client['email']) ?></td>
            <td><?= htmlspecialchars($client['telephone']) ?></td>
            <td><?= htmlspecialchars($client['date_creation']) ?></td>