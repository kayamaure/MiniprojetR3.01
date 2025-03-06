<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
} ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Joueurs</title>
    <link rel="stylesheet" href="../views/css/style.css">
</head>

<body>
    <div class="table-container">
        <h1>Liste des Joueurs</h1>


        <a href="../controllers/JoueursController.php?action=ajouter" class="btn btn-add">Ajouter un Joueur</a>
        <a href="../controllers/DashboardController.php" class="btn btn-back">Retour</a>
        <?php if (!empty($joueurs)) : ?>
            <table id="joueurs-table">
                <thead>
                    <tr>
                        <th>Numéro Licence</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Date de Naissance</th>
                        <th>Taille</th>
                        <th>Poids</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($joueurs as $joueur) : ?>
                        <tr data-id="<?= htmlspecialchars($joueur['numero_licence']); ?>">
                            <td><?= htmlspecialchars($joueur['numero_licence']); ?></td>
                            <td><?= htmlspecialchars($joueur['nom']); ?></td>
                            <td><?= htmlspecialchars($joueur['prenom']); ?></td>
                            <td><?= htmlspecialchars($joueur['date_naissance']); ?></td>
                            <td><?= htmlspecialchars($joueur['taille']); ?> m</td>
                            <td><?= htmlspecialchars($joueur['poids']); ?> kg</td>
                            <td><?= htmlspecialchars($joueur['statut']); ?></td>
                            <td>
                                <!-- Modifier and Supprimer buttons -->
                                <a href="../controllers/JoueursController.php?action=modifier&numero_licence=<?= htmlspecialchars($joueur['numero_licence']); ?>" class="btn btn-edit">Modifier</a>
                                <a href="../controllers/JoueursController.php?action=supprimer&numero_licence=<?= htmlspecialchars($joueur['numero_licence']); ?>" class="btn btn-delete" onclick="return confirm('Voulez-vous vraiment supprimer ce joueur ?');">Supprimer</a>
                                <a href="../controllers/CommentaireController.php?action=ajouter_commentaire&numero_licence=<?= htmlspecialchars($joueur['numero_licence']); ?>" class="btn btn-add">Ajouter Commentaire</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p class="no-data">Aucun joueur trouvé.</p>
        <?php endif; ?>
    </div>

    <?php include '../views/footer.php'; ?>