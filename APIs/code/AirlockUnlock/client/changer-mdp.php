<?php
include '../config.php';
header('Content-Type: application/json; charset=utf-8');

$email = $_POST['email'] ?? '';
$newPassword = $_POST['nouveau_mdp'] ?? '';

if (empty($email)) {
    echo json_encode(["status" => "error", "message" => "Email requis."]);
    exit();
}

function findUserByEmail($pdo, $email, $tableName, $idColumn = 'id_client') {
    $stmt = $pdo->prepare("SELECT $idColumn FROM $tableName WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updatePassword($pdo, $email, $tableName) {
    global $newPassword;
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE $tableName SET mot_de_passe = :mdp WHERE email = :email");
    $stmt->bindParam(':mdp', $hash);
    $stmt->bindParam(':email', $email);
    return $stmt->execute();
}

if (!empty($email) && empty($newPassword)) {
    $client = findUserByEmail($pdo, $email, 'clients', 'id_client');
    if ($client) {
        echo json_encode(["status" => "success", "message" => "Email trouvé dans clients."]);
        exit();
    }
    $proprietaire = findUserByEmail($pdo, $email, 'proprietaires', 'id_proprietaire');
    if ($proprietaire) {
        echo json_encode(["status" => "success", "message" => "Email trouvé dans proprietaires."]);
        exit();
    }
    echo json_encode(["status" => "error", "message" => "Aucun utilisateur trouvé avec cette adresse e-mail."]);
    exit();
}

if (!empty($email) && !empty($newPassword)) {
    $client = findUserByEmail($pdo, $email, 'clients', 'id_client');
    if ($client) {
        if (updatePassword($pdo, $email, 'clients')) {
            echo json_encode(["status" => "success", "message" => "Mot de passe mis à jour avec succès (clients)."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Erreur lors de la mise à jour du mot de passe."]);
        }
        exit();
    }
    $proprietaire = findUserByEmail($pdo, $email, 'proprietaires', 'id_proprietaire');
    if ($proprietaire) {
        if (updatePassword($pdo, $email, 'proprietaires')) {
            echo json_encode(["status" => "success", "message" => "Mot de passe mis à jour avec succès (proprietaires)."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Erreur lors de la mise à jour du mot de passe."]);
        }
        exit();
    }
    echo json_encode(["status" => "error", "message" => "Aucun utilisateur trouvé avec cette adresse e-mail."]);
    exit();
}

echo json_encode(["status" => "error", "message" => "Email requis, ou email + mot de passe pour la mise à jour."]);
exit();
