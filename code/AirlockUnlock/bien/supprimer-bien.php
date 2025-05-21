<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Méthode HTTP non autorisée']);
    exit();
}

if (!isset($_POST['id_bien']) || !is_numeric($_POST['id_bien']) || !isset($_POST['id_proprietaire'])) {
    echo json_encode(['error' => "L'ID du bien et l'ID du propriétaire sont requis."]);
    exit();
}

$id_bien = $_POST['id_bien'];
$id_proprietaire = $_POST['id_proprietaire'];

try {
    $stmt = $pdo->prepare("DELETE FROM biens WHERE id_bien = :id_bien AND id_proprietaire = :id_proprietaire");
    $stmt->execute([
        ':id_bien' => $id_bien,
        ':id_proprietaire' => $id_proprietaire
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['message' => 'Bien supprimé avec succès.']);
    } else {
        echo json_encode(['error' => 'Aucun bien trouvé ou vous n\'êtes pas autorisé à le supprimer.']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erreur PDO : ' . $e->getMessage()]);
}
?>
