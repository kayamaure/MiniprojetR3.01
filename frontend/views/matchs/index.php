<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Matchs</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<?php include '../../views/header.php'; ?>

<div class="table-container">
    <h1>Liste des Matchs</h1>

    <!-- Message d'erreur -->
    <div id="error-message" style="color: red;"></div>

    <!-- Groupe de filtres -->
    <div class="btn-group">
        <a href="index.php?filter=a_venir" class="btn-add-match">Matchs à venir</a>
        <a href="index.php?filter=passes" class="btn-add-match">Matchs passés</a>
    </div>

    <!-- Actions -->
    <div class="action-buttons">
        <a href="ajouter.php" class="btn-add-match">Ajouter un Match</a>
        <a href="../dashboard.php" class="btn btn-back">Retour</a>
    </div>

    <!-- Liste des matchs -->
    <div id="matchs-container">
        <p id="no-match-message" class="no-data">Aucun match trouvé.</p>
        <table id="matchs-table" style="display: none;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Lieu</th>
                    <th>Équipe adverse</th>
                    <th>Statut</th>
                    <th>Résultat</th>
                    <th>État Feuille Match</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="matchs-tbody">
                <!-- Matchs injectés ici -->
            </tbody>
        </table>
    </div>
</div>

<?php include '../../views/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const token = localStorage.getItem('authToken');
    if (!token) {
        window.location.href = '/MiniprojetR3.01/frontend/views/connexion.php';
        return;
    }

    await fetchMatchs(token);
});

// Fonction pour charger les matchs
async function fetchMatchs(token) {
    const errorDiv = document.getElementById('error-message');
    const tableBody = document.getElementById('matchs-tbody');
    const table = document.getElementById('matchs-table');
    const noMatchMessage = document.getElementById('no-match-message');

    errorDiv.textContent = '';
    tableBody.innerHTML = '';

    const urlParams = new URLSearchParams(window.location.search);
    const filter = urlParams.get('filter') || 'all';

    try {
        const res = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/public/index.php?action=matchs&filter=${filter}`, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        const data = await res.json();

        if (Array.isArray(data) && data.length > 0) {
            table.style.display = 'table';
            noMatchMessage.style.display = 'none';

            data.forEach(match => {
                const row = document.createElement('tr');

                const resultat = match.statut === 'Terminé' && match.resultat
                    ? match.resultat
                    : 'Non disponible';

                row.innerHTML = `
                    <td>${match.id_match}</td>
                    <td>${match.date_match}</td>
                    <td>${match.heure_match}</td>
                    <td>${match.lieu_de_rencontre}</td>
                    <td>${match.nom_equipe_adverse}</td>
                    <td>${match.statut}</td>
                    <td>${resultat}</td>
                    <td>${match.etat_feuille}</td>
                    <td class="action-buttons">
                        <a href="../feuilles_matchs/index.php?id_match=${match.id_match}" class="btn btn-add">Feuille du match</a>
                        <a href="modifier.php?id_match=${match.id_match}" class="btn btn-edit">Modifier</a>
                        <button class="btn btn-delete" onclick="deleteMatch(${match.id_match}, '${token}')">Supprimer</button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        } else {
            table.style.display = 'none';
            noMatchMessage.style.display = 'block';
        }

    } catch (error) {
        console.error('Erreur fetch matchs:', error);
        errorDiv.textContent = "Erreur lors de la récupération des matchs.";
    }
}

// Fonction pour supprimer un match
async function deleteMatch(idMatch, token) {
    const errorDiv = document.getElementById('error-message');

    if (!confirm("Voulez-vous vraiment supprimer ce match ?")) return;

    try {
        const res = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/public/index.php?action=matchs&id_match=${idMatch}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        const data = await res.json();

        if (data.success || data.message) {
            errorDiv.style.color = 'green';
            errorDiv.textContent = "✅ Match supprimé avec succès !";
            await fetchMatchs(token); 
        } else {
            errorDiv.style.color = 'red';
            errorDiv.textContent = data.error || "Erreur lors de la suppression du match.";
        }
    } catch (error) {
        console.error("Erreur suppression match:", error);
        errorDiv.textContent = "Erreur lors de la suppression du match.";
    }
}
</script>

</body>
</html>
