<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../config.php';
require_once '/home/airlockunlock/25-airlocunlock/APIs/code/vendor/autoload.php';


use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Méthode HTTP non autorisée. Utilisez POST.']);
    exit();
}

$email = $_POST['email'] ?? '';
$mot_de_passe = $_POST['mot_de_passe'] ?? '';

if (empty($email) || empty($mot_de_passe)) {
    echo json_encode(['status' => 'error', 'message' => 'Erreur : Email et mot de passe sont requis.']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Erreur : Adresse email invalide.']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT id_client, nom, email, mot_de_passe FROM clients WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$client || !password_verify($mot_de_passe, $client['mot_de_passe'])) {
        echo json_encode(['status' => 'error', 'message' => 'Identifiants incorrects.']);
        exit();
    }

    // Génération du token JWT
    $key = getenv('JWT_SECRET_KEY');
    if (!$key) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur : Clé secrète manquante.']);
        exit();
    }

    $iat = time();
    $exp = $iat + 3600; // 1 heure
    $payload = [
        'id_client' => $client['id_client'],
        'nom' => $client['nom'],
        'email' => $client['email'],  // ajout de l'email ici
        'role' => 'client',
        'iat' => $iat,
        'exp' => $exp
    ];
    

    $jwt = JWT::encode($payload, $key, 'HS256');

    // Stockage du token JWT dans un cookie sécurisé
    $cookie_name = 'auth_token';
    $cookie_value = $jwt;
    $cookie_expiry = time() + 3600; // Le cookie expire dans 1 heure
    $cookie_path = '/'; // Le cookie sera accessible pour tout le site
    $cookie_domain = ''; // Laisse vide pour le domaine actuel
    $cookie_secure = true; // True pour une connexion HTTPS
    $cookie_httponly = true; // Empêche l'accès au cookie via JavaScript

    setcookie($cookie_name, $cookie_value, $cookie_expiry, $cookie_path, $cookie_domain, $cookie_secure, $cookie_httponly);

    echo json_encode([
        'status' => 'success',
        'message' => 'Connexion réussie.',
        'token' => $jwt,
        'client_id' => $client['id_client'],
        'nom' => $client['nom'],
        'email' => $client['email']
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erreur serveur : ' . $e->getMessage()]);
}

$pdo = null;
?>
