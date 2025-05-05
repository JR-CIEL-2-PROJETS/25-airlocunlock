<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Méthode HTTP non autorisée.']);
    exit;
}

// Récupération du token dans l'en-tête Authorization
$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    echo json_encode(['status' => 'error', 'message' => 'Token manquant dans l’en-tête Authorization.']);
    exit;
}

if (!preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
    echo json_encode(['status' => 'error', 'message' => 'Format de token invalide.']);
    exit;
}

$token = $matches[1];

// 🔍 Vérifier dans la table tokens si ce token existe
try {
    $stmt = $pdo->prepare("SELECT id_client FROM tokens WHERE token = :token");
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        echo json_encode(['status' => 'error', 'message' => 'Token invalide ou expiré.']);
        exit;
    }

    $id_client = $client['id_client'];

    // ✅ Récupérer les réservations liées à ce client
    $sql = "SELECT 
                r.id_reservation,
                r.date_arrivee,
                r.date_depart,
                r.nombre_personnes,
                r.statut,
                b.titre,
                b.photos
            FROM reservations r
            INNER JOIN biens b ON r.id_bien = b.id_bien
            WHERE r.id_client = :id_client
            ORDER BY r.date_arrivee DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_client', $id_client, PDO::PARAM_INT);
    $stmt->execute();
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'reservations' => $reservations
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erreur serveur : ' . $e->getMessage()]);
}
?>
