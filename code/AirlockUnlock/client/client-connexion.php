<?php
include '../config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Vérification des identifiants (Connexion)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $inputData = json_decode(file_get_contents("php://input"), true);

    if (!isset($inputData['email'], $inputData['password'])) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur: Données manquantes.']);
        exit();
    }

    $email = $inputData['email'];
    $password = $inputData['password'];

    try {
        $stmt = $pdo->prepare("SELECT id, password, username FROM inscription_clients WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            echo json_encode(['status' => 'success', 'message' => 'Connexion réussie.', 'user_id' => $user['id'],'username' => $user['username']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Email ou mot de passe incorrect.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la connexion : ' . $e->getMessage()]);
    }
    exit();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Méthode HTTP non autorisée. Utilisez POST.']);
}

$pdo = null;
?>
