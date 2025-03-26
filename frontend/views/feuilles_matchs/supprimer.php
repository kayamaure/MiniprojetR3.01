<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
} ?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer des Joueurs de la Feuille de Match</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>
    <?php include '../../views/header.php'; ?>
    
    <div class="table-container">
        <h1>Supprimer des Joueurs de la Feuille de Match</h1>
        
        <!-- Message d'erreur -->
        <div id="error-message" style="color: red;"></div>
        <!-- Message de succès -->
        <div id="success-message" style="color: green;"></div>
        
        <a href="#" id="back-link" class="btn btn-back">Retour à la feuille de match</a>
        
        <!-- Informations du match -->
        <div id="match-details">
            <!-- Les détails du match seront insérés ici par JavaScript -->
            <p>Chargement des détails du match...</p>
        </div>
        
        <!-- Formulaire de suppression -->
        <form id="suppression-form">
            <input type="hidden" id="id_match" name="id_match">
            
            <div id="joueurs-container">
                <h2>Joueurs Sélectionnés</h2>
                <p>Chargement des joueurs sélectionnés...</p>
            </div>
            
            <button type="submit" class="btn btn-delete" id="submit-btn" style="display: none;">Supprimer les joueurs sélectionnés</button>
        </form>
    </div>
    
    <?php include '../../views/footer.php'; ?>
    
    <script>
        // Vérifier l'authentification au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            const token = localStorage.getItem('authToken');
            if (!token) {
                // Rediriger vers la page de connexion si pas de token
                window.location.href = '/MiniprojetR3.01/frontend/views/connexion.php';
                return;
            }
            
            // Récupérer l'ID du match depuis l'URL
            const urlParams = new URLSearchParams(window.location.search);
            const idMatch = urlParams.get('id_match');
            
            if (!idMatch) {
                document.getElementById('error-message').textContent = 'ID du match non spécifié.';
                return;
            }
            
            // Configuration du lien de retour
            document.getElementById('back-link').href = `index.php?id_match=${idMatch}`;
            
            // Définir l'ID du match dans le formulaire
            document.getElementById('id_match').value = idMatch;
            
            // Récupérer les détails du match et les joueurs sélectionnés
            fetchMatchAndPlayers(idMatch, token);
            
            // Gestion du formulaire de suppression
            document.getElementById('suppression-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Vérifier si au moins un joueur est sélectionné
                const checkboxes = document.querySelectorAll('input[name="joueurs_supprimer[]"]');
                let selected = false;
                
                checkboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        selected = true;
                    }
                });
                
                if (!selected) {
                    document.getElementById('error-message').textContent = 'Veuillez sélectionner au moins un joueur à supprimer.';
                    return;
                }
                
                // Confirmer la suppression
                if (confirm('Êtes-vous sûr de vouloir supprimer les joueurs sélectionnés de la feuille de match?')) {
                    await deletePlayers(token);
                }
            });
        });
        
        // Fonction pour récupérer les détails du match et les joueurs sélectionnés
        async function fetchMatchAndPlayers(idMatch, token) {
            try {
                const response = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/index.php?action=supprimer&id_match=${idMatch}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });
                
                const data = await response.json();
                
                if (data.error) {
                    document.getElementById('error-message').textContent = data.error;
                    return;
                }
                
                // Afficher les détails du match
                if (data.match) {
                    document.getElementById('match-details').innerHTML = `
                        <h2>Match du ${data.match.date_match} à ${data.match.heure_match}</h2>
                        <p><strong>Contre:</strong> ${data.match.nom_equipe_adverse}</p>
                        <p><strong>Lieu:</strong> ${data.match.lieu_de_rencontre}</p>
                    `;
                }
                
                // Afficher les joueurs sélectionnés
                displaySelectedPlayers(data.joueursSelectionnes || []);
                
            } catch (error) {
                console.error('Erreur:', error);
                document.getElementById('error-message').textContent = 'Erreur lors de la récupération des données du match et des joueurs.';
            }
        }
        
        // Fonction pour afficher les joueurs sélectionnés avec des cases à cocher
        function displaySelectedPlayers(players) {
            const container = document.getElementById('joueurs-container');
            const submitBtn = document.getElementById('submit-btn');
            
            if (!players || players.length === 0) {
                container.innerHTML = '<h2>Joueurs Sélectionnés</h2><p>Aucun joueur sélectionné pour ce match.</p>';
                submitBtn.style.display = 'none';
                return;
            }
            
            submitBtn.style.display = 'block';
            
            let html = '<h2>Joueurs Sélectionnés</h2>';
            html += '<p>Sélectionnez les joueurs à supprimer de la feuille de match:</p>';
            
            // Grouper les joueurs par rôle
            const titulaires = players.filter(j => j.role === 'Titulaire');
            const remplacants = players.filter(j => j.role === 'Remplacant');
            
            // Afficher les titulaires
            html += '<h3>Titulaires</h3>';
            if (titulaires.length === 0) {
                html += '<p>Aucun titulaire sélectionné.</p>';
            } else {
                html += createPlayerTable(titulaires);
            }
            
            // Afficher les remplacants
            html += '<h3>Remplacants</h3>';
            if (remplacants.length === 0) {
                html += '<p>Aucun remplacant sélectionné.</p>';
            } else {
                html += createPlayerTable(remplacants);
            }
            
            container.innerHTML = html;
        }
        
        // Fonction pour créer un tableau de joueurs avec des cases à cocher
        function createPlayerTable(players) {
            let html = `
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
                        <td>
                            <input type="checkbox" name="joueurs_supprimer[]" value="${joueur.id_selection}">
                        </td>
                        <td>${joueur.nom || 'N/A'}</td>
                        <td>${joueur.prenom || 'N/A'}</td>
                        <td>${joueur.role || 'N/A'}</td>
                        <td>${joueur.poste || 'N/A'}</td>
                    </tr>
                `;
            });
            
            html += `
                    </tbody>
                </table>
            `;
            
            return html;
        }
        
        // Fonction pour supprimer les joueurs sélectionnés
        async function deletePlayers(token) {
            try {
                const form = document.getElementById('suppression-form');
                const formData = new FormData(form);
                
                // Récupérer les ID des joueurs à supprimer
                const checkboxes = document.querySelectorAll('input[name="joueurs_supprimer[]"]:checked');
                const ids = Array.from(checkboxes).map(cb => cb.value);
                
                const idMatch = formData.get('id_match');
                
                const response = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/index.php?action=supprimer&id_match=${idMatch}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify({
                        id_match: idMatch,
                        joueurs_supprimer: ids
                    })
                });
                
                const responseData = await response.json();
                
                if (responseData.success) {
                    document.getElementById('success-message').textContent = 'Joueurs supprimés avec succès de la feuille de match.';
                    
                    // Rafraîchir la liste des joueurs après 2 secondes
                    setTimeout(() => {
                        fetchMatchAndPlayers(idMatch, token);
                        document.getElementById('success-message').textContent = '';
                    }, 2000);
                } else {
                    document.getElementById('error-message').textContent = responseData.error || 'Erreur lors de la suppression des joueurs.';
                }
            } catch (error) {
                console.error('Erreur:', error);
                document.getElementById('error-message').textContent = 'Erreur lors de l\'envoi des données.';
            }
        }
    </script>
</body>
</html>