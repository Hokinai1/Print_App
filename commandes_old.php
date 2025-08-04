<?php
session_start();



// Sécurité : redirige si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php'); // ou login.php selon ton fichier
    exit();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="./css/style.css">
  <link rel="stylesheet" href="./css/commande.css">
  <link rel="shortcut icon" href="./assets/icons/icon.png" type="image/x-icon">
 
  
</head>
<body>

  
<?php

// on definit le titre a afficher dans l'onglet
$titre = "Liste Commandes";

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

      #filterYear , #filterMonth{
        
        background-color: #d5dfdcff;
     
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
    .actions{
      /* background-color: #f0f12f; */
      /* justify-content: center; */
      align-items: center;
    }
    .filters{
      display: flex;
      gap: 15px;
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

#desc{
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    margin-bottom: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
    font-size: 14px;
}

</style>

  <!-- Main -->
  <main class="main">
    <h1><?= $titre ?> </h1>

    <div class="actions">
      <button class="btn-add" onclick="openForm()">+ Nouvelle commande</button>

    
  
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
    <!-- <label for="filterYear">Filtrer par année :</label>
    <select id="filterYear">
      <option value="">Toutes les années</option>
      <option value="2024">2024</option>
      <option value="2025">2025</option>
    </select> -->

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

    <div class="table-section">
      <table id="commandesTable">
        <thead>
          <tr>
            <!-- <th>#</th> -->
            <th>Date</th>
            <th>Client</th>
            <th>Type</th>
            <th>Quantité </th>
            <th>Prix Unitaire </th>
            <th>Remise </th>
            <th>Total </th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
           
            <td>25/07/2025</td>
            <td>Durand Marie</td>
            <td>Flyer</td>
            <td>50</td>
            <td>2</td>
            <td>10</td>
            <td>90</td>
            <td class="action-btn">
              <a href="#" class="action-show">Afficher</a>
              <a href="#"class="action-edit">Modifier</a>
              <a href="#" class="action-delete">Supprimer</a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </main>
</div>

<!-- Overlay + Popup -->
<div class="overlay" id="overlay"></div>
<div class="popup-form" id="commandeForm">
  <h3>Nouvelle commande</h3>

   <div class="price">
    <div class="price-1">
           <label for="date">Date</label>
            <input type="date" id="date">

    </div>

     <div class="price-1">
          <label for="client">Client</label>
  <select id="client">
    <option value="">-- Sélectionner un client --</option>
    <option value="Durand Marie">Durand Marie</option>
    <option value="Martin Paul">Martin Paul</option>
  </select>
    </div>
    
 
  </div>
 
  

  <label for="type">Type de commande</label>
  <select id="type">
    <option value="">-- Sélectionner --</option>
    <option value="Bâche">Bâche</option>
    <option value="Flyer">Flyer</option>
    <option value="Logo">Logo</option>
  </select>

  <label for="description">Description de la commande</label>
  <textarea name="" id="desc"></textarea>

  <div class="price">
    <div class="price-1">
         <label for="quantite">Quantité</label>
  <input type="number" id="quantite" placeholder="Quantité">
    </div>
    <div class="price-1">
         <label for="prixUnitaire">Prix unitaire</label>
  <input type="number" id="prixUnitaire" placeholder="Prix unitaire">
    </div>

    <div class="price-1">
        <label for="remise">Remise </label>
  <input type="number" id="remise" placeholder="Remise">
    </div>
 
  </div>

  <div class="price-1">
         <label for="total">Prix total</label>
  <input type="number" id="total" placeholder="Total" readonly>
    </div>

  <div class="form-buttons">
    <button class="btn-cancel" onclick="closeForm()">Annuler</button>
    <button class="btn-submit" onclick="ajouterCommande()">Ajouter</button>
  </div>
</div>

<script>
  function openForm() {
    document.getElementById('commandeForm').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
  }

  function closeForm() {
    document.getElementById('commandeForm').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
  }

  // Calcul automatique du prix total
  document.getElementById('quantite').addEventListener('input', calculerTotal);
  document.getElementById('prixUnitaire').addEventListener('input', calculerTotal);
  document.getElementById('remise').addEventListener('input', calculerTotal);

  function calculerTotal() {
    const qte = parseFloat(document.getElementById('quantite').value) || 0;
    const pu = parseFloat(document.getElementById('prixUnitaire').value) || 0;
    const remise = parseFloat(document.getElementById('remise').value) || 0;
    const total = (qte * pu) - remise;
    document.getElementById('total').value = total.toFixed(2);
  }

  // Export bouton (factice ici)
  function exportCommandes() {
    const type = document.getElementById('exportType').value;
    alert('Export de la liste des commandes en : ' + type.toUpperCase());
  }

  function ajouterCommande() {
    // Ici on pourrait ajouter dynamiquement au tableau ou envoyer au backend
    alert('Commande ajoutée !');
    closeForm();
  }

  // option de fitre du tableau

  document.getElementById('filterMonth').addEventListener('change', filterTable);
document.getElementById('filterYear').addEventListener('change', filterTable);

function filterTable() {
  const selectedMonth = document.getElementById('filterMonth').value;
  const selectedYear = document.getElementById('filterYear').value;
  const rows = document.querySelectorAll('#commandesTable tbody tr');

  rows.forEach(row => {
    const dateCell = row.cells[1].textContent.trim(); // exemple : "25/07/2025"
    const [day, month, year] = dateCell.split('/');

    const matchMonth = !selectedMonth || month === selectedMonth;
    const matchYear = !selectedYear || year === selectedYear;

    if (matchMonth && matchYear) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
}
</script>
</body>
</html>
