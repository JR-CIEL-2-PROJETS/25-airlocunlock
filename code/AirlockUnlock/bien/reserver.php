<?php
session_start();
include '../config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['client_id'])) {
    echo 'Vous devez être connecté pour réserver un bien.';
    exit();
}

// Traitement de la réservation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_client = $_SESSION['client_id'];  // ID du client connecté
    $id_bien = $_POST['id_bien'];
    $date_arrivee = $_POST['date_arrivee'];
    $date_depart = $_POST['date_depart'];
    $nombre_personnes = $_POST['nombre_personnes'];

    // Validation des dates
    if (strtotime($date_arrivee) >= strtotime($date_depart)) {
        echo 'La date d\'arrivée doit être antérieure à la date de départ.';
        exit();
    }

    // Préparer la requête d'insertion
    $sql = "INSERT INTO reservations (id_client, id_bien, nom, date_arrivee, date_depart, nombre_personnes, statut) 
            VALUES (:id_client, :id_bien, (SELECT titre FROM biens WHERE id_bien = :id_bien), :date_arrivee, :date_depart, :nombre_personnes, 'confirmée')";

    try {
        // Préparer la déclaration
        $stmt = $pdo->prepare($sql);

        // Lier les paramètres
        $stmt->bindParam(':id_client', $id_client);
        $stmt->bindParam(':id_bien', $id_bien);
        $stmt->bindParam(':date_arrivee', $date_arrivee);
        $stmt->bindParam(':date_depart', $date_depart);
        $stmt->bindParam(':nombre_personnes', $nombre_personnes);

        // Exécuter la requête
        if ($stmt->execute()) {
            echo 'Réservation confirmée avec succès !';
        } else {
            echo 'Erreur lors de la réservation.';
        }
    } catch (PDOException $e) {
        echo 'Erreur : ' . $e->getMessage();
    }
}
?>
