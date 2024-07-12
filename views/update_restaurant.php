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

       // Récupérer les caractéristiques actuelles
        $paragrapheListe = $restaurant_to_update->Description->Paragraphe->Liste;

        // Créer un tableau pour les nouvelles caractéristiques
        $newCaracteristiques = $_POST['caracteristiques'];
        $newCaracteristiques = array_map('trim', $newCaracteristiques); // Nettoyer les espaces autour des entrées

        // Ajouter les nouvelles caractéristiques si elles n'existent pas déjà
        foreach ($newCaracteristiques as $item) {
            $existing = false;
            foreach ($paragrapheListe->Item as $existingItem) {
                if (trim((string)$existingItem) === trim($item)) {
                    $existing = true;
                    break;
                }
            }
            
            if (!$existing && !empty($item)) {
                $paragrapheListe->addChild('Item', htmlspecialchars($item));
            }
        }

        // Sauvegarder les modifications dans le fichier XML
        $xml->asXML($xmlFile);

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
        if (isset($_POST['plats'])) {
            // Réinitialiser l'index des plats pour assurer la correspondance avec le formulaire
            $xml->Carte->Plat = null;

            foreach ($_POST['plats'] as $plat) {
                // Vérifier si un plat avec le même nom existe déjà
                $existingPlat = null;
                foreach ($restaurant_to_update->Carte->Plat as $xmlPlat) {
                    if ((string) $xmlPlat->Nom === $plat['nom']) {
                        $existingPlat = $xmlPlat;
                        break;
                    }
                }

                if ($existingPlat) {
                    // Mettre à jour le plat existant
                    $existingPlat->Type = htmlspecialchars($plat['type']);
                    $existingPlat->Prix = htmlspecialchars($plat['prix']);
                    $existingPlat->Description = htmlspecialchars($plat['description']);
                } else {
                    // Ajouter un nouveau plat
                    $nouveauPlat = $restaurant_to_update->Carte->addChild('Plat');
                    $nouveauPlat->addChild('Nom', htmlspecialchars($plat['nom']));
                    $nouveauPlat->addChild('Type', htmlspecialchars($plat['type']));
                    $nouveauPlat->addChild('Prix', htmlspecialchars($plat['prix']));
                    $nouveauPlat->addChild('Description', htmlspecialchars($plat['description']));
                }
            }
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

        input[type="text"], textarea, select {
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
            width: 110px;
            text-align: center;
            margin-top: 10px;
            margin-bottom: 10px;
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
            <input type="hidden" name="restaurant_id" value="<?php echo htmlspecialchars($restaurant_to_update['id']); ?>">
            <label for="nom">Nom:</label>
            <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($restaurant_to_update->Nom); ?>" required>
            <label for="adresse">Adresse:</label>
            <input type="text" id="adresse" name="adresse" value="<?php echo htmlspecialchars($restaurant_to_update->Adresse); ?>" required>
            <label for="restaurateur">Restaurateur:</label>
            <input type="text" id="restaurateur" name="restaurateur" value="<?php echo htmlspecialchars($restaurant_to_update->Restaurateur); ?>" required>
            <label for="specialite">Spécialité:</label>
            <input type="text" id="specialite" name="specialite" value="<?php echo htmlspecialchars($restaurant_to_update->Description->Paragraphe->Important); ?>" required>
            <label>Caractéristiques:</label>
            <div id="caracteristiques">
                <?php foreach ($restaurant_to_update->Description->Paragraphe->Liste->Item as $index => $item) { ?>
                    <input type="text" name="caracteristiques[]" value="<?php echo htmlspecialchars($item); ?>" required>
                <?php } ?>
            </div>
            <button type="button" onclick="ajouterCaracteristique()">Ajouter une caractéristique</button>
            <label for="image_url">URL de l'image:</label>
            <input type="text" id="image_url" name="image_url" value="<?php echo isset($restaurant_to_update->Description->Paragraphe->Image) ? htmlspecialchars($restaurant_to_update->Description->Paragraphe->Image['url']) : ''; ?>">
            <label for="description">Description:</label>
            <textarea id="description" name="description" required><?php echo htmlspecialchars($restaurant_to_update->Description->Paragraphe->Texte); ?></textarea>
            <h4>Carte des plats</h4>
            <div id="plats">
                <?php foreach ($restaurant_to_update->Carte->Plat as $index => $plat) { ?>
                    <div>
                        <h4>Plat</h4>
                        <label>Nom:</label>
                        <input type="text" name="plats[<?php echo $index; ?>][nom]" value="<?php echo htmlspecialchars($plat->Nom); ?>" required>
                        <label>Type de plat:</label>
                        <select name="plats[<?php echo $index; ?>][type]" required>
                            <option value="Entrée" <?php if ($plat->Type == 'Entrée') echo 'selected'; ?>>Entrée</option>
                            <option value="Plat" <?php if ($plat->Type == 'Plat') echo 'selected'; ?>>Plat</option>
                            <option value="Dessert" <?php if ($plat->Type == 'Dessert') echo 'selected'; ?>>Dessert</option>
                            <option value="Fromage" <?php if ($plat->Type == 'Fromage') echo 'selected'; ?>>Fromage</option>
                        </select>
                        <label>Prix:</label>
                        <input type="text" name="plats[<?php echo $index; ?>][prix]" value="<?php echo htmlspecialchars($plat->Prix); ?>" required>
                        <label>Description:</label>
                        <textarea name="plats[<?php echo $index; ?>][description]" required><?php echo htmlspecialchars($plat->Description); ?></textarea>
                    </div>
                <?php } ?>
            </div>
            <button type="button" onclick="ajouterPlat()">Ajouter un plat</button>
            <button type="submit">Enregistrer</button>
        </form>
    </div>

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
                        <option value="Entrée">Entrée</option>
                        <option value="Plat">Plat</option>
                        <option value="Dessert">Dessert</option>
                        <option value="Fromage">Fromage</option>
                    </select>
                    <label>Prix:</label>
                    <input type="text" name="plats[${numPlat - 1}][prix]" required>
                    <label>Description:</label>
                    <textarea name="plats[${numPlat - 1}][description]" required></textarea>
                    <button type="button" onclick="supprimerPlat(this)">Supprimer Plat</button>
                </div>
            `;
            divPlats.insertAdjacentHTML('beforeend', html);
        }

        function ajouterCaracteristique() {
            var divCaracteristiques = document.getElementById('caracteristiques');
            var numCaracteristique = divCaracteristiques.querySelectorAll('input').length + 1;

            var html = `
                <div>
                    <label for="caracteristique_${numCaracteristique}"></label>
                    <input type="text" id="caracteristique_${numCaracteristique}" name="caracteristiques[]" required><br>
                    <button type="button" onclick="supprimerCaracteristique(this)">Supprimer Caracteristique</button><br>
                </div>
            `;
            divCaracteristiques.insertAdjacentHTML('beforeend', html);
        }

        function supprimerCaracteristique(button) {
            button.parentNode.remove();
        }

        function supprimerPlat(button) {
            button.parentNode.remove();
        }
    </script>
</body>
</html>
