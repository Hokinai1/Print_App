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
  <link rel="stylesheet" href="./css/config.css">
  
  <?php

// on definit le titre a afficher dans l'onglet
$titre = "Application option";

$currentPage = 'parametres';

// insertion du header
@include("./includes/header.php");

?>

  <div class="dashboard">
<!-- Top Bar -->

<?php

// insertion du sidebar

@include('./includes/sidebar.php');

?>
  


  <!-- Main -->
  <main class="main">
    <h1><?= $titre ?> </h1>

    <!-- Section infos entreprise -->
    <div class="section">
      <h2>Informations de l'entreprise</h2>
      <form id="entrepriseForm">
        <div class="form-group">
          <label for="nomEntreprise">Nom de l'entreprise</label>
          <input type="text" id="nomEntreprise" value="Imprimerie Pro" readonly>
        </div>
        <div class="form-group">
          <label for="domaine">Domaine d'activité</label>
          <input type="text" id="domaine" value="Impression numérique" readonly>
        </div>
        <div class="form-group">
          <label for="adresse">Adresse</label>
          <input type="text" id="adresse" value="123 Rue de l’Imprimerie, Abidjan" readonly>
        </div>
        <div class="form-group">
          <label for="nif">N° NIF</label>
          <input type="text" id="nif" value="NI12345678" readonly>
        </div>
        <div class="form-group">
          <label for="responsable">Responsable</label>
          <input type="text" id="responsable" value="M. Kouadio Jean" readonly>
        </div>
        <div class="form-group">
          <label for="contact">Contact</label>
          <input type="text" id="contact" value="+225 07 00 00 00" readonly>
        </div>
        <div class="form-actions">
          <button type="button" class="btn btn-edit" onclick="toggleEdit('entrepriseForm')">Modifier</button>
          <button type="button" class="btn btn-save" onclick="saveEntreprise()">Enregistrer</button>
        </div>
      </form>
    </div>

    <!-- Section identifiants -->
    <div class="section">
      <h2>Identifiants de connexion</h2>
      <form id="authForm">
        <div class="form-group">
          <label for="userLogin">Nom d'utilisateur</label>
          <input type="text" id="userLogin" value="employe01" readonly>
        </div>

        <div class="form-group">
          <label for="userPwd">Mot de passe utilisateur</label>
          <div style="position: relative; flex: 2 0 300px;">
            <input type="password" id="userPwd" value="motdepasse123" readonly>
            <span class="show-toggle" onclick="togglePassword('userPwd')"> <img src="./assets/icons/eye.png" alt="show"></span>
          </div>
        </div>

        <div class="form-group">
          <label for="adminLogin">Administrateur</label>
          <input type="text" id="adminLogin" value="admin" readonly>
        </div>

        <div class="form-group">
          <label for="adminPwd">Mot de passe admin</label>
          <div style="position: relative; flex: 2 0 300px;">
            <input type="password" id="adminPwd" value="admin1234" readonly>
            <span class="show-toggle" onclick="togglePassword('adminPwd')"> <img src="./assets/icons/eye.png" alt="show"> </span>
          </div>
        </div>

        <div class="form-actions">
          <button type="button" class="btn btn-edit" onclick="toggleEdit('authForm')">Modifier</button>
          <button type="button" class="btn btn-save" onclick="saveAuth()">Enregistrer</button>
        </div>
      </form>
    </div>

    <div class="section">
  <h2>Exportation de la base de données</h2>
  <form action="export_db.php" method="post">
    <button type="submit" class="btn btn-export">📁 Exporter la base de données</button>
  </form>
</div>
  </main>

  

</div>

<script>
  function toggleEdit(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('input');
    inputs.forEach(input => {
      input.readOnly = !input.readOnly;
    });
  }

  function saveEntreprise() {
    alert("✅ Informations de l'entreprise enregistrées !");
    // Ici, tu pourrais envoyer les données au serveur (via AJAX ou fetch)
  }

  function saveAuth() {
    alert("✅ Identifiants mis à jour !");
    // Ici aussi, tu pourrais sauvegarder dans une base de données
  }

  function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    field.type = field.type === 'password' ? 'text' : 'password';
  }
</script>
</body>
</html>
