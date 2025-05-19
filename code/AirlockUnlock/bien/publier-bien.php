<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Méthode HTTP non autorisée']);
    exit();
}

if (!isset($_FILES['photo'])) {
    echo json_encode(['error' => 'Aucun fichier uploadé']);
    exit();
}

$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
$file_extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));

if (!in_array($file_extension, $allowed_extensions)) {
    echo json_encode(['error' => 'Seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés']);
    exit();
}

$photo_name = uniqid('bien_', true) . '.' . $file_extension;
$target_directory = __DIR__ . '/photos/';

// Création du dossier si nécessaire
if (!is_dir($target_directory)) {
    if (!mkdir($target_directory, 0775, true)) {
        echo json_encode(['error' => 'Erreur lors de la création du répertoire']);
        exit();
    }
}

// Double vérification permissions (écriture)
if (!is_writable($target_directory)) {
    echo json_encode(['error' => 'Le répertoire cible n’est pas accessible en écriture']);
    exit();
}

$target_file = $target_directory . $photo_name;

// Sécurité : vérifier que le fichier uploadé est bien une image
if (!getimagesize($_FILES['photo']['tmp_name'])) {
    echo json_encode(['error' => 'Le fichier uploadé n\'est pas une image valide']);
    exit();
}

if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
    echo json_encode(['error' => 'Erreur lors du téléchargement de l\'image']);
    exit();
}

// Construction de l'URL complète
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$port = $_SERVER['SERVER_PORT'];
$baseUrl = "$protocol://$host";
if (!in_array($port, [80, 443])) {
    $baseUrl .= ":$port";
}
$baseUrl .= dirname($_SERVER['PHP_SELF']) . '/photos/';
$image_url = $baseUrl . $photo_name;

echo json_encode([
    'message' => 'Image uploadée avec succès',
    'image_url' => $image_url
]);