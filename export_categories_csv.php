<?php
session_start();
require_once './includes/db.php';

// Vérification rôle admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("⚠️ Accès refusé.");
}

// Récupérer les catégories
$stmt = $pdo->query("SELECT id, nom, prix_unitaire, mesure, description FROM categories ORDER BY id DESC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Headers CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=categories.csv');

// Ouvrir flux de sortie
$output = fopen('php://output', 'w');

// Ajouter BOM UTF-8 pour Excel (pour afficher correctement les accents)
fputs($output, "\xEF\xBB\xBF");

// Ajouter l'en-tête
fputcsv($output, ['ID', 'Nom', 'Prix Unitaire', 'Mesure', 'Description']);

// Ajouter les lignes
foreach ($categories as $cat) {
    fputcsv($output, [
        $cat['id'],
        $cat['nom'],
        $cat['prix_unitaire'],
        $cat['mesure'],
        $cat['description']
    ]);
}

// Fermer le flux
fclose($output);
exit;
