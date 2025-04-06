<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
} ?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier les Joueurs de la Feuille de Match</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>
    <?php include '../../views/header.php'; ?>
    
    <div class="table-container">
        <h1>Modifier les Joueurs de la Feuille de Match</h1>
        
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
        
        <!-- Liste des joueurs déjà sélectionnés -->
        <div id="joueurs-container">
            <h2>Joueurs Sélectionnés</h2>
            <p>Chargement des joueurs sélectionnés...</p>
        </div>
        
        <!-- Formulaire de modification créé dynamiquement par JavaScript -->
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
            
            // Récupérer les détails du match et les joueurs sélectionnés
            fetchMatchAndPlayers(idMatch, token);
        });
        
        // Fonction pour récupérer les détails du match et les joueurs sélectionnés
        async function fetchMatchAndPlayers(idMatch, token) {
            try {
                const response = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/public/index.php?action=feuille_match&sub_action=modifier&id_match=${idMatch}`, {

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
                displaySelectedPlayers(data.joueursSelectionnes || [], idMatch, token);
                
            } catch (error) {
                console.error('Erreur:', error);
                document.getElementById('error-message').textContent = 'Erreur lors de la récupération des données du match et des joueurs.';
            }
        }
        
        // Fonction pour afficher les joueurs sélectionnés avec des formulaires de modification
        function displaySelectedPlayers(players, idMatch, token) {
            const container = document.getElementById('joueurs-container');
            
            if (!players || players.length === 0) {
                container.innerHTML = '<h2>Joueurs Sélectionnés</h2><p>Aucun joueur sélectionné pour ce match.</p>';
                return;
            }
            
            let html = '<h2>Joueurs Sélectionnés</h2>';
            
            // Grouper les joueurs par rôle
            const titulaires = players.filter(j => j.role === 'Titulaire');
            const remplacants = players.filter(j => j.role === 'Remplaçant');
            
            // Afficher les titulaires
            html += '<h3>Titulaires</h3>';
            if (titulaires.length === 0) {
                html += '<p>Aucun titulaire sélectionné.</p>';
            } else {
                html += createPlayerTable(titulaires, idMatch, token, 'Titulaire');
            }
            
            // Afficher les remplacants
            html += '<h3>Remplaçants</h3>';
            if (remplacants.length === 0) {
                html += '<p>Aucun remplaçant sélectionné.</p>';
            } else {
                html += createPlayerTable(remplacants, idMatch, token, 'Remplaçant');
            }
            
            container.innerHTML = html;
            
            // Ajouter les écouteurs d'événements aux formulaires créés dynamiquement
            document.querySelectorAll('.modifier-form').forEach(form => {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const formData = new FormData(form);
                    const data = Object.fromEntries(formData.entries());
                    await updatePlayer(data, token, idMatch);
                });
            });
        }
        
        // Fonction pour créer un tableau de joueurs avec des formulaires de modification
        function createPlayerTable(players, idMatch, token, currentRole) {
            let html = `
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Rôle</th>
                            <th>Poste</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            players.forEach(joueur => {
                html += `
                    <tr>
                        <td>${joueur.nom || 'N/A'}</td>
                        <td>${joueur.prenom || 'N/A'}</td>
                        <td>
                        <form class="modifier-form" id="form-${joueur.id_selection}">
    <input type="hidden" name="id_selection" value="${joueur.id_selection}">
    <input type="hidden" name="id_match" value="${idMatch}">
    <input type="hidden" name="numero_licence" value="${joueur.numero_licence}">
    <select name="role" required>
        <option value="Titulaire" ${joueur.role === 'Titulaire' ? 'selected' : ''}>Titulaire</option>
        <option value="Remplacant" ${joueur.role === 'Remplaçant' ? 'selected' : ''}>Remplaant</option>
    </select>
</form>
                        </td>
                        <td>
                        <select form="form-${joueur.id_selection}" name="poste" required>
    <option value="">Sélectionnez un poste</option>
    <option value="Gardien de But" ${joueur.poste === 'Gardien de But' ? 'selected' : ''}>Gardien de But</option>
    <option value="Défenseur Central" ${joueur.poste === 'Défenseur Central' ? 'selected' : ''}>Défenseur Central</option>
    <option value="Défenseur Latéral" ${joueur.poste === 'Défenseur Latéral' ? 'selected' : ''}>Défenseur Latéral</option>
    <option value="Arrière Latéral Offensif" ${joueur.poste === 'Arrière Latéral Offensif' ? 'selected' : ''}>Arrière Latéral Offensif</option>
    <option value="Libéro" ${joueur.poste === 'Libéro' ? 'selected' : ''}>Libéro</option>
    <option value="Milieu Défensif" ${joueur.poste === 'Milieu Défensif' ? 'selected' : ''}>Milieu Défensif</option>
    <option value="Milieu Central" ${joueur.poste === 'Milieu Central' ? 'selected' : ''}>Milieu Central</option>
    <option value="Milieu Offensif" ${joueur.poste === 'Milieu Offensif' ? 'selected' : ''}>Milieu Offensif</option>
    <option value="Milieu Latéral" ${joueur.poste === 'Milieu Latéral' ? 'selected' : ''}>Milieu Latéral</option>
    <option value="Attaquant Central" ${joueur.poste === 'Attaquant Central' ? 'selected' : ''}>Attaquant Central</option>
    <option value="Avant-Centre" ${joueur.poste === 'Avant-Centre' ? 'selected' : ''}>Avant-Centre</option>
    <option value="Ailier" ${joueur.poste === 'Ailier' ? 'selected' : ''}>Ailier</option>
    <option value="Second Attaquant" ${joueur.poste === 'Second Attaquant' ? 'selected' : ''}>Second Attaquant</option>
</select>
                        </td>
                        <td>
                            <button type="submit" form="form-${joueur.id_selection}" class="btn btn-edit">Modifier</button>
                        </td>
                    </tr>
                `;
            });
            
            html += `
                    </tbody>
                </table>
            `;
            
            return html;
        }
        
        // Fonction pour mettre à jour un joueur
        async function updatePlayer(data, token, idMatch) {
            try {
                // Afficher un message de chargement
                document.getElementById('success-message').textContent = 'Mise à jour en cours...';
                document.getElementById('error-message').textContent = '';
                
                const response = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/public/index.php?action=feuille_match&sub_action=valider_modification&id_match=${idMatch}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('success-message').textContent = result.message || 'Joueur mis à jour avec succès.';
                    
                    // Mettre à jour l'affichage des joueurs si disponible dans la réponse
                    if (result.joueursSelectionnes) {
                        displaySelectedPlayers(result.joueursSelectionnes, idMatch, token);
                    }
                } else {
                    document.getElementById('error-message').textContent = result.error || 'Erreur lors de la mise à jour du joueur.';
                }
            } catch (error) {
                console.error('Erreur:', error);
                document.getElementById('error-message').textContent = 'Erreur lors de la communication avec le serveur.';
            }
        }
    </script>
</body>
</html>