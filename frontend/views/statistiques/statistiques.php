<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../views/header.php'; ?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques</title>
    <link rel="stylesheet" href="../views/css/style.css">
    <style>
        .btn-stat {
            background-color: #12c2f3;
        }

        .btn-stat:hover {
            background-color: #22a1e6; /* Orange plus foncé */
            transform: scale(1.05);
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Statistiques</h1>
        <p>Choisissez le type de statistiques que vous souhaitez visualiser :</p>

        <div class="stat-buttons">
        <a href="../controllers/StatistiquesController.php?action=matchs" class="btn btn-stat">Statistiques des Matchs</a>
        <a href="../controllers/StatistiquesController.php?action=joueurs" class="btn btn-stat">Statistiques des Joueurs</a>
        <a href="../views/dashboard.php" class="btn btn-back">Retour</a>
        </div>
    </div>
</body>

</html>