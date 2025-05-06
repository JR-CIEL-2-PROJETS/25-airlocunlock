<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../config.php'; // Assure-toi que le fichier config.php est bien configuré pour ta connexion à la base de données.
require_once __DIR__ . '/../../vendor/autoload.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['status' => 'error', 'message' => 'Méthode HTTP non autorisée.']);
    exit;
}

// ✅ Récupération du token : soit dans le cookie, soit dans le header Authorization
$token = null;

// 1. D'abord essayer avec le cookie (navigateur web)
if (isset($_COOKIE['auth_token']) && !empty($_COOKIE['auth_token'])) {
    $token = $_COOKIE['auth_token'];
}

$authHeader = null;

// Méthode alternative pour s'assurer de récupérer l'en-tête Authorization même si Apache ne le transmet pas
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
} elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
} elseif (function_exists('getallheaders')) {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
    } elseif (isset($headers['authorization'])) {
        $authHeader = $headers['authorization'];
    }
}


    if (isset($authHeader) && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
    }


// Si aucun token trouvé
if (!$token) {
    echo json_encode(['status' => 'error', 'message' => 'Token manquant.']);
    exit;
}

try {
    // Clé secrète
    $key = getenv('JWT_SECRET_KEY'); // Assure-toi que la clé secrète est bien configurée dans ton .env ou ailleurs.
    if (!$key) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur : Clé secrète manquante.']);
        exit();
    }

    // Décodage du token
    $decoded = JWT::decode($token, new Key($key, 'HS256'));

    // Récupération de l'ID utilisateur
    $id_client = $decoded->id_client;

    // Requête SQL pour obtenir les informations de l'utilisateur
    $sql = "SELECT nom, email FROM clients WHERE id_client = :id_client";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_client', $id_client, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si l'utilisateur est trouvé, renvoie les données
    if ($user) {
        echo json_encode([
            'status' => 'success',
            'user' => $user
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Utilisateur non trouvé.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erreur serveur : ' . $e->getMessage()]);
}
?>
