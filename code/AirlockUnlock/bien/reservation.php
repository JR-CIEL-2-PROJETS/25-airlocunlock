<?php
// Connexion à la base de données
include '../config.php';

// Traitement de la réservation si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bien_id = $_POST['bien_id'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $nombre_personnes = $_POST['nombre_personnes'];

    // Vérification des champs requis
    if (!empty($bien_id) && !empty($date_debut) && !empty($date_fin) && !empty($nombre_personnes)) {
        try {
            // Requête d'insertion dans la table reservation_bien
            $sql = "INSERT INTO reservation_bien (bien_id, date_debut, date_fin, nombre_personnes) 
                    VALUES (:bien_id, :date_debut, :date_fin, :nombre_personnes)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':bien_id' => $bien_id,
                ':date_debut' => $date_debut,
                ':date_fin' => $date_fin,
                ':nombre_personnes' => $nombre_personnes
            ]);

            echo "<p>Réservation enregistrée avec succès !</p>";
        } catch (PDOException $e) {
            echo "<p>Erreur lors de l'enregistrement : " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>Veuillez remplir tous les champs.</p>";
    }
}

// Récupération des biens pour affichage
$sql = "SELECT * FROM publier_bien";
$stmt = $pdo->query($sql);
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation d'appartement</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            width: 80%;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        .property-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            border: 1px solid #ccc;
            padding: 20px;
            margin-top: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .property-card img {
            max-width: 100%;
            border-radius: 10px;
        }

        form {
            margin-top: 20px;
            text-align: center;
        }

        input[type="date"],
        input[type="number"],
        button {
            padding: 10px;
            margin: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #007BFF;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Choisis ta date !</h2>

    <!-- Liste des biens -->
    <?php if ($properties): ?>
        <?php foreach ($properties as $property): ?>
            <div class="property-card">
                <h3><?= htmlspecialchars($property['property_name']) ?></h3>
                <p><strong>Lieu :</strong> <?= htmlspecialchars($property['location']) ?></p>
                <p><?= htmlspecialchars($property['description']) ?></p>
                <p><strong>Prix :</strong> <?= htmlspecialchars($property['price']) ?> €</p>
                <p><strong>Chambres :</strong> <?= htmlspecialchars($property['rooms']) ?></p>
                <img src="<?= htmlspecialchars($property['image_url']) ?>" alt="Image du bien">
                
                <!-- Formulaire de réservation -->
                <form action="" method="POST">
                    <input type="hidden" name="bien_id" value="<?= htmlspecialchars($property['id']) ?>">
                    <label for="date_debut">Date de début :</label>
                    <input type="date" name="date_debut" required>
                    <br>
                    <label for="date_fin">Date de fin :</label>
                    <input type="date" name="date_fin" required>
                    <br>
                    <label for="nombre_personnes">Nombre de personnes :</label>
                    <input type="number" name="nombre_personnes" min="1" required>
                    <br>
                    <button type="submit">Réserver</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucun bien disponible pour la réservation.</p>
    <?php endif; ?>
</div>

</body>
</html>
