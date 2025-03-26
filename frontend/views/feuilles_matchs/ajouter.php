<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
} ?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Joueur à la Feuille de Match</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>
    <?php include '../../views/header.php'; ?>
    
    <div class="table-container">
        <h1>Ajouter un Joueur à la Feuille de Match</h1>
        
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
        
        <!-- Formulaire d'ajout de joueur -->
        <form id="ajout-joueur-form">
            <input type="hidden" id="id_match" name="id_match">
            
            <div>
                <label for="numero_licence">Joueur:</label>
                <select id="numero_licence" name="numero_licence" required>
                    <option value="">Sélectionnez un joueur</option>
                    <!-- Les joueurs disponibles seront insérés ici par JavaScript -->
                </select>
            </div>
            
            <div id="player-details">
                <p>Sélectionnez un joueur pour voir les détails.</p>
            </div>
            
            <div>
                <label for="role">Rôle:</label>
                <select id="role" name="role" required>
                    <option value="">Sélectionnez un rôle</option>
                    <option value="Titulaire">Titulaire</option>
                    <option value="Remplaçant">Remplaçant</option>
                </select>
            </div>
            
            <div>
                <label for="poste">Poste:</label>
                <select id="poste" name="poste" required>
                    <option value="">Sélectionnez un poste</option>
                    <option value="Gardien">Gardien</option>
                    <option value="Défenseur">Défenseur</option>
                    <option value="Milieu">Milieu</option>
                    <option value="Attaquant">Attaquant</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-add">Ajouter</button>
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
            
            // Récupérer les détails du match et les joueurs disponibles
            fetchMatchAndPlayers(idMatch, token);
            
            // Gestion du formulaire d'ajout de joueur
            document.getElementById('ajout-joueur-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                await addPlayerToMatch(token);
            });
            
            // Gestion de l'affichage des détails du joueur sélectionné
            document.getElementById('numero_licence').addEventListener('change', function() {
                displayPlayerDetails(this.value);
            });
        });
        
        // Variable globale pour stocker les données des joueurs
        let playerData = [];
        
        // Fonction pour récupérer les détails du match et les joueurs disponibles
        async function fetchMatchAndPlayers(idMatch, token) {
            try {
                const response = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/index.php?action=ajouter&id_match=${idMatch}`, {
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
                
                // Stocker les données des joueurs dans la variable globale
                playerData = data.joueursNonSelectionnes || [];
                
                // Remplir la liste déroulante des joueurs
                const playerSelect = document.getElementById('numero_licence');
                playerSelect.innerHTML = '<option value="">Sélectionnez un joueur</option>';
                
                if (playerData.length === 0) {
                    document.getElementById('error-message').textContent = 'Aucun joueur disponible pour ce match.';
                } else {
                    playerData.forEach(player => {
                        const option = document.createElement('option');
                        option.value = player.numero_licence;
                        option.textContent = `${player.nom} ${player.prenom}`;
                        playerSelect.appendChild(option);
                    });
                }
                
            } catch (error) {
                console.error('Erreur:', error);
                document.getElementById('error-message').textContent = 'Erreur lors de la récupération des données du match et des joueurs.';
            }
        }
        
        // Fonction pour afficher les détails du joueur sélectionné
        function displayPlayerDetails(numero_licence) {
            const playerDetails = document.getElementById('player-details');
            
            if (!numero_licence) {
                playerDetails.innerHTML = '<p>Sélectionnez un joueur pour voir les détails.</p>';
                return;
            }
            
            const selectedPlayer = playerData.find(player => player.numero_licence === numero_licence);
            
            if (selectedPlayer) {
                playerDetails.innerHTML = `
                    <p><strong>Taille:</strong> ${selectedPlayer.taille || 'N/A'} m</p>
                    <p><strong>Poids:</strong> ${selectedPlayer.poids || 'N/A'} kg</p>
                    <p><strong>Statut:</strong> ${selectedPlayer.statut || 'N/A'}</p>
                    <p><strong>Dernier Commentaire:</strong> ${selectedPlayer.commentaire ? selectedPlayer.commentaire.texte_commentaire : 'Pas de commentaire'}</p>
                    <p><strong>Moyenne des Évaluations:</strong> ${selectedPlayer.moyenne_evaluation ? parseFloat(selectedPlayer.moyenne_evaluation).toFixed(2) : 'Aucune évaluation'}</p>
                `;
            } else {
                playerDetails.innerHTML = '<p>Aucune information disponible pour ce joueur.</p>';
            }
        }
        
        // Fonction pour ajouter un joueur à la feuille de match
        async function addPlayerToMatch(token) {
            try {
                const form = document.getElementById('ajout-joueur-form');
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());
                
                const response = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/index.php?action=ajouter&id_match=${data.id_match}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify(data)
                });
                
                const responseData = await response.json();
                
                if (responseData.success) {
                    document.getElementById('success-message').textContent = 'Joueur ajouté avec succès à la feuille de match.';
                    
                    // Réinitialiser le formulaire
                    form.reset();
                    document.getElementById('player-details').innerHTML = '<p>Sélectionnez un joueur pour voir les détails.</p>';
                    
                    // Rafraîchir la liste des joueurs disponibles après 2 secondes
                    setTimeout(() => {
                        fetchMatchAndPlayers(data.id_match, token);
                        document.getElementById('success-message').textContent = '';
                    }, 2000);
                } else {
                    document.getElementById('error-message').textContent = responseData.error || 'Erreur lors de l\'ajout du joueur à la feuille de match.';
                }
            } catch (error) {
                console.error('Erreur:', error);
                document.getElementById('error-message').textContent = 'Erreur lors de l\'envoi des données.';
            }
        }
    </script>
</body>
</html>