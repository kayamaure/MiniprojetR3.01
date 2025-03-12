

<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../views/header.php'; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Compte</title>
    <link rel="stylesheet" href="../views/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Mon Compte</h1>

        <?php if (!empty($user)): ?>
            <p><strong>Nom d’utilisateur :</strong> <?= htmlspecialchars($user['nom_utilisateur'], ENT_QUOTES, 'UTF-8') ?></p>

            <!-- Formulaire de changement de mot de passe -->
            <h2>Changer de mot de passe</h2>
            <?php if (!empty($error)): ?>
                <p class="error-message"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <p class="success-message"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form action="../controllers/MonCompteController.php?action=updatePassword" method="post" onsubmit="return confirmPasswordChange();">
                <div class="form-group">
                    <label for="current_password">Mot de passe actuel :</label>
                    <input type="password" name="current_password" id="current_password" required>
                </div>

                <div class="form-group">
                    <label for="new_password">Nouveau mot de passe :</label>
                    <input type="password" name="new_password" id="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe :</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
            </form>
        <?php else: ?>
            <p>Aucune information disponible pour l’utilisateur.</p>
        <?php endif; ?>

        <p>
            <a href="../views/dashboard.php" class="btn btn-back">Retour au dashboard</a>

        </p>
    </div>
    <script>
    function confirmPasswordChange() {
        return confirm("Êtes-vous sûr de vouloir changer votre mot de passe ?");
    }
    </script>
</body>
</html>
