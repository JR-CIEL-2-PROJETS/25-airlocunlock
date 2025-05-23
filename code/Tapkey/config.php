<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
// Configuration de la base de données
$host = 'mysql'; // Nom d'hôte ou adresse IP de votre serveur MySQL (ici pour Docker c'est "mysql")
$dbname = 'Tapkey'; // Nom de la base de données
$username = 'root'; // Nom d'utilisateur MySQL
$password = 'root'; // Mot de passe MySQL

try {
    $pdo_tapkey = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo_tapkey->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connexion Tapkey réussie.";
} catch (PDOException $e) {
    die("Erreur de connexion à la base Tapkey : " . $e->getMessage());
}
