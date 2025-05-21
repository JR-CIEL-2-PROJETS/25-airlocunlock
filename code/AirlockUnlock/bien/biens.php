<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config.php';

try {
    // Vérifie si un id_bien est passé dans l'URL
    if (isset($_GET['id_bien']) && is_numeric($_GET['id_bien'])) {
        $sql = "SELECT b.id_bien, b.id_proprietaire, b.type_bien, b.titre, b.prix_par_nuit, b.description,
                       b.surface, b.nombre_pieces, b.capacite, b.adresse, b.photos, b.wifi, b.parking, b.cuisine,
                       b.tv, b.climatisation, b.chauffage, b.serrure_electronique, b.numero_serie_tapkey
                FROM biens b
                WHERE b.id_bien = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_GET['id_bien']]);
        $biens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $sql = "SELECT b.id_bien, b.id_proprietaire, b.type_bien, b.titre, b.prix_par_nuit, b.description,
                       b.surface, b.nombre_pieces, b.capacite, b.adresse, b.photos, b.wifi, b.parking, b.cuisine,
                       b.tv, b.climatisation, b.chauffage, b.serrure_electronique, b.numero_serie_tapkey
                FROM biens b";
        $stmt = $pdo->query($sql);
        $biens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = $protocol . $host . "/AirlockUnlock/bien/photos/";

    foreach ($biens as &$bien) {
        // Ajoute l'URL de la photo
        if (!empty($bien['photos'])) {
            $bien['photo_url'] = $baseUrl . trim($bien['photos']);
        } else {
            $bien['photo_url'] = null;
        }

        unset($bien['photos']); // Supprime le champ brut 'photos' si tu ne veux pas l'envoyer brut
    }

    echo json_encode($biens);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Erreur PDO : ' . $e->getMessage()]);
}
?>
