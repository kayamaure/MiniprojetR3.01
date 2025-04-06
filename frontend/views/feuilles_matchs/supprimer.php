<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
} ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer un Joueur de la Feuille de Match</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>
<?php include '../../views/header.php'; ?>

<div class="table-container">
    <h1>Supprimer un Joueur de la Feuille de Match</h1>

    <div id="error-message" style="color: red;"></div>
    <div id="success-message" style="color: green;"></div>

    <a href="#" id="back-link" class="btn btn-back">Retour à la feuille de match</a>

    <div id="match-details">
        <p>Chargement des détails du match...</p>
    </div>

    <form id="suppression-form">
        <input type="hidden" id="id_match" name="id_match">
        <div id="joueurs-container">
            <p>Chargement des joueurs...</p>
        </div>
        <button type="submit" class="btn btn-delete" id="submit-btn" style="display: none;">Supprimer ce joueur</button>
    </form>
</div>

<?php include '../../views/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const token = localStorage.getItem('authToken');
    if (!token) return window.location.href = '/MiniprojetR3.01/frontend/views/connexion.php';

    const urlParams = new URLSearchParams(window.location.search);
    const idMatch = urlParams.get('id_match');
    if (!idMatch) {
        document.getElementById('error-message').textContent = 'ID du match non spécifié.';
        return;
    }

    document.getElementById('back-link').href = `index.php?id_match=${idMatch}`;
    document.getElementById('id_match').value = idMatch;

    fetchMatchAndPlayers(idMatch, token);

    document.getElementById('suppression-form').addEventListener('submit', async function (e) {
        e.preventDefault();
        const selectedRadio = document.querySelector('input[name="joueur_supprimer"]:checked');
        if (!selectedRadio) {
            document.getElementById('error-message').textContent = "Veuillez sélectionner un joueur.";
            return;
        }

        if (confirm("Confirmer la suppression de ce joueur ?")) {
            await deletePlayer(idMatch, selectedRadio.value, token);
        }
    });
});

async function fetchMatchAndPlayers(idMatch, token) {
    try {
        const res = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/public/index.php?action=feuille_match&sub_action=supprimer&id_match=${idMatch}`, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });
        const data = await res.json();

        if (data.error) {
            document.getElementById('error-message').textContent = data.error;
            return;
        }

        if (data.match) {
            document.getElementById('match-details').innerHTML = `
                <h2>Match du ${data.match.date_match} à ${data.match.heure_match}</h2>
                <p><strong>Contre:</strong> ${data.match.nom_equipe_adverse}</p>
                <p><strong>Lieu:</strong> ${data.match.lieu_de_rencontre}</p>
            `;
        }

        displaySelectedPlayers(data.joueursSelectionnes || []);
    } catch (err) {
        console.error(err);
        document.getElementById('error-message').textContent = 'Erreur lors du chargement.';
    }
}

function displaySelectedPlayers(players) {
    const container = document.getElementById('joueurs-container');
    const submitBtn = document.getElementById('submit-btn');

    if (!players.length) {
        container.innerHTML = '<p>Aucun joueur sélectionné pour ce match.</p>';
        submitBtn.style.display = 'none';
        return;
    }

    submitBtn.style.display = 'block';

    let html = `
        <p>Sélectionnez un joueur à supprimer :</p>
        <table>
            <thead>
                <tr>
                    <th>Sélectionner</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Rôle</th>
                    <th>Poste</th>
                </tr>
            </thead>
            <tbody>
    `;

    players.forEach(joueur => {
        html += `
            <tr>
                <td><input type="radio" name="joueur_supprimer" value="${joueur.id_selection}"></td>
                <td>${joueur.nom}</td>
                <td>${joueur.prenom}</td>
                <td>${joueur.role}</td>
                <td>${joueur.poste}</td>
            </tr>
        `;
    });

    html += `</tbody></table>`;
    container.innerHTML = html;
}

async function deletePlayer(idMatch, idSelection, token) {
    try {
        const response = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/public/index.php?action=feuille_match&sub_action=supprimer&id_match=${idMatch}&id_selection=${idSelection}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        const result = await response.json();

        if (result.success) {
            document.getElementById('success-message').textContent = result.success;
            setTimeout(() => location.reload(), 1500);
        } else {
            document.getElementById('error-message').textContent = result.error || 'Erreur lors de la suppression.';
        }
    } catch (err) {
        console.error(err);
        document.getElementById('error-message').textContent = 'Erreur lors de la requête.';
    }
}
</script>
</body>
</html>
