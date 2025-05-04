<?php
header('Content-Type: application/json');
include '../config.php';

$client_id = $_GET['id_client']; // Récupérer l'ID client depuis l'URL

// Requête SQL pour récupérer les réservations avec les infos du bien
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
        'message' => 'Erreur lors de la récupération des données : ' . $e->getMessage()
    ]);
}
?>
