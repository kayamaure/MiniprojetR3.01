<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Matchs</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php 
    // Vérification de l'authentification JWT
    include '../../views/header.php';
    ?>
    
    <div class="table-container">
        <h1>Liste des Matchs</h1>
        
        <!-- Message d'erreur -->
        <div id="error-message" style="color: red;"></div>
        
        <div class="btn-group">
            <a href="index.php?filter=a_venir" class="btn-add-match">Matchs à venir</a>
            <a href="index.php?filter=passes" class="btn-add-match">Matchs passés</a>
        </div>
        
        <a href="ajouter.php" class="btn-add-match">Ajouter un Match</a>
        <a href="../dashboard.php" class="btn btn-back">Retour</a>

        <?php if (!empty($matchs)) : ?>
            <table id="matchs-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Lieu</th>
                        <th>Équipe adverse</th>
                        <th>Statut</th>
                        <th>État Feuille</th>
                        <th>Résultat</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="matchs-tbody">
                    <?php foreach ($matchs as $match) : ?>
                        <tr>
                            <td><?= htmlspecialchars($match['id_match']); ?></td>
                            <td><?= htmlspecialchars($match['date_match']); ?></td>
                            <td><?= htmlspecialchars($match['heure_match']); ?></td>
                            <td><?= htmlspecialchars($match['lieu_de_rencontre']); ?></td>
                            <td><?= htmlspecialchars($match['nom_equipe_adverse']); ?></td>
                            <td><?= htmlspecialchars($match['statut']); ?></td>
                            <td><?= htmlspecialchars($match['etat_feuille']); ?></td>
                            <td><?= $match['statut'] === 'Terminé' ? htmlspecialchars($match['resultat']) : 'N/A'; ?></td>
                            <td class="action-buttons">
                                <a href="../controllers/FeuilleMatchController.php?action=afficher&id_match=<?= $match['id_match']; ?>" class="btn btn-add">Feuille du match</a>
                                <a href="../controllers/MatchsController.php?action=modifier&id_match=<?= $match['id_match']; ?>" class="btn btn-edit">Modifier</a>
                                <a href="../controllers/MatchsController.php?action=supprimer&id_match=<?= $match['id_match']; ?>" class="btn btn-delete">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p class="no-data">Aucun match trouvé.</p>
        <?php endif; ?>
    </div>

    <?php include '../../views/footer.php'; ?>
    
    <script>
        // Vérifier l'authentification au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            const token = localStorage.getItem('authToken');
            if (!token) {
                // Rediriger vers la page de connexion si pas de token
                window.location.href = '/MiniprojetR3.01/frontend/views/connexion.php';
            } else {
                // Afficher la liste des matchs
                fetchMatchs();
            }
        });
        
        // Fonction pour récupérer les matchs via l'API
        async function fetchMatchs() {
            try {
                // Récupérer le filtre depuis l'URL (matchs à venir, passés ou tous)
                const urlParams = new URLSearchParams(window.location.search);
                const filter = urlParams.get('filter') || 'all';
                
                const token = localStorage.getItem('authToken');
                const response = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/index.php?action=matchs&filter=${filter}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });
                
                const data = await response.json();
                
                if (data.error) {
                    document.getElementById('error-message').textContent = data.error;
                    return;
                }
                
                const tableBody = document.getElementById('matchs-tbody');
                
                // Vider le tableau existant
                tableBody.innerHTML = '';
                
                // Remplir le tableau avec les données
                if (data.length > 0) {
                    data.forEach(match => {
                        const row = document.createElement('tr');
                        row.dataset.id = match.id_match;
                        
                        // Formatter le score
                        let scoreDisplay = 'Non joué';
                        if (match.score_equipe !== null && match.score_adversaire !== null) {
                            scoreDisplay = `${match.score_equipe} - ${match.score_adversaire}`;
                        }
                        
                        row.innerHTML = `
                            <td>${match.id_match}</td>
                            <td>${match.date_match}</td>
                            <td>${match.heure_match}</td>
                            <td>${match.lieu_match}</td>
                            <td>${match.nom_adversaire}</td>
                            <td>${scoreDisplay}</td>
                            <td>
                                <a href="modifier.php?id_match=${match.id_match}" class="btn btn-edit">Modifier</a>
                                <a href="#" class="btn btn-delete" onclick="deleteMatch('${match.id_match}'); return false;">Supprimer</a>
                                <a href="../statistiques/saisir.php?id_match=${match.id_match}" class="btn btn-stats">Saisir Stats</a>
                            </td>
                        `;
                        
                        tableBody.appendChild(row);
                    });
                } else {
                    const row = document.createElement('tr');
                    row.innerHTML = `<td colspan="7" class="no-data">Aucun match trouvé.</td>`;
                    tableBody.appendChild(row);
                }
            } catch (error) {
                console.error('Error fetching matchs:', error);
                document.getElementById('error-message').textContent = 'Erreur lors de la récupération des matchs.';
            }
        }
        
        // Fonction pour supprimer un match
        async function deleteMatch(idMatch) {
            if (!confirm('Voulez-vous vraiment supprimer ce match ?')) {
                return;
            }
            
            try {
                const token = localStorage.getItem('authToken');
                const response = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/index.php?action=supprimer_match&id_match=${idMatch}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Rafraîchir la liste des matchs
                    fetchMatchs();
                } else {
                    document.getElementById('error-message').textContent = data.error || 'Erreur lors de la suppression du match.';
                }
            } catch (error) {
                console.error('Error deleting match:', error);
                document.getElementById('error-message').textContent = 'Erreur lors de la suppression du match.';
            }
        }
    </script>
</body>
</html>
