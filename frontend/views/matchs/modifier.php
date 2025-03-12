<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
} include '../views/header.php'; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un Match</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h1>Modifier le Match</h1>
    <form action="MatchsController.php?action=modifier&id_match=<?= $match['id_match'] ?>" method="POST">
        <!-- Date du match -->
        <label for="date_match">Date :</label>
        <input 
            type="date" 
            name="date_match" 
            id="date_match" 
            value="<?= htmlspecialchars($match['date_match']) ?>" 
            <?= $isMatchInThePast ? 'disabled' : '' ?> 
            required>
        <?php if ($isMatchInThePast): ?>
            <input type="hidden" name="date_match" value="<?= htmlspecialchars($match['date_match']) ?>">
        <?php endif; ?>

        <!-- Heure du match -->
        <label for="heure_match">Heure :</label>
        <input 
            type="time" 
            name="heure_match" 
            id="heure_match" 
            value="<?= htmlspecialchars($match['heure_match']) ?>" 
            <?= $isMatchInThePast ? 'disabled' : '' ?> 
            required>
        <?php if ($isMatchInThePast): ?>
            <input type="hidden" name="heure_match" value="<?= htmlspecialchars($match['heure_match']) ?>">
        <?php endif; ?>

        <!-- Lieu de rencontre -->
        <label for="lieu_de_rencontre">Lieu de rencontre :</label>
        <select 
            name="lieu_de_rencontre" 
            id="lieu_de_rencontre" 
            <?= $isMatchInThePast ? 'disabled' : '' ?>>
            <option value="">-- Sélectionner --</option>
            <option value="Domicile" <?= isset($match['lieu_de_rencontre']) && $match['lieu_de_rencontre'] === 'Domicile' ? 'selected' : '' ?>>Domicile</option>
            <option value="Extérieur" <?= isset($match['lieu_de_rencontre']) && $match['lieu_de_rencontre'] === 'Extérieur' ? 'selected' : '' ?>>Extérieur</option>
        </select>
        <?php if ($isMatchInThePast): ?>
            <input type="hidden" name="lieu_de_rencontre" value="<?= htmlspecialchars($match['lieu_de_rencontre']) ?>">
        <?php endif; ?>

        <!-- Résultat : Modifiable uniquement si le match est dans le passé -->
        <?php if ($isMatchInThePast): ?>
        <label for="resultat">Résultat :</label>
        <select name="resultat" id="resultat">
            <option value="">-- Sélectionner --</option>
            <option value="Victoire" <?= isset($match['resultat']) && $match['resultat'] === 'Victoire' ? 'selected' : '' ?>>Victoire</option>
            <option value="Match nul" <?= isset($match['resultat']) && $match['resultat'] === 'Match nul' ? 'selected' : '' ?>>Match nul</option>
            <option value="Défaite" <?= isset($match['resultat']) && $match['resultat'] === 'Défaite' ? 'selected' : '' ?>>Défaite</option>
        </select>
        <?php endif; ?>

        <button type="submit">Modifier</button>
        <a href="../controllers/MatchsController.php?action=liste" class="btn btn-back">Retour</a>
    </form>
</div>
</body>
</html>
<?php include '../views/footer.php'; ?>
