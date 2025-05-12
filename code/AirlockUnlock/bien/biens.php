<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config.php';

try {
    // Vérifie si un id_bien est passé dans l'URL
    if (isset($_GET['id_bien']) && is_numeric($_GET['id_bien'])) {
        $sql = "SELECT id_bien, titre, description, prix_par_nuit, capacite, adresse, photos FROM biens WHERE id_bien = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_GET['id_bien']]);
        $biens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $sql = "SELECT id_bien, titre, description, prix_par_nuit, capacite, adresse, photos FROM biens";
        $stmt = $pdo->query($sql);
        $biens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // URL de base pour les photos
    $baseUrl = "https://172.16.15.74:421/AirlockUnlock/bien/photos/";

    foreach ($biens as &$bien) {
        if (!empty($bien['photos'])) {
            $bien['photo_url'] = $baseUrl . $bien['photos'];
        } else {
            $bien['photo_url'] = null;
        }
        unset($bien['photos']); // Supprime le champ brut 'photos'
    }

    echo json_encode($biens);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Erreur PDO : ' . $e->getMessage()]);
}
?>
