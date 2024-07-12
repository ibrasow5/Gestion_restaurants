<?php
session_start();
include_once 'config/config.php';
include_once 'models/User.php';
include_once 'views/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil</title>
    <style>
        .container {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .container h1 {
            color: #333;
        }

        .container p {
            color: #666;
        }

        .container a {
            display: inline-block;
            margin: 10px;
            padding: 10px 20px;
            color: #fff;
            background-color: #007bff;
            text-decoration: none;
            border-radius: 5px;
        }

        .container a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bienvenue sur le portail restaurant</h1>
        <p><a href="login.php">Se connecter</a> ou <a href="register.php">S'inscrire</a></p>
    </div>
</body>
</html>

<?php include 'views/footer.php'; ?>
