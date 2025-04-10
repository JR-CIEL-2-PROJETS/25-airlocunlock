<?php
header('Content-Type: application/json');
include '../config.php';

// Récupérer l'ID du client depuis les paramètres GET
$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

if ($client_id <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ID client invalide ou manquant.'
    ]);
    exit();
}

// Requête SQL pour récupérer les réservations du client
$sql = "SELECT r.id_reservation, r.date_arrivee, r.date_depart, r.nombre_personnes, r.statut,
               b.titre, b.photos
        FROM reservations r
        JOIN biens b ON r.id_bien = b.id_bien
        WHERE r.id_client = :id_client";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_client', $client_id, PDO::PARAM_INT);
    $stmt->execute();
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'reservations' => $reservations
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur lors de l\'exécution de la requête : ' . $e->getMessage()
    ]);
}
?>