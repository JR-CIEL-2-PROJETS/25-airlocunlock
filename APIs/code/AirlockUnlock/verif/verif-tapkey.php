<?php

header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../config.php';  // connexion PDO dans $pdo

require_once __DIR__ . '/../../vendor/autoload.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

// CORS
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Support OPTIONS pour CORS préflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['status' => 'error', 'message' => 'Méthode HTTP non autorisée.']);
    exit;
}

// Récupérer token Bearer dans header Authorization
$token = null;
$headers = getallheaders();

if (isset($headers['Authorization'])) {
    $authHeader = $headers['Authorization'];
} elseif (isset($headers['authorization'])) {
    $authHeader = $headers['authorization'];
} else {
    $authHeader = null;
}

if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $token = $matches[1];
}

if (!$token) {
    echo json_encode(['status' => 'error', 'message' => 'Token manquant.']);
    exit;
}

if (!isset($_GET['serial'])) {
    echo json_encode(['status' => 'error', 'message' => 'Paramètre serial manquant.']);
    exit;
}

$serial = $_GET['serial'];

try {
    $key = getenv('JWT_SECRET_KEY');
    if (!$key) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur : Clé secrète JWT manquante.']);
        exit;
    }

    // Decode token JWT HS256
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    $id_client = $decoded->id_client ?? null;

    if (!$id_client) {
        echo json_encode(['status' => 'error', 'message' => 'Token invalide : id_client manquant.']);
        exit;
    }

    // Vérifier la réservation valide pour ce client ET serial correct

    $sql = "SELECT r.id_reservation, r.date_arrivee, r.date_depart, b.numero_serie_tapkey
            FROM reservations r
            INNER JOIN biens b ON r.id_bien = b.id_bien
            WHERE r.id_client = :id_client
            AND b.numero_serie_tapkey = :serial
            AND r.statut = 'confirmée'";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_client', $id_client, PDO::PARAM_INT);
    $stmt->bindParam(':serial', $serial, PDO::PARAM_STR);
    $stmt->execute();

    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservation) {
        echo json_encode(['status' => 'error', 'message' => 'Pas de réservation valide pour ce client et ce numéro de série.']);
        exit;
    }

    // Vérifier que la date courante est dans la période réservation
    $now = new DateTime("now");
    $dateArrivee = new DateTime($reservation['date_arrivee']);
    $dateDepart = new DateTime($reservation['date_depart']);

    if ($now < $dateArrivee || $now > $dateDepart) {
        echo json_encode(['status' => 'error', 'message' => 'Réservation hors période valide.']);
        exit;
    }

    // Tout est OK
    echo json_encode(['status' => 'success', 'message' => 'OK']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erreur serveur : ' . $e->getMessage()]);
}
