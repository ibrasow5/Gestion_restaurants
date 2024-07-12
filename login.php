<?php
session_start();
include_once 'config/config.php';
include_once 'models/User.php';
include_once 'views/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $user = new User();
    if ($user->login($username, $password)) {
        if ($_SESSION['user']['role'] == 'admin') {
            header('Location: views/index.php');
        } else {
            header('Location: views/index.php');
        }
        exit();
    } else {
        $error = "Nom d'utilisateur ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
<div class="container">
    <h2>Se connecter</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="post" action="">
        <label for="username">Nom d'utilisateur:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Mot de passe:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Se connecter</button>
    </form>
    <p>Pas encore de compte? <a href="register.php">Inscrivez-vous ici</a></p>
</div>
</body>
</html>

<?php include 'views/footer.php'; ?>
