<?php
$host = 'localhost'; // ou 127.0.0.1
$dbname = 'print_store'; // Remplacez par le nom de votre base
$username = 'root'; // Par défaut sur XAMPP ou Uwamp
$password = ''; // Par défaut vide sur XAMPP ou Uwamp

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Active le mode exception pour mieux gérer les erreurs
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connexion réussie !";
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>