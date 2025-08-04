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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="./css/style.css" />
  <link rel="shortcut icon" href="./assets/icons/icon.png" type="image/x-icon">
  
</head>
<body>


<?php
$currentPage = 'dashboard';
// on definit le titre a afficher dans l'onglet
$titre = "Tableau de bord";
// insertion du header
@include("./includes/header.php");
?>

  <div class="dashboard">
<!-- Top Bar -->

<?php

// insertion du sidebar
@include('./includes/sidebar.php');
?>
    
    <!-- Main content -->
    <main class="main">
      <div class="header">
         <h1><?= $titre ?> </h1>

        <p>Bienvenue ! Voici un aperçu des activités de l'imprimerie.</p>
      </div>

      <!-- Stats cards -->
      <div class="cards">
        <div class="card">
          <h3>Commandes (mois) </h3>
          <div class="card-items">
            <p>128</p>
          <img src="./assets/icons/box-open.png" alt="">
          </div>
        </div>
        <div class="card">
          <h3>Categories</h3>
           <div class="card-items">
            <p>250</p>
          <img src="./assets/icons/tags.png" alt="">
          </div>
        </div>
        <div class="card">
          <h3>Clients</h3>
           <div class="card-items">
            <p>598</p>
          <img src="./assets/icons/users-alt.png" alt="">
          </div>
        </div>
        <div class="card">
          <h3>Coût total(FCFA)</h3>
           <div class="card-items">
            <p>4 350 </p>
          <img src="./assets/icons/chart.png" alt="">
          </div>
        </div>
      </div>

      <!-- Tables -->
      <div class="tables">
        <div class="table-section">
          <h2>10 dernières commandes</h2>
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Client</th>
                <th>Date</th>
                <th>Montant</th>
              </tr>
            </thead>
            <tbody>
              <tr><td>1</td><td>Durand</td><td>20/07</td><td>150 €</td></tr>
              <tr><td>2</td><td>Martin</td><td>19/07</td><td>90 €</td></tr>
              <tr><td>3</td><td>Lemoine</td><td>18/07</td><td>220 €</td></tr>
              <tr><td>4</td><td>Petit</td><td>18/07</td><td>180 €</td></tr>
              <tr><td>5</td><td>Moreau</td><td>17/07</td><td>95 €</td></tr>
              
            </tbody>
          </table>
        </div>

        <div class="table-section">
          <h2>Top 5 Clients</h2>
          <table>
            <thead>
              <tr>
                <th>Client</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
              <tr><td>Martin</td><td>1 200 €</td></tr>
              <tr><td>Durand</td><td>980 €</td></tr>
              <tr><td>Petit</td><td>890 €</td></tr>
              <tr><td>Lemoine</td><td>820 €</td></tr>
              <tr><td>Moreau</td><td>750 €</td></tr>
            </tbody>
          </table>
        </div>
      </div>

    </main>
  </div>

 


  <script>
    function confirmerDeconnexion() {
        let confirmation = confirm("Voulez-vous vraiment vous déconnecter ?");
        if (confirmation) {
            window.location.href = "logout.php";
        }
        // Sinon, ne rien faire
    }
    </script>
 


</body>
</html>
