<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config.php';
require_once __DIR__ . '/../../vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

// Autoriser uniquement DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    echo json_encode(['error' => 'Méthode HTTP non autorisée']);
    exit();
}

// Vérifie si le token JWT est présent
$key = getenv('JWT_SECRET_KEY');
if (!$key) {
    echo json_encode(['error' => 'Clé secrète JWT non définie.']);
    exit();
}

$token = null;
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $token = $matches[1];
} elseif (isset($_COOKIE['auth_token']) && !empty($_COOKIE['auth_token'])) {
    $token = $_COOKIE['auth_token'];
}

if (!$token) {
    echo json_encode(['error' => 'Token JWT manquant.']);
    exit();
}

try {
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    $id_proprietaire = $decoded->id_proprietaire;
} catch (Exception $e) {
    echo json_encode(['error' => 'Token invalide : ' . $e->getMessage()]);
    exit();
}

// Lire le corps brut de la requête DELETE
$rawInput = file_get_contents("php://input");
$input = json_decode($rawInput, true);

if (empty($input['id_bien']) || !is_numeric($input['id_bien'])) {
    echo json_encode(['error' => "L'ID du bien est requis et doit être numérique."]);
    exit();
}

$id_bien = (int)$input['id_bien'];

try {
    $stmt = $pdo->prepare("DELETE FROM biens WHERE id_bien = :id_bien AND id_proprietaire = :id_proprietaire");
    $stmt->execute([
        ':id_bien' => $id_bien,
        ':id_proprietaire' => $id_proprietaire
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Bien supprimé avec succès.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Aucun bien trouvé ou vous n\'êtes pas autorisé à le supprimer.']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erreur PDO : ' . $e->getMessage()]);
}
?>
