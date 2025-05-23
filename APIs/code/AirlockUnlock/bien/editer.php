<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Autoriser OPTIONS pour CORS prévol (si utilisé)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Methods: PUT, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    exit(0);
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config.php';
include __DIR__ . '/../../Tapkey/config.php';

// Vérifie que la méthode est PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    echo json_encode(['error' => 'Méthode HTTP non autorisée, utilisez PUT']);
    exit();
}

// Récupère les données JSON du corps de la requête
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['error' => 'Données JSON invalides ou manquantes']);
    exit();
}

if (!isset($input['id_bien']) || !is_numeric($input['id_bien'])) {
    echo json_encode(['error' => "L'ID du bien est requis pour la modification."]);
    exit();
}

$id_bien = (int)$input['id_bien'];

$fields = [
    'type_bien', 'titre', 'prix_par_nuit', 'description',
    'surface', 'nombre_pieces', 'capacite', 'adresse',
    'wifi', 'parking', 'cuisine', 'tv', 'climatisation',
    'chauffage', 'serrure_electronique', 'numero_serie_tapkey', 'photos'
];

$data = [];
$set = [];

// Champs à traiter (sauf cases à cocher)
foreach ($fields as $field) {
    if (isset($input[$field])) {
        $data[$field] = $input[$field];
        $set[] = "$field = :$field";
    }
}

// Traite les checkbox (booléens)
$checkboxFields = ['wifi', 'parking', 'cuisine', 'tv', 'climatisation', 'chauffage', 'serrure_electronique'];
foreach ($checkboxFields as $field) {
    // Si champ non présent dans JSON, on considère 0 (désactivé)
    $data[$field] = !empty($input[$field]) ? 1 : 0;
    // S'assurer qu'on a bien ajouté ces champs à la requête
    if (!in_array("$field = :$field", $set)) {
        $set[] = "$field = :$field";
    }
}

// Vérifie la clé Tapkey si renseignée
if (!empty($input['numero_serie_tapkey'])) {
    $numero = strtoupper(trim($input['numero_serie_tapkey'])); // Normalisation

    try {
        // Connexion à la BDD Tapkey (tu dois avoir $pdo_tapkey dans config.php)
        $stmt = $pdo_tapkey->prepare("SELECT COUNT(*) FROM Tapkey.cles_electroniques WHERE numero_serie = :numero");
        $stmt->execute([':numero' => $numero]);
        if ($stmt->fetchColumn() == 0) {
            echo json_encode(['error' => 'Ce numéro de série Tapkey n’existe pas.']);
            exit();
        }

        // Récupérer le numéro série actuel dans la base pour ce bien
        $stmt = $pdo->prepare("SELECT numero_serie_tapkey FROM biens WHERE id_bien = :id_bien");
        $stmt->execute([':id_bien' => $id_bien]);
        $ancien_numero = $stmt->fetchColumn();

        if ($ancien_numero === false) {
            echo json_encode(['error' => 'Bien introuvable pour vérification du numéro série Tapkey']);
            exit();
        }

        // Normaliser aussi l'ancien numéro pour comparaison
        $ancien_numero = strtoupper(trim($ancien_numero));

        // Si le numéro a changé, vérifier qu'il n'est pas déjà utilisé
        if ($numero !== $ancien_numero) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM biens WHERE numero_serie_tapkey = :numero AND id_bien != :id_bien");
            $stmt->execute([':numero' => $numero, ':id_bien' => $id_bien]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['error' => 'Ce numéro de série Tapkey est déjà utilisé pour un autre bien.']);
                exit();
            }
        }
        // Sinon, pas de problème, on continue

    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erreur PDO Tapkey : ' . $e->getMessage()]);
        exit();
    }
}

// Pas de gestion des fichiers photos ici, car en PUT JSON on ne peut pas envoyer de fichiers
// Si tu veux gérer upload d'image, il faudra une route séparée en POST multipart/form-data

// Requête de mise à jour
$sql = "UPDATE biens SET " . implode(', ', $set) . " WHERE id_bien = :id_bien";
$data['id_bien'] = $id_bien;

try {
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute($data)) {
        echo json_encode(['success' => true, 'message' => 'Bien modifié avec succès.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Échec de la mise à jour.']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erreur PDO : ' . $e->getMessage()]);
}
