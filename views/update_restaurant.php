<?php
session_start();
include_once '../config/config.php';
include_once 'header.php';

// Vérifier si l'utilisateur est connecté et est administrateur
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

// Charger le fichier XML des restaurants
$xml = simplexml_load_file('../exo7.xml');

// Récupérer l'ID du restaurant à modifier depuis POST
if (isset($_POST['restaurant_id'])) {
    $restaurant_id = (int)$_POST['restaurant_id'];

    // Rechercher le restaurant correspondant dans XML
    $restaurant_to_update = null;
    foreach ($xml->Restaurant as $restaurant) {
        if ((int)$restaurant['id'] == $restaurant_id) {
            $restaurant_to_update = $restaurant;
            break;
        }
    }

    if (!$restaurant_to_update) {
        die('Restaurant non trouvé.');
    }

    // Soumettre le formulaire de mise à jour
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_restaurant'])) {
        // Mettre à jour les données du restaurant dans XML
        $restaurant_to_update->Nom = $_POST['nom'];
        $restaurant_to_update->Adresse = $_POST['adresse'];
        $restaurant_to_update->Restaurateur = $_POST['restaurateur'];
        $restaurant_to_update->Description->Paragraphe->Texte = $_POST['description'];
        $restaurant_to_update->Description->Paragraphe->Important = $_POST['specialite'];

        // Mettre à jour les caractéristiques
        $caracteristiques = explode(',', $_POST['caracteristiques']);
        $restaurant_to_update->Description->Paragraphe->Liste->Item = '';
        foreach ($caracteristiques as $item) {
            $restaurant_to_update->Description->Paragraphe->Liste->addChild('Item', trim($item));
        }

        // Mettre à jour l'image si elle est fournie
        if (!empty($_POST['image_url'])) {
            if (isset($restaurant_to_update->Description->Paragraphe->Image)) {
                $restaurant_to_update->Description->Paragraphe->Image['url'] = $_POST['image_url'];
            } else {
                $image = $restaurant_to_update->Description->Paragraphe->addChild('Image');
                $image->addAttribute('url', $_POST['image_url']);
            }
        }

        // Mettre à jour la carte des plats
        $restaurant_to_update->Carte->Plat = '';
        foreach ($_POST['plats'] as $plat) {
            $nouveauPlat = $restaurant_to_update->Carte->addChild('Plat');
            $nouveauPlat->addChild('Nom', $plat['nom']);
            $nouveauPlat->addChild('Type', $plat['type']);
            $nouveauPlat->addChild('Prix', $plat['prix']);
            $nouveauPlat->addChild('Description', $plat['description']);
        }

        // Sauvegarder les modifications dans le fichier XML
        $xml->asXML('../exo7.xml');

        // Rediriger vers la page de gestion après la mise à jour
        header('Location: manage_restaurants.php');
        exit();
    }
} else {
    die('ID du restaurant non spécifié.');
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un restaurant</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .container {
            width: 50%;
            margin: 20px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2, h3 {
            color: #333;
        }

        form {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .horaires {
            margin-top: 20px;
        }

        .horaires label {
            font-weight: bold;
        }

        .horaires input[type="text"] {
            margin-right: 10px;
        }

        .edit {
            margin-top: 20px;
        }

        button {
            padding: 10px 15px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100px;
            text-align: center;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Modifier un restaurant</h2>
        <form method="post" action="">
            <input type="hidden" name="update_restaurant" value="1">
            <input type="hidden" name="restaurant_id" value="<?php echo $restaurant_to_update['id']; ?>">
            <label for="nom">Nom:</label>
            <input type="text" id="nom" name="nom" value="<?php echo $restaurant_to_update->Nom; ?>" required>
            <label for="adresse">Adresse:</label>
            <input type="text" id="adresse" name="adresse" value="<?php echo $restaurant_to_update->Adresse; ?>" required>
            <label for="restaurateur">Restaurateur:</label>
            <input type="text" id="restaurateur" name="restaurateur" value="<?php echo $restaurant_to_update->Restaurateur; ?>" required>
            <label for="specialite">Spécialité:</label>
            <input type="text" id="specialite" name="specialite" value="<?php echo $restaurant_to_update->Description->Paragraphe->Important; ?>" required>
            <label for="caracteristiques">Caractéristiques (séparées par des virgules):</label>
            <input type="text" id="caracteristiques" name="caracteristiques" value="<?php echo implode(', ', iterator_to_array($restaurant_to_update->Description->Paragraphe->Liste->Item)); ?>" required>
            <label for="image_url">URL de l'image:</label>
            <input type="text" id="image_url" name="image_url" value="<?php echo isset($restaurant_to_update->Description->Paragraphe->Image) ? $restaurant_to_update->Description->Paragraphe->Image['url'] : ''; ?>">
            <label for="description">Description:</label>
            <textarea id="description" name="description" required><?php echo $restaurant_to_update->Description->Paragraphe->Texte; ?></textarea>
            <h4>Carte des plats</h4>
            <div id="plats">
                <?php foreach ($restaurant_to_update->Carte->Plat as $index => $plat) { ?>
                    <div>
                        <h4>Plat <?php echo $index + 1; ?></h4>
                        <label>Nom:</label>
                        <input type="text" name="plats[<?php echo $index; ?>][nom]" value="<?php echo $plat->Nom; ?>" required>
                        <label>Type de plat:</label>
                        <select name="plats[<?php echo $index; ?>][type]" required>
                            <option value="entree" <?php echo ($plat->Type == 'entree') ? 'selected' : ''; ?>>Entrée</option>
                            <option value="plat" <?php echo ($plat->Type == 'plat') ? 'selected' : ''; ?>>Plat</option>
                            <option value="dessert" <?php echo ($plat->Type == 'dessert') ? 'selected' : ''; ?>>Dessert</option>
                            <option value="fromage" <?php echo ($plat->Type == 'fromage') ? 'selected' : ''; ?>>Fromage</option>
                        </select>
                        <label>Prix:</label>
                        <input type="text" name="plats[<?php echo $index; ?>][prix]" value="<?php echo $plat->Prix; ?>" required>
                        <label>Description:</label>
                        <textarea name="plats[<?php echo $index; ?>][description]" required><?php echo $plat->Description; ?></textarea>
                    </div>
                <?php } ?>
            </div>
            <button type="button" onclick="ajouterPlat()">Ajouter un plat</button>
            <br>
            <button type="submit">Mettre à jour le restaurant</button>
        </form>

        <script>
            function ajouterPlat() {
                var divPlats = document.getElementById('plats');
                var numPlat = divPlats.querySelectorAll('div').length + 1;

                var html = `
                    <div>
                        <h4>Plat ${numPlat}</h4>
                        <label>Nom:</label>
                        <input type="text" name="plats[${numPlat - 1}][nom]" required>
                        <label>Type de plat:</label>
                        <select name="plats[${numPlat - 1}][type]" required>
                            <option value="entree">Entrée</option>
                            <option value="plat">Plat</option>
                            <option value="dessert">Dessert</option>
                            <option value="fromage">Fromage</option>
                        </select>
                        <label>Prix:</label>
                        <input type="text" name="plats[${numPlat - 1}][prix]" required>
                        <label>Description:</label>
                        <textarea name="plats[${numPlat - 1}][description]" required></textarea>
                    </div>
                `;

                divPlats.innerHTML += html;
            }
        </script>
    </div>
</body>
</html>