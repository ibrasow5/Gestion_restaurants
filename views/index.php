<?php
session_start();
include_once '../config/config.php';
include_once 'header.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}

// Charger et afficher les restaurants depuis le fichier XML
$xml = simplexml_load_file('../exo7.xml');

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Portail Restaurants</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .restaurant-container {
            display: flex;
            margin-bottom: 20px;
        }
        .restaurant-details {
            flex: 1;
            padding-right: 20px;
        }
        .restaurant-details h3 {
            margin-top: 0;
        }
        .restaurant-affiche {
            flex: 0 0 150px; 
            margin-right: 150px;
        }
        .restaurant-affiche img {
            max-width: 200%; 
            height: auto;
            display: block; 
            margin-bottom: 10px; 
        }
        .restaurant-details p {
            color: #000;
        }
        .restaurant-details h4 {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
<nav>
    <ul>
        <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
            <p><a href="/Gestion_restaurants/views/manage_restaurants.php">Gérer les restaurants</a></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['user'])): ?>
            <li><a href="/Gestion_restaurants/index.php">Déconnexion</a></li>
        <?php else: ?>
            <li><a href="/Gestion_restaurants/login.php">Connexion</a></li>
            <li><a href="/Gestion_restaurants/register.php">Inscription</a></li>
        <?php endif; ?>
    </ul>
</nav>
<div class="container">
    <h2>Restaurants disponibles</h2>
    <?php foreach ($xml->Restaurant as $restaurant) : ?>
        <div class="restaurant-container">
            <div class="restaurant-details">
                <h3><?php echo $restaurant->Nom; ?></h3>
                <p><strong>Adresse:</strong> <?php echo $restaurant->Adresse; ?></p>
                <p><strong>Restaurateur:</strong> <?php echo $restaurant->Restaurateur; ?></p>
                <div class="restaurant-description">
                    <p><?php echo $restaurant->Description->Paragraphe->Texte; ?></p>
                    <p><strong>Spécialité:</strong> <?php echo $restaurant->Description->Paragraphe->Important; ?></p>
                    <h4>Caractéristiques:</h4>
                    <ul>
                        <?php foreach ($restaurant->Description->Paragraphe->Liste->Item as $caracteristique) : ?>
                            <li><?php echo $caracteristique; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <h4>Carte:</h4>
                    <ul>
                        <?php foreach ($restaurant->Carte->Plat as $plat) : ?>
                            <li>
                                <strong><?php echo $plat->Type; ?>:</strong> <?php echo $plat->Nom; ?>
                                <ul>
                                    <li><strong>Prix:</strong> <?php echo $plat->Prix; ?> <?php echo $xml['devise']; ?></li>
                                    <li><?php echo $plat->Description; ?></li>
                                </ul><br>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="restaurant-affiche">
                <img src="<?php echo $restaurant->Description->Paragraphe->Image['url']; ?>" alt="Image du restaurant <?php echo $restaurant->Nom; ?>">
            </div>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>

<?php include 'footer.php'; ?>
