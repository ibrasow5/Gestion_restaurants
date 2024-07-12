<?php
session_start();
include_once 'config/config.php';
include_once 'models/User.php';
include_once 'views/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $user = new User();
    if ($user->register($username, $password, 'visitor')) { // Par défaut, le rôle est 'visitor'
        // Redirection vers la page de connexion après inscription réussie
        header('Location: login.php');
        exit(); // Assurez-vous de terminer le script après redirection
    } else {
        $error = "Erreur lors de l'inscription. Veuillez réessayer.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Inscription</h2>
        <?php if (isset($error)): ?>
            <p><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="post" action="register.php">
            <label for="username">Nom d'utilisateur :</label>
            <input type="text" id="username" name="username" required>
            <br>
            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required>
            <br>
            <button type="submit">S'inscrire</button>
        </form>
        <p>Déjà inscrit? <a href="login.php">Connectez-vous ici</a></p>
    </div>
</body>
</html>

<?php include 'views/footer.php'; ?>
