<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../views/header.php'; ?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Joueur à la Feuille de Match</title>
    <link rel="stylesheet" href="../views/css/style.css">
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const playerSelect = document.getElementById("numero_licence");
            const playerDetails = document.getElementById("player-details");

            
            const playerData = <?= json_encode($joueursNonSelectionnes) ?>;

            playerSelect.addEventListener("change", () => {
                const selectedPlayer = playerData.find(player => player.numero_licence === playerSelect.value);

                if (selectedPlayer) {
                    playerDetails.innerHTML = `
                    <p><strong>Taille :</strong> ${selectedPlayer.taille ?? 'N/A'} cm</p>
                    <p><strong>Poids :</strong> ${selectedPlayer.poids ?? 'N/A'} kg</p>
                    <p><strong>Dernier Commentaire :</strong> ${selectedPlayer.commentaire ?? 'Pas de commentaire'}</p>
                    <p><strong>Moyenne des Évaluations :</strong> ${selectedPlayer.moyenne_evaluation ? selectedPlayer.moyenne_evaluation.toFixed(2) : 'Aucune évaluation'}</p>
                `;
                } else {
                    playerDetails.innerHTML = "<p>Sélectionnez un joueur pour voir les détails.</p>";
                }
            });
        });
    </script>

</head>

<body>
    <div class="container">
        <a href="../controllers/FeuilleMatchController.php?action=afficher&id_match=<?= htmlspecialchars($id_match) ?>" class="btn btn-back">Retour</a>

        <h1>Ajouter un Joueur à la Feuille de Match</h1>

        <!-- Afficher un message d'erreur s'il y en a -->
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="error-message">
                <?= htmlspecialchars($_SESSION['error']); ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form action="FeuilleMatchController.php?action=ajouter&id_match=<?= htmlspecialchars($id_match) ?>" method="POST">
            <input type="hidden" name="id_match" value="<?= htmlspecialchars($id_match) ?>">

            <!-- Sélection du joueur -->
            <div>
                <label for="numero_licence">Joueur :</label>
                <select name="numero_licence" id="numero_licence" required>
                    <option value="">-- Sélectionner un joueur --</option>
                    <?php foreach ($joueursNonSelectionnes as $joueur): ?>
                        <option value="<?= htmlspecialchars($joueur['numero_licence']) ?>">
                            <?= htmlspecialchars($joueur['nom']) . " " . htmlspecialchars($joueur['prenom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Afficher les détails du joueur -->
            <div id="player-details" style="margin-top: 15px;">
                <p>Sélectionnez un joueur pour voir les détails.</p>
            </div>

            <!-- Rôle du joueur -->
            <div>
                <label for="role">Rôle :</label>
                <select name="role" id="role" required>
                    <option value="">-- Sélectionner un rôle --</option>
                    <option value="Titulaire">Titulaire</option>
                    <option value="Remplaçant">Remplaçant</option>
                </select>
            </div>

            <!-- Poste du joueur -->
            <div>
                <label for="poste">Poste :</label>
                <select name="poste" id="poste" required>
                    <option value="">-- Poste --</option>
                    <option value="Gardien de But">Gardien de But</option>
                    <option value="Défenseur Central">Défenseur Central</option>
                    <option value="Défenseur Latéral">Défenseur Latéral</option>
                    <option value="Arrière Latéral Offensif">Arrière Latéral Offensif</option>
                    <option value="Libéro">Libéro</option>
                    <option value="Milieu Défensif">Milieu Défensif</option>
                    <option value="Milieu Central">Milieu Central</option>
                    <option value="Milieu Offensif">Milieu Offensif</option>
                    <option value="Milieu Latéral">Milieu Latéral</option>
                    <option value="Attaquant Central">Attaquant Central</option>
                    <option value="Avant-Centre">Avant-Centre</option>
                    <option value="Ailier">Ailier</option>
                    <option value="Second Attaquant">Second Attaquant</option>
                </select>
            </div>

            <!-- Bouton de soumission -->
            <button type="submit">Ajouter à la Feuille de Match</button>
        </form>
    </div>
</body>

</html>