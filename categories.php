<?php
session_start();
require_once './includes/db.php';

// selectionner les donnée de la table categorie pour afficher le nombre apres

$stmt = $pdo->query("SELECT * FROM categories ORDER BY id DESC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Redirection si non connecté
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Récupération rôle utilisateur
$role= $_SESSION['user_role'] ?? 'user';

// Traitement AJOUT
$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'ajout') {
    $nom = trim($_POST['nom']);
    $description = trim($_POST['description']);
    $mesure = trim($_POST['mesure']);
    $prix = intval($_POST['prix']);

    if ($nom && $mesure && $prix) {
        $stmt = $pdo->prepare("INSERT INTO categories (nom, description, mesure, prix_unitaire) VALUES (?, ?, ?, ?)");
        $success = $stmt->execute([$nom, $description, $mesure, $prix]);
        $message = $success ? "success" : "error";
        header("Location: categories.php");
        
    } else {
        $message = "empty";
    }
 
}

// Traitement MODIFICATION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'modifier') {
    $id = intval($_POST['id']);
    $nom = trim($_POST['nom']);
    $description = trim($_POST['description']);
    $mesure = trim($_POST['mesure']);
    $prix = intval($_POST['prix']);

    if ($nom && $mesure && $prix) {
        $stmt = $pdo->prepare("UPDATE categories SET nom=?, description=?, mesure=?, prix_unitaire=? WHERE id=?");
        $success = $stmt->execute([$nom, $description, $mesure, $prix, $id]);
        $_SESSION['message'] = $success ? 'update_success' : 'update_error';
        header("Location: categories.php");
        exit();
    }
}

// PRÉPARATION MODIFICATION
$categorieAModifier = null;
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $categorieAModifier = $stmt->fetch(PDO::FETCH_ASSOC);
}


?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="./css/style.css">
   <link rel="shortcut icon" href="./assets/icons/icon.png" type="image/x-icon">
 
 
</head>
<body>

<style>
   .overlay {
     display: none;
     position: fixed;
     top: 0;
     left: 0;
     width: 100%;
     height: 100%;
     background-color: rgba(0, 0, 0, 0.5);
     justify-content: center;
     align-items: center;
     z-index: 999;
   }

   .popup-form {
     background: white;
     padding: 20px;
     border-radius: 10px;
     width: 400px;
   }

   .popup-form h3 {
     margin: 20px 0;
   }

   .popup-form input,
   .popup-form textarea {
     width: 100%;
      padding: 10px;
      margin-top: 5px;
      margin-bottom: 10px;
      border-radius: 5px;
      border: 1px solid #ccc;
      font-size: 14px;
   }

   .form-buttons {
     display: flex;
     justify-content: space-between;
   }

   .btn-cancel {
      background-color: #f44336;
      color: white;
      border: none;
      cursor: pointer;
      padding: 8px 14px;
      border-radius: 5px;
      margin-right: 10px;

   }

   .btn-submit {
      background-color:rgba(17, 160, 72, 1);
      color: white;
      border: none;
      cursor: pointer;
      padding: 8px 14px;
      border-radius: 5px;
   }

   .actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin: 20px 0;
    }

    .export-wrapper {
      display: flex;
      gap: 10px;
    }

    .export-wrapper select,
    .export-wrapper button {
      padding: 8px 12px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }
 #exportType{
       
        background-color: #2c7be5;
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

    #update{
      color:rgba(17, 160, 72, 1);
    }

  .total{
    color: red;
    background-color:#fdd5d2ff;
      border: none;
      padding: 8px 14px;
      border-radius: 5px;
    }




</style>

<?php
$currentPage = 'categories';
// on definit le titre a afficher dans l'onglet
$titre = "Liste des catégories";
// insertion du header
@include("./includes/header.php");
?>

  <div class="dashboard">
<!-- Top Bar -->

<?php

// insertion du sidebar
@include('./includes/sidebar.php');
?>

<main class="main">
  <h1><?= $titre ?> </h1>

  <div class="actions">
    <!-- <button id="addCategoryBtn" class="btn-add">+ Nouvelle catégorie</button> -->
    <!-- <button onclick="openForm()">+ Nouvelle catégorie</button> -->
    <?php if ($role === 'admin'): ?>
  <button class="btn-add" onclick="openAddForm()"> + Nouvelle catégorie</button>
<?php endif; ?>

 <p class="total">Total de catégories : <strong><?= count($categories) ?></strong></p>

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
  </div>

  <div class="table-section">
    <table id="categoriesTable">
      <thead>
        <tr>
          <th>#</th>
          <th>Nom</th>
          <!-- <th>Description</th> -->
          <th>Unité</th>
          <th>Prix Unitaire</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $stmt = $pdo->query("SELECT * FROM categories ORDER BY id DESC");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($categories):
          foreach ($categories as $i => $cat):
        ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><?= htmlspecialchars($cat['nom']) ?></td>
          <!--  -->
          <td><?= htmlspecialchars($cat['mesure']) ?></td>
          <td><?= number_format($cat['prix_unitaire'], 0, ',', ' ') ?> FCFA</td>

          <td class="action-btn">
            <?php if ($role === 'admin'): ?>
              <a href="categories.php?id=<?= $cat['id'] ?>" class="action-show">Modifier</a>
              <a href="categories.php?delete=<?= $cat['id'] ?>" class="action-delete" onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
            <?php else: ?>
              <span style="color: grey;">Non autorisé</span>
            <?php endif; ?>
          </td>

        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="6">Aucune catégorie trouvée.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
</div>

<!--  POPUP AJOUT -->
<div class="overlay" id="popupAjout">
  <form method="POST" action="categories.php">
    <div class="popup-form">
      <h3 id="add">Nouvelle catégorie</h3>
      
      <input type="hidden" name="action" value="ajout">
      <label>Nom</label>
      <input type="text" name="nom" required>
      <label>Description</label>
      <textarea name="description"></textarea>
      <label>Unité</label>
      <input type="text" name="mesure" required>
      <label>Prix unitaire</label>
      <input type="number" name="prix" required>
      <div class="form-buttons">
        <button type="button"class="btn-cancel" onclick="closeAddForm()">Annuler</button>
        <button type="submit" class="btn-add">Ajouter</button>
      </div>
    </div>
  </form>
</div>

<!--  POPUP MODIFICATION -->
<?php if ($categorieAModifier): ?>
  <div class="overlay" id="popupModifier" style="display:flex;">
    <form method="POST">
      <div class="popup-form">
        <h3 id="update">Modifier la catégorie</h3>
        <input type="hidden" name="action" value="modifier">
        <input type="hidden" name="id" value="<?= $categorieAModifier['id'] ?>">
        <label>Nom</label>
        <input type="text" name="nom" value="<?= $categorieAModifier['nom'] ?>" required>
        <label>Description</label>
        <textarea name="description"><?= $categorieAModifier['description'] ?></textarea>
        <label>Unité</label>
        <input type="text" name="mesure" value="<?= $categorieAModifier['mesure'] ?>" required>
        <label>Prix unitaire</label>
        <input type="number" name="prix" value="<?= $categorieAModifier['prix_unitaire'] ?>" required>
        <div class="form-buttons">
          <button type="button" class="btn-cancel" onclick="window.location.href='categories.php'">Annuler</button>
          <button type="submit" class="btn-submit">Modifier</button>
        </div>
      </div>
    </form>
  </div>
<?php endif; ?>

<?php
// Traitement SUPPRESSION
if (isset($_GET['delete'])) {
    if ($_SESSION['user_role'] !== 'admin') {
        die("⚠️ Accès refusé.");
    }
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: categories.php");
    exit;
}
?>

<!-- JAVASCRIPT -->
<script>
  function updateCategoriesCount() {
    const rows = document.querySelectorAll('#categoriesTable tbody tr');
    const count = rows.length;
    document.getElementById('totalCategories').textContent = count;
  }

  document.addEventListener('DOMContentLoaded', function () {
    updateCategoriesCount();
  });



function openAddForm() {
  document.getElementById('popupAjout').style.display = 'flex';
}
function closeAddForm() {
  document.getElementById('popupAjout').style.display = 'none';
}


function exportCategories() {
  const type = document.getElementById('exportType').value;
  alert("Export des catégories en : " + type.toUpperCase());
}


</script>

<?php
if ($message === 'success') {
  echo "<script>alert('✅ Catégorie ajoutée avec succès');</script>";
} elseif ($message === 'error') {
  echo "<script>alert('❌ Erreur lors de l\'ajout');</script>";
} elseif ($message === 'empty') {
  echo "<script>alert('❌ Tous les champs sont obligatoires');</script>";
}
if (isset($_SESSION['message'])) {
  if ($_SESSION['message'] === 'update_success') {
    echo "<script>alert('✅ Catégorie modifiée avec succès');</script>";
  } elseif ($_SESSION['message'] === 'update_error') {
    echo "<script>alert('❌ Erreur lors de la modification');</script>";
  }
  unset($_SESSION['message']);
}
?>
</body>
</html>
