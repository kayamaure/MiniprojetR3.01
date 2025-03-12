<?php require_once 'header.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="../views/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Créer un Compte</h2>
        <?php if (isset($erreur)) : ?>
            <p class="error-message"><?= htmlspecialchars($erreur) ?></p>
        <?php endif; ?>
        <form action="../controllers/InscriptionController.php" method="POST">
            <label for="nom_utilisateur">Nom d'utilisateur :</label>
            <input type="text" name="nom_utilisateur" id="nom_utilisateur" required>

            <label for="mot_de_passe">Mot de passe :</label>
            <input type="password" name="mot_de_passe" id="mot_de_passe" required>

            <button type="submit">Créer le Compte</button>
            <a href="../controllers/ConnexionController.php" class="btn btn-back">Retour</a>

        </form>
    </div>
</body>
</html>
