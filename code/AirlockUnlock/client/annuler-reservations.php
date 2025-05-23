<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Méthode HTTP non autorisée.']);
    exit;
}

// Lecture du body JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id_reservation']) || !is_numeric($input['id_reservation'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID de réservation invalide ou manquant.']);
    exit;
}

$id_reservation = $input['id_reservation'];

// Récupération du token (cookie ou header)
$token = null;

if (isset($_COOKIE['auth_token']) && !empty($_COOKIE['auth_token'])) {
    $token = $_COOKIE['auth_token'];
}

$headers = getallheaders();
if (!$token) {
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
    } elseif (isset($headers['authorization'])) {
        $authHeader = $headers['authorization'];
    }

    if (isset($authHeader) && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
    }
}

if (!$token) {
    echo json_encode(['status' => 'error', 'message' => 'Token manquant.']);
    exit;
}

try {
    $key = getenv('JWT_SECRET_KEY');
    if (!$key) {
        echo json_encode(['status' => 'error', 'message' => 'Clé secrète manquante.']);
        exit;
    }

    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    $id_client = $decoded->id_client;

    $stmt = $pdo->prepare("SELECT statut FROM reservations WHERE id_reservation = :id_reservation AND id_client = :id_client");
    $stmt->execute([
        ':id_reservation' => $id_reservation,
        ':id_client' => $id_client
    ]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservation) {
        echo json_encode(['status' => 'error', 'message' => 'Réservation non trouvée ou accès refusé.']);
        exit;
    }

    if ($reservation['statut'] === 'annulée') {
        echo json_encode(['status' => 'error', 'message' => 'Cette réservation a déjà été annulée.']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE reservations SET statut = 'annulée' WHERE id_reservation = :id_reservation AND id_client = :id_client");
    $stmt->execute([
        ':id_reservation' => $id_reservation,
        ':id_client' => $id_client
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Votre réservation a aété annulée avec succès, le montant sera remboursé dans les 15 jours ouvrés.']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erreur serveur : ' . $e->getMessage()]);
}
?>
