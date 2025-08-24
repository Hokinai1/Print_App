<?php
session_start();
require_once './includes/db.php'; // fichier de connexion PDO

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $sql = "SELECT * FROM users WHERE username = :username LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // if ($user && password_verify($password, $user['password'])) { ce code s'utilise si le mot de passe est hashé.
          if ($user && $password === $user['password']) {
            // Connexion réussie
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role']; // Si vous avez un champ "role"
            header('Location: home.php');
            exit();

        } else {
            $erreur = "Nom d'utilisateur ou mot de passe incorrect. Attention";
        }
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>



<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>App_Login</title>
  <link rel="stylesheet" href="./css/root.css">
  <link rel="stylesheet" href="./css/login.css">
  <link rel="shortcut icon" href="./assets/icons/icon.png" type="image/x-icon">
</head>
<body>

<style>
  img{
    width: 25px;
  }
</style>
  <div class="login-container">
   


    <div class="form-panel">
      <form class="login-form" id="loginForm" method="POST">
        <h2>Connexion à l'application</h2>

        <label for="username">Nom d'utilisateur</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Mot de passe</label>
        <div class="password-wrapper">
          <input type="password" id="password" name="password" required>
          

          <i id="togglePassword">  <img src="./assets/images/eye.png" id="eye" ></i>
        </div>

        <button type="submit"> Se connecter </button>
        <?php if (!empty($erreur)): ?>
    <p class="message" style="color: red;"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>

        <!-- <p class="message" id="message"></p> -->
      </form>
    </div>

  </div>

  <script src="./js/index.js"></script>
</body>
</html>
