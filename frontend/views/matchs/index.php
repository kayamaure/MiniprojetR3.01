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
        
        <div class="action-buttons">
            <a href="ajouter.php" class="btn-add-match">Ajouter un Match</a>
            <a href="../dashboard.php" class="btn btn-back">Retour</a>
        </div>

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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="matchs-tbody">
                    <!-- Le contenu sera généré dynamiquement par JavaScript -->
                </tbody>
            </table>
        </div>
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
                const response = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/public/index.php?action=matchs&filter=${filter}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });
                
                const data = await response.json();
                // console.log('Données reçues de l\'API:', data); // Pour debugging
                
                // Récupérer les éléments du DOM
                const tableBody = document.getElementById('matchs-tbody');
                const table = document.getElementById('matchs-table');
                const noMatchMessage = document.getElementById('no-match-message');
                
                // Vider le tableau existant
                tableBody.innerHTML = '';
                
                // Vérifier si nous avons des données
                if (data && data.length > 0) {
                    // Afficher le tableau, masquer le message "aucun match"
                    table.style.display = 'table';
                    noMatchMessage.style.display = 'none';
                    
                    // Remplir le tableau avec les données
                    data.forEach(match => {
                        const row = document.createElement('tr');
                        
                        // Déterminer le résultat à afficher
                        let resultatDisplay = 'Non disponible';
                        if (match.statut === 'Terminé' && match.resultat) {
                            resultatDisplay = match.resultat;
                        }
                        
                        row.innerHTML = `
                            <td>${match.id_match}</td>
                            <td>${match.date_match}</td>
                            <td>${match.heure_match}</td>
                            <td>${match.lieu_de_rencontre}</td>
                            <td>${match.nom_equipe_adverse}</td>
                            <td>${match.statut}</td>
                            <td>${resultatDisplay}</td>
                            <td class="action-buttons">
                                <a href="../feuilles_matchs/index.php?id_match=${match.id_match}" class="btn btn-add">Feuille du match</a>
                                <a href="modifier.php?id_match=${match.id_match}" class="btn btn-edit">Modifier</a>
                                <button class="btn btn-delete" onclick="deleteMatch(${match.id_match})">Supprimer</button>
                            </td>
                        `;
                        
                        tableBody.appendChild(row);
                    });
                } else {
                    // Masquer le tableau, afficher le message "aucun match"
                    table.style.display = 'none';
                    noMatchMessage.style.display = 'block';
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
                const response = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/public/index.php?action=matchs&id_match=${idMatch}`, {
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
