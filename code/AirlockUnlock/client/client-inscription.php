<?php
include '../config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Inscription d'un nouveau client
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $inputData = json_decode(file_get_contents("php://input"), true);

    if (isset($inputData['nom'], $inputData['username'], $inputData['email'], $inputData['password'], $inputData['phone'])) {
        $nom = $inputData['nom'];
        $username = $inputData['username'];
        $email = $inputData['email'];
        $password = $inputData['password'];
        $phone = $inputData['phone'];

        if (empty($nom) || empty($username) || empty($email) || empty($password) || empty($phone)) {
            echo json_encode(['status' => 'error', 'message' => 'Erreur: Tous les champs sont requis.']);
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Erreur: Adresse email invalide.']);
            exit();
        }

        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO inscription_clients (nom, username, email, password, phone) 
                VALUES (:nom, :username, :email, :password, :phone)";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password_hash);
            $stmt->bindParam(':phone', $phone);

            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Client inscrit avec succès.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'inscription.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'exécution de la requête : ' . $e->getMessage()]);
        }
        exit();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erreur: Données manquantes.']);
        exit();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Méthode HTTP non autorisée. Utilisez POST.']);
}

$pdo = null;
?>
