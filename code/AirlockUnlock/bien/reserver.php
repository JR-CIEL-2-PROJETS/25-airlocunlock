<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

include '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Méthode non autorisée.']);
    exit();
}

$token = null;
if (isset($_COOKIE['auth_token']) && !empty($_COOKIE['auth_token'])) {
    $token = $_COOKIE['auth_token'];
}
if (!$token) {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
    }
}
if (!$token) {
    echo json_encode(['error' => 'Token d\'authentification manquant.']);
    exit();
}

$key = getenv('JWT_SECRET_KEY');
if (!$key) {
    echo json_encode(['error' => 'Clé secrète JWT non définie.']);
    exit();
}

try {
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    $id_client = $decoded->id_client;
    $email_client = $decoded->email ?? null;
} catch (Exception $e) {
    echo json_encode(['error' => 'Token invalide : ' . $e->getMessage()]);
    exit();
}

$stmt = $pdo->prepare("SELECT nom FROM clients WHERE id_client = :id_client");
$stmt->bindParam(':id_client', $id_client, PDO::PARAM_INT);
$stmt->execute();
$nom_client = $stmt->fetchColumn();

if (!$nom_client) {
    echo json_encode(['error' => 'Client introuvable dans la base de données.']);
    exit();
}

if (
    !isset($_POST['id_bien']) ||
    !isset($_POST['date_arrivee']) ||
    !isset($_POST['date_depart']) ||
    !isset($_POST['nombre_personnes'])
) {
    echo json_encode(['error' => 'Données de réservation incomplètes.']);
    exit();
}

$id_bien = $_POST['id_bien'];
$date_arrivee = $_POST['date_arrivee'];
$date_depart = $_POST['date_depart'];
$nombre_personnes = $_POST['nombre_personnes'];

$stmt = $pdo->prepare("SELECT photos FROM biens WHERE id_bien = :id_bien");
$stmt->bindParam(':id_bien', $id_bien, PDO::PARAM_INT);
$stmt->execute();
$photo_bien = $stmt->fetchColumn();

if (!$photo_bien) {
    echo json_encode(['error' => 'Le bien spécifié n\'existe pas ou n\'a pas de photo.']);
    exit();
}

if (strtotime($date_arrivee) >= strtotime($date_depart)) {
    echo json_encode(['error' => 'La date d\'arrivée doit être antérieure à la date de départ.']);
    exit();
}

$sql = "INSERT INTO reservations (id_client, id_bien, nom, date_arrivee, date_depart, nombre_personnes, statut)
        VALUES (:id_client, :id_bien, :nom, :date_arrivee, :date_depart, :nombre_personnes, 'confirmée')";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_client', $id_client, PDO::PARAM_INT);
    $stmt->bindParam(':id_bien', $id_bien, PDO::PARAM_INT);
    $stmt->bindParam(':nom', $nom_client, PDO::PARAM_STR);
    $stmt->bindParam(':date_arrivee', $date_arrivee);
    $stmt->bindParam(':date_depart', $date_depart);
    $stmt->bindParam(':nombre_personnes', $nombre_personnes, PDO::PARAM_INT);

    if ($stmt->execute()) {

        echo json_encode(['success' => 'Réservation confirmée avec succès.']);
    } else {
        echo json_encode(['error' => 'Erreur lors de l\'enregistrement de la réservation.']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erreur SQL : ' . $e->getMessage()]);
}
?>
