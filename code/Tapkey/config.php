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

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Définir le mode d'erreur de PDO sur Exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Afficher un message de connexion réussie
    echo "Connexion réussie à la base de données '$dbname' avec l'utilisateur '$username'.<br>";
} catch (PDOException $e) {
    echo "Échec de la connexion à la base de données : " . $e->getMessage();
}
?>
