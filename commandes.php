<?php
// ==== Connexion à la base de données ====
session_start();
require_once './includes/db.php'; // Connexion PDO $pdo


// Sécurité : redirige si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php'); // ou login.php selon ton fichier
    exit();
}


$message = "";
$type = "";


// ==== Traitement - Ajout d'une commande ====
if (isset($_POST['ajouter_commande'])) {
    $client = $_POST['client'];
    $categorie = $_POST['categorie'];
    $quantite = $_POST['quantite'];
    $prix_unitaire = $_POST['prix_unitaire'];
    $remise = $_POST['remise'];
    $username = $_SESSION['username'];
    $description = $_POST['description'];

    $total = ($prix_unitaire * $quantite) - $remise;

     // Récupération de l'utilisateur connecté
    $utilisateur = $_SESSION['username'] ?? 'inconnu';

    $stmt = $pdo->prepare("INSERT INTO commandes (client_id, categorie_id, quantite, prix_unitaire, remise, total, utilisateur, description) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$client, $categorie, $quantite, $prix_unitaire, $remise, $total, $utilisateur, $description]);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// ==== Traitement - Suppression ====
if (isset($_GET['supprimer'])) {
    $id = $_GET['supprimer'];
    $pdo->prepare("DELETE FROM commandes WHERE id = ?")->execute([$id]);
    header("Location: ".$_SERVER['PHP_SELF']);
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
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// ==== Récupération des données ====
$commandes = $pdo->query("SELECT c.*, cl.nom AS client_nom, cat.nom AS categorie_nom 
                          FROM commandes c
                          JOIN clients cl ON c.client_id = cl.id
                          JOIN categories cat ON c.categorie_id = cat.id
                          ORDER BY c.date_commande DESC")->fetchAll();

$clients = $pdo->query("SELECT * FROM clients")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  
 <link rel="stylesheet" href="./css/style.css">
  <link rel="stylesheet" href="./css/commande.css.">
  <link rel="shortcut icon" href="./assets/icons/icon.png" type="image/x-icon">

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
       .actions{
      /* background-color: #f0f12f; */
      /* justify-content: center; */
      align-items: center;
    }
    .filters{
      display: flex;
      gap: 15px;
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
.details-data{
  padding: 10px 0;
 
}
    .grise { color: gray; }

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
.price {
  display: flex;
  justify-content: space-between;
  gap: 10px;
}

.price-1 {
  margin: 0 15px;
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

.details-data{
  padding: 10px 0;
 
}

.popup-content h3{
  margin-bottom: 20px;
}

.btn-cancel {
    background-color: #f44336;
    color: white;
    padding: 8px 14px;
    border: none;
    border-radius: 4px;
    margin-right: 10px;
    cursor: pointer;
}

.btn-submit {
    background-color: #2c7be5;
    color: white;
    padding: 8px 14px;
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
      padding: 8px 14px;
      border-radius: 5px;
    }

    .btn-add:hover {
      background-color: #1a5ecd;
    }

    #add{
      color: #2c7be5;
     
    }

label{
   color: #023544;
}
.table-commandes{
  min-width: 1000px;
}

    /* Récupère le style de ton popup */

  </style>
</head>

<body>

<div class="dashboard">
  
  <!-- Main -->
  <main class="main">
    
    <!-- <a href="index.php" class="btn-retour">⬅ Retour à l'accueil</a> -->
    <h1><?= $titre ?> </h1>
     <div class="actions">
    <button  class="btn-add" onclick="document.getElementById('ajoutPopup').style.display='block'">+ Nouvelle commande</button>

  <div class="filters">
        <div>
    <label for="filterMonth">Filtrer par mois :</label>
    <select id="filterMonth">
      <option value="">Tous les mois</option>
      <option value="01">Janvier</option>
      <option value="02">Février</option>
      <option value="03">Mars</option>
      <option value="04">Avril</option>
      <option value="05">Mai</option>
      <option value="06">Juin</option>
      <option value="07">Juillet</option>
      <option value="08">Août</option>
      <option value="09">Septembre</option>
      <option value="10">Octobre</option>
      <option value="11">Novembre</option>
      <option value="12">Décembre</option>
    </select>
  </div>
  <div>
   
    <select id="filterYear">
  <option value="">Toutes les années</option>
  <?php
    $currentYear = date('Y');
    for ($year = 2023; $year <= $currentYear; $year++) {
      echo "<option value=\"$year\">$year</option>";
    }
  ?>
</select>
  </div>
      
</div>
 <p class="total">Total commandes : <strong><?= count($commandes) ?></strong></p>
<div class="">
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

      <!--  -->
    </div>
    
     

      <!-- <input type="text" id="searchInput" placeholder="🔍 Rechercher un client..."> -->
      
      

   

    </div>


<!-- ==== TABLEAU DES COMMANDES ==== -->
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
            <td><?= date('d/m/Y', strtotime($cmd['date_commande'])) ?></td>    <!--afficcher la date sans lheure -->
            <td><?= htmlspecialchars($cmd['client_nom']) ?></td>
            <td><?= htmlspecialchars($cmd['categorie_nom']) ?></td>
            <td><?= $cmd['quantite'] ?></td>
            <td><?= $cmd['prix_unitaire'] ?></td>
            <td><?= $cmd['remise'] ?></td>
            <td><?= $cmd['total'] ?></td>
         
            <td class="action-btn">
                <a href="#" class="action-show" onclick="ouvrirPopupVoir(<?= $cmd['id'] ?>)">Afficher</a>
                <a href="#" class="action-edit" onclick="ouvrirPopupModifier(<?= $cmd['id'] ?>)">Modifier</a>
                <a href="?supprimer=<?= $cmd['id'] ?>"  class="action-delete" onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
            </td>
        </tr>

        <!-- ==== POPUP VOIR COMMANDE ==== -->
        <div class="popup-form" id="voirPopup<?= $cmd['id'] ?>">
            <div class="popup-content">
                <h3>Détails de la commande</h3>
                <p class="details-data"><strong>Client :</strong> <?= htmlspecialchars($cmd['client_nom']) ?></p>
                <p class="details-data"><strong>Catégorie :</strong> <?= htmlspecialchars($cmd['categorie_nom']) ?></p>
                <p class="details-data"><strong>Quantité :</strong> <?= $cmd['quantite'] ?></p>
                <p class="details-data"><strong>Prix unitaire :</strong> <?= $cmd['prix_unitaire'] ?></p>
                <p class="details-data"><strong>Remise :</strong> <?= $cmd['remise'] ?></p>
                <p class="details-data"><strong>Total :</strong> <?= $cmd['total'] ?></p>
                <p class="details-data"><strong>Description :</strong> <?= htmlspecialchars($cmd['description']) ?></p>
                <p class="details-data"><strong>Utilisateur :</strong> <?= htmlspecialchars($cmd['utilisateur']) ?></p>
                <p class="details-data"><strong>Date :</strong> <?= $cmd['date_commande'] ?></p>

                <div class="form-buttons">
                <button onclick="window.print()">Imprimer</button>
                <button class="btn-cancel" onclick="fermerPopup('voirPopup<?= $cmd['id'] ?>')">Fermer</button>
                </div>
            </div>
        </div>

        <!-- ==== POPUP MODIFIER COMMANDE ==== -->
        <div class="popup-form " id="modifierPopup<?= $cmd['id'] ?>">
            <form method="POST" class="popup-content">
                <h3>Modifier la commande</h3>
                <input type="hidden" name="id" value="<?= $cmd['id'] ?>">
                
                <div class="price">
                  <div class="price-1">
                    <label>Client</label>
                <select name="client">
                    <?php foreach ($clients as $cl): ?>
                        <option value="<?= $cl['id'] ?>" <?= ($cl['id'] == $cmd['client_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cl['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                    </div>
                    
                <div class="price-1">
                  <label>Catégorie</label>
                <select name="categorie" onchange="updatePrixUnitaireEdit(this, 'pu<?= $cmd['id'] ?>')">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" data-prix="<?= $cat['prix_unitaire'] ?>" <?= ($cat['id'] == $cmd['categorie_id']) ? 'selected' : '' ?>>
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
                <input type="number" name="prix_unitaire" id="pu<?= $cmd['id'] ?>" value="<?= $cmd['prix_unitaire'] ?>" readonly>

                  </div>
                </div> 
                
                <div class="price">
                  <div class="price-1">
                    <label>Remise</label>
                <input type="number" name="remise" value="<?= $cmd['remise'] ?>">
                  </div>
                  <div class="price-1">
                    <label >Utilisateur</label>
                    <input type="text" name="utilisateur" value="<?= $_SESSION['username'] ?>" readonly>
                  </div>
                </div>
                 <div class="form-buttons">
                <button class="btn-cancel" type="button" onclick="fermerPopup('modifierPopup<?= $cmd['id'] ?>')">Annuler</button>
               <button class="btn-submit" type="submit" name="modifier_commande">Modifier</button>
                </div>
            </form>
        </div>
    <?php endforeach; ?>
    </tbody>
</table>


<!-- ==== POPUP AJOUTER COMMANDE ==== -->
<div class="popup-form" id="ajoutPopup">
    <form method="POST" class="popup-content">
        <h3>Nouvelle commande</h3>

        <div class="price">
          <div class="price-1"></div>
          <div class="price-1"></div>
        </div>

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
        <textarea id="desc" name="description">...</textarea>

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
             
        <button type="button" class="btn-cancel" onclick="document.getElementById('ajoutPopup').style.display='none'">Annuler</button>
         <button type="submit" class="btn-submit" name="ajouter_commande">Ajouter</button>        
      </div>
        
    </form>
</div>

<!-- ==== JS POUR AFFICHER LE PRIX SELON CATÉGORIE ==== -->
<script>
function updatePrixUnitaire(select) {
    let pu = select.options[select.selectedIndex].dataset.prix;
    document.getElementById('pu').value = pu;
}
function updatePrixUnitaireEdit(select, inputId) {
    let pu = select.options[select.selectedIndex].dataset.prix;
    document.getElementById(inputId).value = pu;
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
</script>
