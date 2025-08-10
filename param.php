<?php
session_start();
require_once './includes/db.php';


$infos = []; // Valeur par défaut
// Récupération des infos de l'entreprise
$stmt = $pdo->query("SELECT * FROM infos LIMIT 1");
$infos = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupération des utilisateurs
$users = $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);

// Gestion des mises à jour
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mise à jour entreprise
    if (isset($_POST['save_entreprise'])) {
        $sql = "UPDATE infos SET nom_entreprise=?, domaine=?, adresse=?, numero=?, responsable=?, email=?, telephone=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['nom_entreprise'],
            $_POST['domaine'],
            $_POST['adresse'],
            $_POST['numero'],
            $_POST['responsable'],
            $_POST['email'],
            $_POST['telephone'],
            $infos['id']
        ]);
     $_SESSION['success_message']  = "✅ Informations de l'entreprise enregistrées avec succès.";

        $infos = $pdo->query("SELECT * FROM infos LIMIT 1")->fetch(PDO::FETCH_ASSOC); // Rafraîchir les données
        header("Location: param.php"); // Redirection vers la même page
        exit;
    }

    // Mise à jour utilisateurs
    if (isset($_POST['save_users'])) {
        foreach ($_POST['user_id'] as $index => $id) {
            $stmt = $pdo->prepare("UPDATE users SET username=?, password=?, role=? WHERE id=?");
            $stmt->execute([
                $_POST['username'][$index],
                $_POST['password'][$index],
                $_POST['role'][$index],
                $id
            ]);
        }
        $_SESSION['success_message']  = "✅ Identifiants mis à jour avec succès.";

        $users = $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC); // Rafraîchir
        header("Location: param.php"); // Redirection vers la même page
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/config.css">
    <title>Paramètres de l'application</title>
</head>

<style>
    /* .form-group {
        margin-bottom: 10px;
    } */

    /* label {
        display: block;
        font-weight: normal;
        margin-bottom: 2px;
    } */

    .section {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .form-group {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 10px;
        align-items: center;
    }

    .form-group label {
        /* flex: 1 0 200px; */
        font-weight: normal;
    }

    /* .form-group input {
    flex: 2 0 300px;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
} */

    input[type="text"],
    input[type="email"] {
        width: 100%;
        padding: 5px;
    }

    .alert-success {
        background-color: #c8f7c5;
        padding: 10px;
        margin-bottom: 15px;
        border-left: 4px solid green;
    }

    .btn {
        margin-right: 0;
        margin-left: 65%;

    }

    .btn-submit {
        background-color: #2c7be5;
        justify-content: right;
        color: white;
        padding: 8px 14px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        box-shadow: rgba(0, 0, 0, 0.15) 1.95px 1.95px 2.6px;
    }


    .btn-submit:hover {
        background-color: #1b60c9;
    }

    .user-form {
        display: flex;
        gap: 40px;
    }

    .user-1 {
        background-color: #fff;
        padding: 0 10px;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        border-top: 5px solid #bebfc0ff;
    }

    .user-1 h3,
    .user-2 h3 {
        padding: 10px 0;
        color: #bebfc0ff;
        text-align: center;
    }

    .user-2 {
        background-color: #fff;
        padding: 0 10px;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        border-top: 5px solid #bebfc0ff;
    }
</style>

<body>

    <div class="dashboard">
        <?php @include('./includes/sidebar.php'); ?>

        <main class="main">
            <h1>Paramètres de l'application</h1>
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['success_message'] ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>


            <?php if (!empty($success_message)): ?>
                <div class="alert-success"><?= $success_message ?></div>
            <?php endif; ?>

            <!-- Infos entreprise -->
            <div class="section">
                <h2>Informations de l'entreprise</h2>
                <form method="POST">
                    <input type="hidden" name="save_entreprise" value="1">

                    <div class="form-group"><label>Nom</label><input type="text" name="nom_entreprise" value="<?= htmlspecialchars($infos['nom_entreprise'] ?? '') ?>"></div>
                    <div class="form-group"><label>Domaine</label><input type="text" name="domaine" value="<?= htmlspecialchars($infos['domaine'] ?? '') ?>"></div>
                    <div class="form-group"><label>Adresse</label><input type="text" name="adresse" value="<?= htmlspecialchars($infos['adresse'] ?? '') ?>"></div>
                    <div class="form-group"><label>Numéro</label><input type="text" name="numero" value="<?= htmlspecialchars($infos['numero'] ?? '') ?>"></div>
                    <div class="form-group"><label>Responsable</label><input type="text" name="responsable" value="<?= htmlspecialchars($infos['responsable'] ?? '') ?>"></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($infos['email'] ?? '') ?>"></div>
                    <div class="form-group"><label>Téléphone</label><input type="text" name="telephone" value="<?= htmlspecialchars($infos['telephone'] ?? '') ?>"></div>

                    <div class="btn">
                        <button type="submit" class="btn-submit">✅ Enregistrer les modification</button>
                    </div>
                </form>
            </div>

            <!-- Identifiants de connexion -->
            <div class="section">
                <h2>Identifiants de connexion</h2>
                <form method="POST">
                    <input type="hidden" name="save_users" value="1">

                    <div class="user-form">
                        <!-- Colonne Utilisateurs -->
                        <div class="user-1">
                            <h3> Infos de l'utilisateurs</h3>
                            <?php foreach ($users as $index => $user): ?>
                                <?php if ($user['role'] !== 'admin'): ?>
                                    <input type="hidden" name="user_id[]" value="<?= $user['id'] ?>">
                                    <div class="form-group"><label>Nom</label><input type="text" name="username[]" value="<?= $user['username'] ?>"></div>
                                    <div class="form-group"><label>Mot de passe</label><input type="text" name="password[]" value="<?= $user['password'] ?>"></div>
                                    <div class="form-group"><label>Rôle</label><input type="text" name="role[]" value="<?= $user['role'] ?>"></div>
                                    <br>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>

                        <!-- Colonne Administrateurs -->
                        <div class="user-2">
                            <h3>Infos de l'administrateur</h3>
                            <?php foreach ($users as $index => $user): ?>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <input type="hidden" name="user_id[]" value="<?= $user['id'] ?>">
                                    <div class="form-group"><label>Nom</label><input type="text" name="username[]" value="<?= $user['username'] ?>"></div>
                                    <div class="form-group"><label>Mot de passe</label><input type="text" name="password[]" value="<?= $user['password'] ?>"></div>
                                    <div class="form-group"><label>Rôle</label><input type="text" name="role[]" value="<?= $user['role'] ?>"></div>
                                    <br>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="btn">
                        <button type="submit" class="btn btn-save">✅Sauvegarder</button>
                    </div>
                </form>

            </div>
        </main>
    </div>



</body>

</html>