 <?php
// session_start();


//  Sécurité : rediriger si non connecté
if (!isset($_SESSION['username'])) {
    header("Location: index.php"); // redirige vers login
    exit();
}

// Récupération des infos depuis la session
$username = $_SESSION['username'];
$role = $_SESSION['user_role'] ?? 'user';

?>
 
 
 <link rel="stylesheet" href="./css/style.css" />
 <link rel="shortcut icon" href=".../assets/icons/icon.png" type="image/x-icon">
 <!-- on declare une variable titre qui aura le titre de chaque page . au cas ou la page n'existe pas elle affiche par defaut le nom Accueil -->
 <title> <?= $titre ?? "Accueil" ?> </title> 
 
 <style>
  .user{
    color: #f78c1f;
  }
  .date{
    color : hsl(202, 55%, 16%);
  }
 </style>

 <!-- Top Bar -->
  <div class="topbar">
    <div class="topbar-left">
      <span class="date" id="current-date"></span>
    </div>
    <div class="topbar-right">
       <div class="user-info">
        <img src="./assets/images/profil.jpg" alt="Profil" class="profile-pic">
        <!-- <span class="username">Jean Dupont</span> -->
         <p>Rôle : <strong class="user"><?= htmlspecialchars($role) ?></strong> |</p>
        <div class="dropdown">
          <button class="dropdown-toggle"> Connecté en tant que : <span class="user"> <?= htmlspecialchars($username) ?></span> </button>
          <div class="dropdown-menu">
            <a href="#"onclick="confirmerDeconnexion()">Déconnexion</a>
          </div>
        </div>
        
      </div>

    </div>
  </div>

  
<script>
  // Afficher la date du jour au format JJ/MM/YYYY
  const dateEl = document.getElementById("current-date");
  const now = new Date();
  const jour = String(now.getDate()).padStart(2, '0');
  const mois = String(now.getMonth() + 1).padStart(2, '0');
  const annee = now.getFullYear();
  dateEl.textContent = `${jour}/${mois}/${annee}`;

  // Dropdown toggle
  const toggle = document.querySelector('.dropdown-toggle');
  const menu = document.querySelector('.dropdown-menu');
  toggle.addEventListener('click', () => {
    menu.classList.toggle('show');
  });

  // Fermer le menu si on clique ailleurs
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.dropdown')) {
      menu.classList.remove('show');
    }
  });


   
    function confirmerDeconnexion() {
        let confirmation = confirm("Voulez-vous vraiment vous déconnecter ?");
        if (confirmation) {
            window.location.href = "logout.php";
        }
        // Sinon, ne rien faire
    }
    </script>

