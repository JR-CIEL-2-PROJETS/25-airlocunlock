<?php
header('Content-Type: application/json');
include '../config.php';

// Vérifie si l'ID client est passé en paramètre GET
$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

if ($client_id <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ID client invalide ou manquant.'
    ]);
    exit();
}

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

    // Ajouter le chemin complet de l'image si besoin
    foreach ($reservations as &$res) {
        $res['photo_url'] = 'chemin/vers/tes/images/' . $res['photos']; // modifie ce chemin selon ton dossier
    }

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
