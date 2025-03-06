<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'header.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="../views/css/style.css"> <!-- Chemin vers la feuille de style -->
    <style>
        .btn-create-compte {
            background-color: #34dbb1;
            /* Blue */
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Connexion</h2>

        <!-- Message d'erreur en cas d'échec de connexion -->
        <?php if (isset($erreur)) : ?>
            <p class="error-message"><?= htmlspecialchars($erreur) ?></p>
        <?php endif; ?>

        <!-- Formulaire de connexion -->
        <form action="../controllers/ConnexionController.php" method="POST">
            <label for="nom_utilisateur">Nom d'utilisateur :</label>
            <input type="text" name="nom_utilisateur" id="nom_utilisateur" required>

            <label for="mot_de_passe">Mot de passe :</label>
            <input type="password" name="mot_de_passe" id="mot_de_passe" required>

            <button type="submit" class="btn-submit">Se connecter</button>
        </form>

        <!-- Lien pour créer un compte -->
        <p class="create-account-container">
            Pas encore de compte ? <a href="../controllers/InscriptionController.php" class="btn btn-create-compte">Créer un compte</a>
        </p>
    </div>

    <!-- Message de succès après inscription -->
    <?php if (isset($_GET['inscription']) && $_GET['inscription'] === 'success') : ?>
        <div class="success-container">
            <p class="success-message">Compte créé avec succès. Vous pouvez maintenant vous connecter.</p>
        </div>
    <?php endif; ?>

    <?php include 'footer.php'; ?>
</body>

</html>