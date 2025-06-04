<?php
include '../config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Pour debug : active l'affichage des erreurs (uniquement en dev)
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $inputJSON = file_get_contents('php://input');
    $data = json_decode($inputJSON, true);

    if (!$data || !isset($data['nom'], $data['email'], $data['mot_de_passe'], $data['telephone'])) {
        echo json_encode(['message' => 'Tous les champs sont requis.']);
        exit();
    }

    $nom = trim($data['nom']);
    $email = trim($data['email']);
    $mot_de_passe = $data['mot_de_passe'];
    $telephone = trim($data['telephone']);

    if (empty($nom) || empty($email) || empty($mot_de_passe) || empty($telephone)) {
        echo json_encode(['message' => 'Tous les champs sont requis.']);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['message' => 'Adresse email invalide.']);
        exit();
    }

    if (!preg_match('/^[0-9]{10,15}$/', $telephone)) {
        echo json_encode(['message' => 'Le numéro de téléphone doit être valide (10-15 chiffres).']);
        exit();
    }

    try {
        // Vérifier si l'email existe déjà
        $checkEmailSql = "SELECT COUNT(*) FROM clients WHERE email = :email";
        $stmtCheck = $pdo->prepare($checkEmailSql);
        $stmtCheck->bindParam(':email', $email);
        $stmtCheck->execute();
        $emailExists = $stmtCheck->fetchColumn();

        if ($emailExists > 0) {
            echo json_encode(['message' => 'Cette adresse email est déjà utilisée.']);
            exit();
        }

        // Hasher le mot de passe
        $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_BCRYPT);

        // Insérer le nouveau client
        $sql = "INSERT INTO clients (nom, email, mot_de_passe, telephone) VALUES (:nom, :email, :mot_de_passe, :telephone)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':mot_de_passe', $mot_de_passe_hash);
        $stmt->bindParam(':telephone', $telephone);

        $executed = $stmt->execute();

        if ($executed) {
            echo json_encode(['message' => 'Inscription réussie !']);
        } else {
            $errorInfo = $stmt->errorInfo();
            echo json_encode([
                'message' => 'Erreur lors de l\'inscription.',
                'errorInfo' => $errorInfo
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode(['message' => 'Erreur lors de l\'exécution de la requête : ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['message' => 'Méthode HTTP non autorisée']);
}
