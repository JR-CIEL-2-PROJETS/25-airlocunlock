<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

$method = $_SERVER['REQUEST_METHOD'];

// Autoriser les requêtes OPTIONS (préflight CORS)
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Récupération du token
$token = null;
if (isset($_COOKIE['auth_token']) && !empty($_COOKIE['auth_token'])) {
    $token = $_COOKIE['auth_token'];
}
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;
if (!$token && $authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $token = $matches[1];
}

if (!$token) {
    echo json_encode(['status' => 'error', 'message' => 'Token manquant.']);
    exit;
}

try {
    $key = getenv('JWT_SECRET_KEY');
    if (!$key) {
        echo json_encode(['status' => 'error', 'message' => 'Clé secrète JWT manquante.']);
        exit;
    }

    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    $id_client = $decoded->id_client;

    if ($method === 'GET') {
        // Récupération des infos utilisateur
        $sql = "SELECT nom, email FROM clients WHERE id_client = :id_client";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_client', $id_client, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo json_encode(['status' => 'success', 'user' => $user]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Utilisateur non trouvé.']);
        }

    } elseif ($method === 'POST') {
        // Lecture du corps JSON
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['nom']) || !isset($data['email'])) {
            echo json_encode(['status' => 'error', 'message' => 'Champs nom ou email manquants.']);
            exit;
        }

        $nom = trim($data['nom']);
        $email = trim($data['email']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Email invalide.']);
            exit;
        }

        // Mise à jour en base
        $sql = "UPDATE clients SET nom = :nom, email = :email WHERE id_client = :id_client";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id_client', $id_client, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(['status' => 'success', 'message' => 'Profil mis à jour avec succès.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Méthode HTTP non autorisée.']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erreur serveur : ' . $e->getMessage()]);
}
