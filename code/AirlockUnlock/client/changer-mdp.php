<?php
include '../config.php';

header('Content-Type: application/json; charset=utf-8');

$email = $_POST['email'] ?? '';
$newPassword = $_POST['nouveau_mdp'] ?? '';

if (empty($email) || empty($newPassword)) {
    echo json_encode(["status" => "error", "message" => "Email et nouveau mot de passe sont requis."]);
    exit();
}

// Vérifier si l'email existe
$stmt = $pdo->prepare("SELECT id_client FROM clients WHERE email = :email");
$stmt->bindParam(':email', $email);
$stmt->execute();
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    echo json_encode(["status" => "error", "message" => "Aucun utilisateur trouvé avec cette adresse e-mail."]);
    exit();
}

// Mettre à jour le mot de passe
$hash = password_hash($newPassword, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE clients SET mot_de_passe = :mdp WHERE email = :email");
$stmt->bindParam(':mdp', $hash);
$stmt->bindParam(':email', $email);
$stmt->execute();

echo json_encode(["status" => "success", "message" => "Mot de passe mis à jour avec succès."]);
