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

$infos = $pdo->query("SELECT * FROM infos LIMIT 1")->fetch(PDO::FETCH_ASSOC);

?>

<style>
.popup {
  position: fixed;
  top: 0; left: 0;
  width: 100vw; height: 100vh;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 999;
}

.popup-content {
  background: #fff;
  padding: 30px;
  border-radius: 10px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
  position: relative;
}

.popup .close {
  position: absolute;
  top: 10px;
  right: 15px;
  font-size: 24px;
  cursor: pointer;
  color: #888;
}

.popup button {
  padding: 8px 16px;
  border: none;
  background: #007BFF;
  color: #fff;
  border-radius: 5px;
  cursor: pointer;
}

.popup button:hover {
  background: #0056b3;
}
.brand {
  /* font-weight: bold; */
  /* background-color: #fef3e2; */
  background-color: hsla(204, 39%, 87%, 1.00);
  padding: 5px;
  border-radius: 15px;
  text-align: center;
  font-size: 25px;
  /* color: #f78c1f; */
  color:hsl(202, 55%, 16%);
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
  font-family: 'PlusJakartaSans-Medium';
  margin-bottom: 30px;
}
.brand:hover{
  background-color: hsla(203, 27%, 61%, 1.00)

}

.popup-content p{
  padding: 10px 0;
}
</style>


 

<aside class="sidebar">
  <h1 style="cursor:pointer;" class="brand" onclick="document.getElementById('popupEntreprise').style.display='flex'">
  <?= htmlspecialchars($infos['nom_entreprise']) ?>
</h1>

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
      <a href="param.php" >
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

<!-- POPUP INFOS ENTREPRISE -->
<div id="popupEntreprise" class="popup" style="display: none;">
  <div class="popup-content">
    <span class="close" onclick="document.getElementById('popupEntreprise').style.display='none'">&times;</span>
    <h2>Informations de l'entreprise</h2>
    <hr> <br> <br>
    <p><strong>Nom :</strong> <?= htmlspecialchars($infos['nom_entreprise']) ?></p>
    <p><strong>Domaine :</strong> <?= htmlspecialchars($infos['domaine']) ?></p>
    <p><strong>Adresse :</strong> <?= htmlspecialchars($infos['adresse']) ?></p>
    <p><strong>Numéro :</strong> <?= htmlspecialchars($infos['numero']) ?></p>
    <p><strong>Responsable :</strong> <?= htmlspecialchars($infos['responsable']) ?></p>
    <p><strong>Email :</strong> <?= htmlspecialchars($infos['email']) ?></p>
    <p><strong>Téléphone :</strong> <?= htmlspecialchars($infos['telephone']) ?></p>

    <div style="text-align: center; margin-top: 20px;">
      <button onclick="document.getElementById('popupEntreprise').style.display='none'">Fermer</button>
    </div>
  </div>
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