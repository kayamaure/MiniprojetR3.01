<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Match</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php include '../../views/header.php'; ?>

<div class="container">
    <h1>Modifier le Match</h1>

    <div id="error-message" style="color: red;"></div>

    <form id="match-form">
        <input type="hidden" name="id_match" id="id_match">

        <label for="date_match">Date :</label>
        <input type="date" name="date_match" id="date_match" required>

        <label for="heure_match">Heure :</label>
        <input type="time" name="heure_match" id="heure_match" required>

        <label for="lieu_match">Lieu de rencontre :</label>
        <select name="lieu_match" id="lieu_match" required>
            <option value="">-- Sélectionner --</option>
            <option value="Domicile">Domicile</option>
            <option value="Extérieur">Extérieur</option>
        </select>

        <label for="nom_adversaire">Nom de l'équipe adverse :</label>
        <input type="text" name="nom_adversaire" id="nom_adversaire" required>

        <div id="resultat-container" style="display: none;">
    <label for="resultat">Résultat du match :</label>
    <select name="resultat" id="resultat">
        <option value="">-- Sélectionner un résultat --</option>
        <option value="Victoire">Victoire</option>
        <option value="Défaite">Défaite</option>
        <option value="Match Nul">Match Nul</option>
    </select>
</div>

        <button type="submit" class="btn btn-edit">Modifier</button>
        <a href="index.php" class="btn btn-back">Retour</a>
    </form>
</div>

<?php include '../../views/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', async function () {
    const token = localStorage.getItem('authToken');
    const errorDiv = document.getElementById('error-message');

    if (!token) {
        window.location.href = '/MiniprojetR3.01/frontend/views/connexion.php';
        return;
    }

    const urlParams = new URLSearchParams(window.location.search);
    const matchId = urlParams.get('id_match');

    if (!matchId) {
        errorDiv.textContent = 'ID du match non spécifié.';
        return;
    }

    await chargerMatch(matchId, token);

    document.getElementById('match-form').addEventListener('submit', async function (e) {
        e.preventDefault();
        await modifierMatch(matchId, token);
    });
});

// Charger un match dans le formulaire
async function chargerMatch(matchId, token) {
    const errorDiv = document.getElementById('error-message');
    try {
        const res = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/public/index.php?action=matchs&id_match=${matchId}`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });

        const data = await res.json();
        if (data.error) {
            errorDiv.textContent = data.error;
            return;
        }

        // Remplissage du formulaire
        document.getElementById('id_match').value = data.id_match;
        document.getElementById('date_match').value = data.date_match;
        document.getElementById('heure_match').value = data.heure_match;
        document.getElementById('lieu_match').value = data.lieu_de_rencontre;
        document.getElementById('nom_adversaire').value = data.nom_equipe_adverse;

        // Affichage des scores si match passé
        const matchDate = new Date(`${data.date_match}T${data.heure_match}`);
        if (matchDate < new Date()) {
            document.getElementById('resultat-container').style.display = 'block';
        }

    } catch (err) {
        console.error('Erreur chargement match:', err);
        errorDiv.textContent = "Erreur lors de la récupération du match.";
    }
}

// Modifier un match
async function modifierMatch(matchId, token) {
    const errorDiv = document.getElementById('error-message');
    const form = document.getElementById('match-form');
    const formData = new FormData(form);
    const matchData = {};

    for (const [key, value] of formData.entries()) {
        if (key === 'lieu_match') matchData['lieu_de_rencontre'] = value;
        else if (key === 'nom_adversaire') matchData['nom_equipe_adverse'] = value;
        else if (key === 'score_equipe') matchData['score_domicile'] = value;
        else if (key === 'score_adversaire') matchData['score_exterieur'] = value;
        else matchData[key] = value;
    }

    try {
        const response = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/public/index.php?action=matchs&id_match=${matchId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(matchData)
        });

        const data = await response.json();

        if (data.success || data.message) {
            errorDiv.style.color = 'green';
            errorDiv.textContent = '✅ Match modifié avec succès ! Redirection...';
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 1500);
        } else {
            errorDiv.style.color = 'red';
            errorDiv.textContent = data.error || 'Erreur lors de la modification.';
        }

    } catch (err) {
        console.error('Erreur de modification:', err);
        errorDiv.textContent = "Erreur lors de la mise à jour du match.";
    }
}
</script>
</body>
</html>
