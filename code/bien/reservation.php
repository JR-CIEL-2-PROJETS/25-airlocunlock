<?php
// Connexion à la base de données
include '../config.php';

// Requête pour récupérer tous les biens
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
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            margin-top: 50px;
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

        .property-card button {
            background-color: transparent;
            border: none;
            cursor: pointer;
            padding: 10px;
            margin-top: 20px;
        }

        .property-card button img {
            width: 150px;
            height: auto;
            border-radius: 5px;
            transition: transform 0.3s ease;
        }

        .property-card button img:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Appartements disponibles à la réservation</h2>

        <!-- Vérifier si des propriétés existent dans la base de données -->
        <?php
        if ($properties) {
            foreach ($properties as $property) {
                echo "<div class='property-card'>
                        <h3>" . htmlspecialchars($property['property_name']) . "</h3>
                        <p><strong>Lieu: </strong>" . htmlspecialchars($property['location']) . "</p>
                        <p>" . htmlspecialchars($property['description']) . "</p>
                        <p><strong>Prix: </strong>" . htmlspecialchars($property['price']) . " €</p>
                        <p><strong>Chambres: </strong>" . htmlspecialchars($property['rooms']) . "</p>
                        <!-- Affichage de l'image du bien -->
                        <img src='" . htmlspecialchars($property['image_url']) . "' alt='Image du bien'>
                        <form action='reservation.php' method='GET'>
                            <input type='hidden' name='id' value='" . htmlspecialchars($property['id']) . "'>
                            <button type='submit'>Réserver</button>
                        </form>
                    </div>";
            }
        } else {
            echo "<p>Aucun bien trouvé à la réservation.</p>";
        }
        ?>
    </div>

</body>
</html>
