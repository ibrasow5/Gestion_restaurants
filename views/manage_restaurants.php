<?php
session_start();
include_once '../config/config.php';
include_once 'header.php';

// Vérifier si l'utilisateur est connecté et est administrateur
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

// Charger et afficher les restaurants depuis le fichier XML
$xml = simplexml_load_file('../exo7.xml');

// Ajouter un restaurant
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_restaurant'])) {
    $dom = dom_import_simplexml($xml)->ownerDocument;
    $dom->formatOutput = true;

    $newRestaurant = $xml->addChild('Restaurant');
    $newRestaurant->addChild('Nom', $_POST['nom']);
    $newRestaurant->addChild('Adresse', $_POST['adresse']);
    $newRestaurant->addChild('Restaurateur', $_POST['restaurateur']);

    // Ajouter la description
    $description = $newRestaurant->addChild('Description');
    $paragraphe = $description->addChild('Paragraphe');
    $paragraphe->addChild('Texte', $_POST['description']);

    // Ajouter la spécialité
    $important = $paragraphe->addChild('Important', $_POST['specialite']);

    // Ajouter les caractéristiques
    $liste = $paragraphe->addChild('Liste');
    $caracteristiques = explode(',', $_POST['caracteristiques']);
    foreach ($caracteristiques as $item) {
        $liste->addChild('Item', trim($item));
    }

    // Ajouter l'image avec attribut 'url'
    if (!empty($_POST['image_url'])) {
        $image = $paragraphe->addChild('Image');
        $image->addAttribute('url', $_POST['image_url']);
    }

    // Ajout de la carte des plats
    $carte = $newRestaurant->addChild('Carte');
    foreach ($_POST['plats'] as $plat) {
        $nouveauPlat = $carte->addChild('Plat');
        $nouveauPlat->addChild('Nom', $plat['nom']);
        $nouveauPlat->addChild('Type', $plat['type']);
        $nouveauPlat->addChild('Prix', $plat['prix']);
        $nouveauPlat->addChild('Description', $plat['description']);
    }

    // Enregistrer le fichier XML avec un formatage lisible
    $dom->save('../exo7.xml');

    // Rediriger vers la page de gestion après l'ajout
    header('Location: manage_restaurants.php');
    exit();
}

// Supprimer un restaurant
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_restaurant'])) {
    $restaurantIndex = 0;
    foreach ($xml->Restaurant as $restaurant) {
        // Comparaison avec l'attribut 'id' du restaurant
        if ((int)$restaurant['id'] == (int)$_POST['restaurant_id']) {
            unset($xml->Restaurant[$restaurantIndex]);

            $dom = dom_import_simplexml($xml)->ownerDocument;
            $dom->formatOutput = true;
            $dom->save('../exo7.xml');
            break;
        }
        $restaurantIndex++;
    }

    // Rediriger vers la page de gestion après la suppression
    header('Location: manage_restaurants.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer les restaurants</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .container {
            width: 80%;
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

        button {
            padding: 10px 15px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100px;
            text-align: center;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        table th {
            background-color: #f2f2f2;
        }

        table td {
            background-color: #fff;
        }

        table td form {
            display: inline-block; /* Utiliser inline-block pour permettre l'espacement */
            margin-right: 5px;
        }

        table td form:first-child {
            margin-bottom: 5px; /* Espacement vertical sous le premier formulaire */
        }

        table td form button {
            margin: 0 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Gérer les restaurants</h2>
        <h3>Ajouter un restaurant</h3>
        <form method="post" action="">
            <input type="hidden" name="add_restaurant" value="1">
            <label for="nom">Nom:</label>
            <input type="text" id="nom" name="nom" required>
            <label for="adresse">Adresse:</label>
            <input type="text" id="adresse" name="adresse" required>
            <label for="restaurateur">Restaurateur:</label>
            <input type="text" id="restaurateur" name="restaurateur" required>
            <label for="specialite">Spécialité:</label>
            <input type="text" id="specialite" name="specialite" required>
            <label for="caracteristiques">Caractéristiques (séparées par des virgules):</label>
            <input type="text" id="caracteristiques" name="caracteristiques" required>
            <label for="image_url">URL de l'image:</label>
            <input type="text" id="image_url" name="image_url">
            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>
            <h4>Carte des plats</h4>
            <label for="type_plat">Type de plat:</label>
            <select id="type_plat" name="plats[0][type]" required>
                <option value="Entree">Entrée</option>
                <option value="Plat">Plat</option>
                <option value="Dessert">Dessert</option>
                <option value="Fromage">Fromage</option>
            </select>
            <div id="plats">
                <div>
                    <h4>Plat 1</h4>
                    <label>Nom:</label>
                    <input type="text" name="plats[0][nom]" required>
                    <label>Prix:</label>
                    <input type="text" name="plats[0][prix]" required>
                    <label>Description:</label>
                    <textarea name="plats[0][description]" required></textarea>
                </div>
            </div>
            <button type="button" onclick="ajouterPlat()">Ajouter un plat</button>
            <br>
            <button type="submit">Ajouter le restaurant</button>
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
                        <label>Prix:</label>
                        <input type="text" name="plats[${numPlat - 1}][prix]" required>
                        <label>Description:</label>
                        <textarea name="plats[${numPlat - 1}][description]" required></textarea>
                    </div>
                `;
                divPlats.insertAdjacentHTML('beforeend', html);
            }
        </script>
    </div>
    <h3>Restaurants existants</h3>
        <table>
            <tr>
                <th>Nom</th>
                <th>Adresse</th>
                <th>Restaurateur</th>
                <th>Spécialité</th>
                <th>Caractéristiques</th>
                <th>Description</th>
                <th>Image</th>
                <th>Carte des plats</th>
                <th>Action</th>
            </tr>
            <?php foreach ($xml->Restaurant as $restaurant) { ?>
                <tr>
                    <td><?php echo $restaurant->Nom; ?></td>
                    <td><?php echo $restaurant->Adresse; ?></td>
                    <td><?php echo $restaurant->Restaurateur; ?></td>
                    <td><?php echo $restaurant->Description->Paragraphe->Important; ?></td>
                    <td>
                        <?php
                        $caracteristiques = [];
                        foreach ($restaurant->Description->Paragraphe->Liste->Item as $item) {
                            $caracteristiques[] = $item;
                        }
                        echo implode(', ', $caracteristiques);
                        ?>
                    </td>
                    <td><?php echo $restaurant->Description->Paragraphe->Texte; ?></td>
                    <td>
                        <?php if (isset($restaurant->Description->Paragraphe->Image)) { ?>
                            <img src="<?php echo $restaurant->Description->Paragraphe->Image['url']; ?>" alt="Image" width="100">
                        <?php } else { ?>
                            N/A
                        <?php } ?>
                    </td>
                    <td>
                        <?php foreach ($restaurant->Carte->Plat as $plat) { ?>
                            <strong><?php echo $plat->Nom; ?></strong> (<?php echo $plat->Type; ?>): <?php echo $plat->Prix; ?> CFA<br>
                            <em><?php echo $plat->Description; ?></em><br>
                        <?php } ?>
                    </td>
                    <td>
                    <form method="post" action="">
                        <input type="hidden" name="delete_restaurant" value="1">
                        <input type="hidden" name="restaurant_id" value="<?php echo $restaurant['id']; ?>">
                        <button type="submit">Supprimer</button>
                    </form>
                    <form method="post" action="update_restaurant.php">
                        <input type="hidden" name="restaurant_id" value="<?php echo $restaurant['id']; ?>">
                        <button type="submit">Modifier</button>
                    </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>
