<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Gestion pré-vol OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    echo json_encode(['status' => 'error', 'message' => 'Méthode HTTP non autorisée.']);
    exit;
}

// Récupération du token (cookie ou header Authorization)
$token = null;

if (isset($_COOKIE['auth_token']) && !empty($_COOKIE['auth_token'])) {
    $token = $_COOKIE['auth_token'];
}

if (!$token) {
    $headers = getallheaders();
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
        echo json_encode(['status' => 'error', 'message' => 'Erreur : Clé secrète manquante.']);
        exit();
    }

    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    $id_client = $decoded->id_client;

    // Récupérer l'id_reservation depuis la requête DELETE
    // Pour DELETE, on lit souvent le corps JSON, ou sinon query string
    parse_str(file_get_contents("php://input"), $delete_vars);
    
    if (!isset($delete_vars['id_reservation']) || !is_numeric($delete_vars['id_reservation'])) {
        echo json_encode(['status' => 'error', 'message' => 'ID de réservation manquant ou invalide.']);
        exit;
    }
    $id_reservation = (int)$delete_vars['id_reservation'];

    // Vérifier que la réservation appartient bien à ce client avant suppression
    $sql_check = "SELECT id_reservation FROM reservations WHERE id_reservation = :id_reservation AND id_client = :id_client";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->bindParam(':id_reservation', $id_reservation, PDO::PARAM_INT);
    $stmt_check->bindParam(':id_client', $id_client, PDO::PARAM_INT);
    $stmt_check->execute();

    if ($stmt_check->rowCount() === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Réservation non trouvée ou non autorisée.']);
        exit;
    }

    // Suppression
    $sql_delete = "DELETE FROM reservations WHERE id_reservation = :id_reservation";
    $stmt_delete = $pdo->prepare($sql_delete);
    $stmt_delete->bindParam(':id_reservation', $id_reservation, PDO::PARAM_INT);
    $stmt_delete->execute();

    echo json_encode(['status' => 'success', 'message' => 'Réservation supprimée avec succès.']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erreur serveur : ' . $e->getMessage()]);
}
?>
