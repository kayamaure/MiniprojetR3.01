<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../views/header.php'; ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Supprimer des Joueurs</title>
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
</head>

<body>
    <?php if (!empty($_SESSION['error'])): ?>
        <script>
            alert("<?= htmlspecialchars($_SESSION['error']); ?>");
        </script>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <script>
            alert("<?= htmlspecialchars($_SESSION['success']); ?>");
        </script>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="table-container">
        <a href="../controllers/FeuilleMatchController.php?action=afficher&id_match=<?= htmlspecialchars($id_match) ?>" class="btn btn-back">Retour</a>

        <h1>Supprimer des Joueurs de la Feuille de Match</h1>
        <form action="FeuilleMatchController.php?action=valider_suppression&id_match=<?= htmlspecialchars($id_match) ?>" method="POST">
            <h2>Joueurs Sélectionnés</h2>
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
                                name="joueur_a_supprimer[<?= $index ?>][numero_licence]"
                                value="<?= htmlspecialchars($joueur['numero_licence']) ?>">
                            <?= htmlspecialchars($joueur['nom_joueur'] . ' ' . $joueur['prenom_joueur']) ?>
                        </label>

                        <div class="player-info">
                            <p><strong>Rôle :</strong> <?= htmlspecialchars($joueur['role'] ?? 'N/A') ?></p>
                            <p><strong>Poste :</strong> <?= htmlspecialchars($joueur['poste'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <button type="submit">Supprimer les Joueurs Sélectionnés</button>
        </form>
    </div>
</body>

</html>