<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config.php';
include __DIR__ . '/../../Tapkey/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$token = null;
if (isset($_COOKIE['auth_token']) && !empty($_COOKIE['auth_token'])) {
    $token = $_COOKIE['auth_token'];
} else {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
    }
}

if (!$token) {
    echo json_encode(['error' => 'Token d\'authentification manquant.']);
    exit();
}

$key = "votre_cle_secrete";

try {
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    $id_proprietaire = $decoded->id_proprietaire;
    $email_proprietaire = $decoded->email ?? null;
} catch (Exception $e) {
    echo json_encode(['error' => 'Token invalide : ' . $e->getMessage()]);
    exit();
}

$stmt = $pdo->prepare("SELECT nom FROM proprietaires WHERE id_proprietaire = :id_proprietaire");
$stmt->bindParam(':id_proprietaire', $id_proprietaire, PDO::PARAM_INT);
$stmt->execute();
$nom_proprietaire = $stmt->fetchColumn();

if (!$nom_proprietaire) {
    echo json_encode(['error' => 'Propriétaire introuvable.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type_bien = $_POST['type_bien'];
    $titre = $_POST['titre'];
    $prix_par_nuit = $_POST['prix_par_nuit'];
    $description = $_POST['description'];
    $surface = $_POST['surface'];
    $nombre_pieces = $_POST['nombre_pieces'];
    $capacite = $_POST['capacite'];
    $adresse = $_POST['adresse'];
    $wifi = isset($_POST['wifi']) ? 1 : 0;
    $parking = isset($_POST['parking']) ? 1 : 0;
    $cuisine = isset($_POST['cuisine']) ? 1 : 0;
    $tv = isset($_POST['tv']) ? 1 : 0;
    $climatisation = isset($_POST['climatisation']) ? 1 : 0;
    $chauffage = isset($_POST['chauffage']) ? 1 : 0;
    $serrure_electronique = isset($_POST['serrure_electronique']) ? 1 : 0;
    $numero_serie_tapkey = $_POST['numero_serie_tapkey'];

    try {
        if (!empty($numero_serie_tapkey)) {
            $numero = trim($numero_serie_tapkey);
            $stmt = $pdo_tapkey->prepare("SELECT COUNT(*) FROM Tapkey.cles_electroniques WHERE numero_serie = :numero");
            $stmt->execute([':numero' => $numero]);
            if ($stmt->fetchColumn() == 0) {
                echo json_encode(['error' => 'Ce numéro de série Tapkey n’existe pas.']);
                exit();
            }

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM biens WHERE numero_serie_tapkey = :numero");
            $stmt->execute([':numero' => $numero]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['error' => 'Ce numéro de série Tapkey est déjà utilisé.']);
                exit();
            }
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erreur PDO Tapkey : ' . $e->getMessage()]);
        exit();
    }

    if (isset($_FILES['photos']) && $_FILES['photos']['error'] === 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($_FILES['photos']['name'], PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_extensions)) {
            echo json_encode(['error' => 'Erreur : seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés.']);
            exit();
        }

        $photo_name = str_replace('.', '_', uniqid('bien_', true)) . '.' . $file_extension;
        $target_directory = '/var/www/html/AirlockUnlock/bien/photos/';

        if (!is_dir($target_directory)) {
            if (!mkdir($target_directory, 0775, true)) {
                echo json_encode(['error' => 'Erreur lors de la création du répertoire.']);
                exit();
            }
        }

        if (!is_writable($target_directory)) {
            echo json_encode(['error' => 'Le répertoire cible n’est pas accessible en écriture.']);
            exit();
        }

        $target_file = $target_directory . $photo_name;

        if (!move_uploaded_file($_FILES['photos']['tmp_name'], $target_file)) {
            echo json_encode(['error' => 'Erreur lors du téléchargement de l\'image.']);
            exit();
        }

        $host = $_SERVER['HTTP_HOST'];
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $baseUrl = "$protocol://$host/airlockunlock/bien/photos/";
        $image_url = $baseUrl . $photo_name;
    } else {
        $photo_name = null;
        $image_url = null;
    }

    $sql = "INSERT INTO biens (
                id_proprietaire, type_bien, titre, prix_par_nuit, description, surface, nombre_pieces,
                capacite, adresse, photos, wifi, parking, cuisine, tv, climatisation, chauffage,
                serrure_electronique, numero_serie_tapkey
            ) VALUES (
                :id_proprietaire, :type_bien, :titre, :prix_par_nuit, :description, :surface, :nombre_pieces,
                :capacite, :adresse, :photos, :wifi, :parking, :cuisine, :tv, :climatisation, :chauffage,
                :serrure_electronique, :numero_serie_tapkey
            )";

    try {
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':id_proprietaire', $id_proprietaire);
        $stmt->bindParam(':type_bien', $type_bien);
        $stmt->bindParam(':titre', $titre);
        $stmt->bindParam(':prix_par_nuit', $prix_par_nuit);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':surface', $surface);
        $stmt->bindParam(':nombre_pieces', $nombre_pieces);
        $stmt->bindParam(':capacite', $capacite);
        $stmt->bindParam(':adresse', $adresse);
        $stmt->bindParam(':photos', $photo_name);
        $stmt->bindParam(':wifi', $wifi);
        $stmt->bindParam(':parking', $parking);
        $stmt->bindParam(':cuisine', $cuisine);
        $stmt->bindParam(':tv', $tv);
        $stmt->bindParam(':climatisation', $climatisation);
        $stmt->bindParam(':chauffage', $chauffage);
        $stmt->bindParam(':serrure_electronique', $serrure_electronique);
        $stmt->bindParam(':numero_serie_tapkey', $numero_serie_tapkey);

        if ($stmt->execute()) {
            echo json_encode([
                'message' => 'Bien publié avec succès !',
                'image_url' => $image_url
            ]);
        } else {
            echo json_encode(['error' => 'Erreur lors de la publication du bien.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erreur PDO : ' . $e->getMessage()]);
    }
}
?>
