<?php
include '../config.php'; // Assurez-vous que le fichier config.php est dans le même répertoire

// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['client_id'])) {
    echo 'Vous devez être connecté pour accéder à cette page.';
    exit();
}

// Récupérer les réservations du client
$id_client = $_SESSION['client_id'];
$sql = "SELECT r.id_reservation, r.date_arrivee, r.date_depart, r.nombre_personnes, r.statut, 
               b.titre, b.photos
        FROM reservations r
        JOIN biens b ON r.id_bien = b.id_bien
        WHERE r.id_client = :id_client";
try {
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_client', $id_client);
    $stmt->execute();

    // Récupérer toutes les réservations
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Erreur lors de l\'exécution de la requête : ' . $e->getMessage();
    exit();
}
?>
