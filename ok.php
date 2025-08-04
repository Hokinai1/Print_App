/**
 * fichier: includes/db.php
 */
<?php
$host = 'localhost';
$db   = 'nom_de_ta_base';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Connexion échouée : ' . $e->getMessage());
}
?>


/**
 * fichier: categorie/ajouter.php
 */



/**



/**
 * fichier: categorie/supprimer.php
 */

