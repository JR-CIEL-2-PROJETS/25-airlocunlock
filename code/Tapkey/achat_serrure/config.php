<?php
// Configuration de la base de données
$host = 'mysql';
$dbname = 'Tapkey';
$username = 'root';
$password = 'root';

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Pas de message de connexion réussie ici !
} catch (PDOException $e) {
    // On affiche l'erreur uniquement en cas de problème
    die("Échec de la connexion à la base de données : " . $e->getMessage());
}
