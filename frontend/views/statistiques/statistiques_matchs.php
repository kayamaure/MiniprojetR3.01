<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../views/header.php'; ?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques des Matchs</title>
    <link rel="stylesheet" href="../views/css/style.css">
</head>

<body>
    <div class="container">
        <h1>Statistiques des Matchs</h1>

        <h2>Nombre Total de Matchs</h2>
        <ul>
            <li><strong>Matchs Gagnés :</strong> <?= htmlspecialchars($totaux['matchs_gagnes']) ?></li>
            <li><strong>Matchs Nuls :</strong> <?= htmlspecialchars($totaux['matchs_nuls']) ?></li>
            <li><strong>Matchs Perdus :</strong> <?= htmlspecialchars($totaux['matchs_perdus']) ?></li>
        </ul>

        <h2>Pourcentages des Matchs</h2>
        <ul>
            <li><strong>Pourcentage Gagnés :</strong> <?= number_format($pourcentages['pourcentage_gagnes'], 2) ?>%</li>
            <li><strong>Pourcentage Nuls :</strong> <?= number_format($pourcentages['pourcentage_nuls'], 2) ?>%</li>
            <li><strong>Pourcentage Perdus :</strong> <?= number_format($pourcentages['pourcentage_perdus'], 2) ?>%</li>
        </ul>

        <!-- Bouton de retour -->
        <div class="return-button">
            <a href="../controllers/StatistiquesController.php?action=index" class="btn btn-back">Retour</a>
        </div>
    </div>
</body>

</html>