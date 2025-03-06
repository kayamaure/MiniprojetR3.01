<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../views/header.php'; ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Modifier la Sélection des Joueurs</title>
    <link rel="stylesheet" href="../views/css/style.css">
    <style>
        .player-selection {
            margin-bottom: 15px;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
        }

        .player-info {
            margin-bottom: 10px;
        }
    </style>
    <script>
        function toggleFields(checkbox, index) {
            const roleField = document.getElementById(`role_${index}`);
            const posteField = document.getElementById(`poste_${index}`);
            const isEnabled = checkbox.checked;

            // Enable/disable les champs
            roleField.disabled = !isEnabled;
            posteField.disabled = !isEnabled;

            if (!isEnabled) {
                roleField.removeAttribute('required');
                posteField.removeAttribute('required');
            } else {
                roleField.setAttribute('required', true);
                posteField.setAttribute('required', true);
            }
        }
    </script>
</head>

<body>
    <!-- Messages d'erreur -->
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="error-message">
            <?= htmlspecialchars($_SESSION['error']); ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="success-message">
            <?= htmlspecialchars($_SESSION['success']); ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>


    <div class="container">
    <a href="../controllers/FeuilleMatchController.php?action=afficher&id_match=<?= htmlspecialchars($id_match) ?>" class="btn btn-back">Retour</a>

        <h1>Modifier la Sélection des Joueurs</h1>
        <form action="FeuilleMatchController.php?action=valider_modification&id_match=<?= htmlspecialchars($id_match) ?>" method="POST">
            <h2>Joueurs disponibles</h2>
            <input type="hidden" name="id_match" value="<?= htmlspecialchars($id_match) ?>">

            <?php if (empty($titulaires) && empty($remplacants)): ?>
                <p class="no-players">Aucun joueur trouvé pour ce match.</p>
            <?php else: ?>
                <?php
                $joueurs = array_merge($titulaires, $remplacants);
                foreach ($joueurs as $index => $joueur):
                ?>
                    <div class="player-selection">
                        <label>
                            <input
                                type="checkbox"
                                name="joueur_selectionnes[<?= $index ?>][numero_licence]"
                                value="<?= htmlspecialchars($joueur['numero_licence']) ?>"
                                onclick="toggleFields(this, <?= $index ?>)">
                            <?= htmlspecialchars($joueur['nom_joueur'] . ' ' . $joueur['prenom_joueur']) ?>
                        </label>

                        <div class="player-info">
                            <p><strong>Poste actuel :</strong> <?= htmlspecialchars($joueur['poste'] ?? 'N/A') ?></p>
                        </div>

                        <select id="role_<?= $index ?>" name="joueur_selectionnes[<?= $index ?>][role]" disabled>
                            <option value="">-- Rôle --</option>
                            <option value="Titulaire" <?= $joueur['role'] === 'Titulaire' ? 'selected' : '' ?>>Titulaire</option>
                            <option value="Remplaçant" <?= $joueur['role'] === 'Remplaçant' ? 'selected' : '' ?>>Remplaçant</option>
                        </select>
                        <select id="poste_<?= $index ?>" name="joueur_selectionnes[<?= $index ?>][poste]" disabled>
                            <option value="">-- Poste --</option>
                            <option value="Gardien de But" <?= $joueur['poste'] === 'Gardien de But' ? 'selected' : '' ?>>Gardien de But</option>
                            <option value="Défenseur Central" <?= $joueur['poste'] === 'Défenseur Central' ? 'selected' : '' ?>>Défenseur Central</option>
                            <option value="Défenseur Latéral" <?= $joueur['poste'] === 'Défenseur Latéral' ? 'selected' : '' ?>>Défenseur Latéral</option>
                            <option value="Arrière Latéral Offensif" <?= $joueur['poste'] === 'Arrière Latéral Offensif' ? 'selected' : '' ?>>Arrière Latéral Offensif</option>
                            <option value="Libéro" <?= $joueur['poste'] === 'Libéro' ? 'selected' : '' ?>>Libéro</option>
                            <option value="Milieu Défensif" <?= $joueur['poste'] === 'Milieu Défensif' ? 'selected' : '' ?>>Milieu Défensif</option>
                            <option value="Milieu Central" <?= $joueur['poste'] === 'Milieu Central' ? 'selected' : '' ?>>Milieu Central</option>
                            <option value="Milieu Offensif" <?= $joueur['poste'] === 'Milieu Offensif' ? 'selected' : '' ?>>Milieu Offensif</option>
                            <option value="Milieu Latéral" <?= $joueur['poste'] === 'Milieu Latéral' ? 'selected' : '' ?>>Milieu Latéral</option>
                            <option value="Attaquant Central" <?= $joueur['poste'] === 'Attaquant Central' ? 'selected' : '' ?>>Attaquant Central</option>
                            <option value="Avant-Centre" <?= $joueur['poste'] === 'Avant-Centre' ? 'selected' : '' ?>>Avant-Centre</option>
                            <option value="Ailier" <?= $joueur['poste'] === 'Ailier' ? 'selected' : '' ?>>Ailier</option>
                            <option value="Second Attaquant" <?= $joueur['poste'] === 'Second Attaquant' ? 'selected' : '' ?>>Second Attaquant</option>
                        </select>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <button type="submit">Valider les Modifications</button>
        </form>
    </div>
</body>

</html>