<?php
session_start();

require_once './includes/db.php';



// Sécurité : redirige si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php'); // ou login.php selon ton fichier
    exit();
}

// Récupération rôle utilisateur
$role= $_SESSION['user_role'] ?? 'user';

// Récupération des statistiques
$mois = date('m');
$annee = date('Y');

// Total commandes mois
$stmt = $pdo->prepare("SELECT COUNT(*) FROM commandes WHERE MONTH(date_commande) = ? AND YEAR(date_commande) = ?");
$stmt->execute([$mois, $annee]);
$totalCommandesMois = $stmt->fetchColumn();

// Coût total du mois
$stmt = $pdo->prepare("SELECT SUM(total) FROM commandes WHERE MONTH(date_commande) = ? AND YEAR(date_commande) = ?");
$stmt->execute([$mois, $annee]);
$coutTotalMois = $stmt->fetchColumn();
$coutTotalMois = $coutTotalMois ?: 0;

// Total clients
$stmt = $pdo->query("SELECT COUNT(*) FROM clients");
$totalClients = $stmt->fetchColumn();

// Total catégories
$stmt = $pdo->query("SELECT COUNT(*) FROM categories");
$totalCategories = $stmt->fetchColumn();

// Dernières commandes
$stmt = $pdo->query("SELECT c.nom AS client, com.date_commande, com.total 
                     FROM commandes com 
                     JOIN clients c ON com.client_id = c.id 
                     ORDER BY com.date_commande DESC 
                     LIMIT 10");
$dernieresCommandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Top 5 clients
$stmt = $pdo->query("SELECT c.nom AS client, SUM(com.total) AS total 
                     FROM commandes com 
                     JOIN clients c ON com.client_id = c.id 
                     GROUP BY com.client_id 
                     ORDER BY total DESC 
                     LIMIT 5");
$topClients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>





<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="./css/style.css" />
  <link rel="shortcut icon" href="./assets/icons/logo.ico" type="image/x-icon">
 
  
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
            <p><?= $totalCommandesMois ?></p>
          <img src="./assets/icons/box-open.png" alt="">
          </div>
        </div>
        <div class="card">
          <h3>Categories</h3>
           <div class="card-items">
            <p><?= $totalCategories ?></p>
          <img src="./assets/icons/tags.png" alt="">
          </div>
        </div>
        <div class="card">
          <h3>Clients</h3>
           <div class="card-items">
            <p> <?= $totalClients ?> </p>
          <img src="./assets/icons/users-alt.png" alt="">
          </div>
        </div>

         <?php if ($role === 'admin'): ?>
        <div class="card">
          <h3>Coût totat </h3>
           <div class="card-items">
            <p><?= number_format($coutTotalMois, 0, ',', ' ') ?> FCFA </p>
          <img src="./assets/icons/chart.png" alt="">
          </div>
        </div>
         <?php else: ?>
    <span style="color: grey;"></span>
            <?php endif; ?>
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
              <?php foreach ($dernieresCommandes as $index => $cmd): ?>
              <tr>
                <td><?= $index + 1 ?></td>
            <td><?= htmlspecialchars($cmd['client']) ?></td>
            <td><?= date('d/m', strtotime($cmd['date_commande'])) ?></td>
            <td><?= number_format($cmd['total'], 0, ',', ' ') ?> FCFA</td>

              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="table-section">
          <h2>Top 5 meilleurs clients</h2>
          <table>
            <thead>
              <tr>
                <th>Client</th>
                <th>Total des commandes</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($topClients as $cli): ?>
                <tr>
                <tr>
            <td><?= htmlspecialchars($cli['client']) ?></td>
            <td><?= number_format($cli['total'], 0, ',', ' ') ?> FCFA</td>
        </tr>
              <?php endforeach; ?>
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
