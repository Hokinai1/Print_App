 <?php
// session_start();


//  Sécurité : rediriger si non connecté
if (!isset($_SESSION['username'])) {
    header("Location: index.php"); // redirige vers login
    exit();
}

// Récupération des infos depuis la session
$username = $_SESSION['username'];
$role = $_SESSION['user_role'] ?? null;

?>
 

<aside class="sidebar">
  <div class="brand">Ricchi House</div>

  <ul class="menu">
    <li class="<?= ($currentPage ==='dashboard') ? 'active' : '' ?>">
      <a href="home.php" >
        <img src="./assets/icons/home.svg" class="icon" alt="Accueil"> Tableau de bord
      </a>
    </li>

    <li class="<?= ($currentPage === 'commandes') ? 'active' : '' ?>">
      <a href="commandes.php" >
        <img src="./assets/icons/box.svg" class="icon" alt="Commandes"> Commandes
      </a>
    </li>

    <li class="<?= ($currentPage === 'clients') ? 'active' : '' ?>">
      <a href="clients.php" >
        <img src="./assets/icons/users.svg" class="icon" alt="Clients"> Clients
      </a>
    </li>

    <li class="<?= ($currentPage === 'categories') ? 'active' : '' ?>">
      <a href="categories.php" >
        <img src="./assets/icons/tags.svg" class="icon" alt="Catégories"> Catégories
      </a>
    </li>
    
 <?php if ($role === 'admin'): ?>
  
    <li class="<?= ($currentPage === 'parametres') ? 'active' : '' ?>">
      <a href="config.php" >
        <img src="./assets/icons/settings.svg" class="icon" alt="Paramètres"> Paramètres
      </a>
    </li>
    <?php endif; ?>
    <li>
      <a href="#" onclick=" confirmerDeconnexion()">
        <img src="./assets/icons/exit.svg" class="icon" alt="Déconnexion"> Déconnexion
      </a>
    </li>
  </ul>
</aside>


 <script>
    function confirmerDeconnexion() {
        let confirmation = confirm("Voulez-vous vraiment vous déconnecter ?");
        if (confirmation) {
            window.location.href = "logout.php";
        }
         // Sinon, ne rien faire
    }
    </script>