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

if (!isset($_POST['id_bien']) || !is_numeric($_POST['id_bien'])) {
    echo json_encode(['error' => "L'ID du bien est requis pour la modification."]);
    exit();
}

$id_bien = $_POST['id_bien'];

$fields = [
    'type_bien', 'titre', 'prix_par_nuit', 'description',
    'surface', 'nombre_pieces', 'capacite', 'adresse',
    'wifi', 'parking', 'cuisine', 'tv', 'climatisation',
    'chauffage', 'serrure_electronique', 'numero_serie_tapkey'
];

$data = [];
$set = [];

foreach ($fields as $field) {
    if (isset($_POST[$field])) {
        $data[$field] = $_POST[$field];
        $set[] = "$field = :$field";
    }
}

$checkboxFields = ['wifi', 'parking', 'cuisine', 'tv', 'climatisation', 'chauffage', 'serrure_electronique'];
foreach ($checkboxFields as $field) {
    $data[$field] = isset($_POST[$field]) ? 1 : 0;
    $set[] = "$field = :$field";
}

if (!empty($_POST['numero_serie_tapkey'])) {
    $numero = trim($_POST['numero_serie_tapkey']);

    try {
        $stmt = $pdo_tapkey->prepare("SELECT COUNT(*) FROM Tapkey.cles_electroniques WHERE numero_serie = :numero");
        $stmt->execute([':numero' => $numero]);
        if ($stmt->fetchColumn() == 0) {
            echo json_encode(['error' => 'Ce numéro de série Tapkey n’existe pas.']);
            exit();
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM biens WHERE numero_serie_tapkey = :numero AND id_bien != :id_bien");
        $stmt->execute([':numero' => $numero, ':id_bien' => $id_bien]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['error' => 'Ce numéro de série Tapkey est déjà utilisé pour un autre bien.']);
            exit();
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erreur PDO Tapkey : ' . $e->getMessage()]);
        exit();
    }
}

if (isset($_FILES['photos']) && $_FILES['photos']['error'] === 0) {
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $file_extension = strtolower(pathinfo($_FILES['photos']['name'], PATHINFO_EXTENSION));

    if (!in_array($file_extension, $allowed_extensions)) {
        echo json_encode(['error' => 'Fichier photo invalide.']);
        exit();
    }

    $photo_name = uniqid('bien_', true) . '.' . $file_extension;
    $target_directory = __DIR__ . '/photos/';

    if (!is_dir($target_directory)) {
        mkdir($target_directory, 0775, true);
    }

    $target_file = $target_directory . $photo_name;

    if (!move_uploaded_file($_FILES['photos']['tmp_name'], $target_file)) {
        echo json_encode(['error' => 'Erreur lors du téléchargement de la photo.']);
        exit();
    }

    $data['photos'] = $photo_name;
    $set[] = "photos = :photos";
}

$sql = "UPDATE biens SET " . implode(', ', $set) . " WHERE id_bien = :id_bien";
$data['id_bien'] = $id_bien;

try {
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute($data)) {
        echo json_encode(['message' => 'Bien modifié avec succès.']);
    } else {
        echo json_encode(['error' => 'Échec de la mise à jour.']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erreur PDO : ' . $e->getMessage()]);
}
?>
