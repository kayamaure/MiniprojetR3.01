<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
} ?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Évaluer les Joueurs du Match</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
        }
        
        .rating > input {
            display: none;
        }
        
        .rating > label {
            position: relative;
            width: 1.1em;
            font-size: 25px;
            color: #FFD700;
            cursor: pointer;
        }
        
        .rating > label::before {
            content: "\2605";
            position: absolute;
            opacity: 0;
        }
        
        .rating > label:hover:before,
        .rating > label:hover ~ label:before {
            opacity: 1 !important;
        }
        
        .rating > input:checked ~ label:before {
            opacity: 1;
        }
        
        .rating:hover > input:checked ~ label:before {
            opacity: 0.4;
        }
        
        .player-evaluation {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .player-evaluation h3 {
            margin-top: 0;
        }
    </style>
</head>

<body>
    <?php include '../../views/header.php'; ?>
    
    <div class="table-container">
        <h1>Évaluer les Joueurs du Match</h1>
        
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
        
        <!-- Formulaire d'évaluation -->
        <div id="evaluation-container">
            <h2>Évaluation des Joueurs</h2>
            <p>Chargement des joueurs à évaluer...</p>
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
            
            // Récupérer les détails du match et les joueurs à évaluer
            fetchMatchAndPlayers(idMatch, token);
        });
        
        // Fonction pour récupérer les détails du match et les joueurs à évaluer
        async function fetchMatchAndPlayers(idMatch, token) {
            try {
                const response = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/index.php?action=evaluer&id_match=${idMatch}`, {
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
                        <p><strong>Résultat:</strong> ${data.match.resultat || 'N/A'}</p>
                    `;
                }
                
                // Afficher le formulaire d'évaluation pour les joueurs
                displayEvaluationForm(data.joueursSelectionnes || [], idMatch, token);
                
            } catch (error) {
                console.error('Erreur:', error);
                document.getElementById('error-message').textContent = 'Erreur lors de la récupération des données du match et des joueurs.';
            }
        }
        
        // Fonction pour afficher le formulaire d'évaluation des joueurs
        function displayEvaluationForm(players, idMatch, token) {
            const container = document.getElementById('evaluation-container');
            
            if (!players || players.length === 0) {
                container.innerHTML = '<h2>Évaluation des Joueurs</h2><p>Aucun joueur à évaluer pour ce match.</p>';
                return;
            }
            
            let html = '<h2>Évaluation des Joueurs</h2>';
            html += '<p>Attribuez une note de 1 à 5 étoiles à chaque joueur qui a participé au match:</p>';
            
            // Grouper les joueurs par rôle
            const titulaires = players.filter(j => j.role === 'Titulaire');
            const remplacants = players.filter(j => j.role === 'Remplacant');
            
            // Créer le formulaire pour les titulaires
            if (titulaires.length > 0) {
                html += '<h3>Titulaires</h3>';
                html += createEvaluationForms(titulaires, idMatch, token);
            }
            
            // Créer le formulaire pour les remplacants
            if (remplacants.length > 0) {
                html += '<h3>Remplacants</h3>';
                html += createEvaluationForms(remplacants, idMatch, token);
            }
            
            container.innerHTML = html;
            
            // Ajouter les écouteurs d'événements aux formulaires créés dynamiquement
            document.querySelectorAll('.evaluation-form').forEach(form => {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const formData = new FormData(form);
                    const data = Object.fromEntries(formData.entries());
                    await submitEvaluation(data, token, idMatch);
                });
            });
        }
        
        // Fonction pour créer les formulaires d'évaluation individuels
        function createEvaluationForms(players, idMatch, token) {
            let html = '';
            
            players.forEach(joueur => {
                const formId = `form-${joueur.id_selection}`;
                const noteValue = joueur.note_joueur ? joueur.note_joueur : 0;
                
                html += `
                    <div class="player-evaluation">
                        <h3>${joueur.nom} ${joueur.prenom} - ${joueur.poste}</h3>
                        <form class="evaluation-form" id="${formId}">
                            <input type="hidden" name="id_selection" value="${joueur.id_selection}">
                            <input type="hidden" name="id_match" value="${idMatch}">
                            
                            <div class="rating">
                                <input type="radio" id="star5-${joueur.id_selection}" name="note" value="5" ${noteValue == 5 ? 'checked' : ''} />
                                <label for="star5-${joueur.id_selection}" title="5 étoiles"></label>
                                
                                <input type="radio" id="star4-${joueur.id_selection}" name="note" value="4" ${noteValue == 4 ? 'checked' : ''} />
                                <label for="star4-${joueur.id_selection}" title="4 étoiles"></label>
                                
                                <input type="radio" id="star3-${joueur.id_selection}" name="note" value="3" ${noteValue == 3 ? 'checked' : ''} />
                                <label for="star3-${joueur.id_selection}" title="3 étoiles"></label>
                                
                                <input type="radio" id="star2-${joueur.id_selection}" name="note" value="2" ${noteValue == 2 ? 'checked' : ''} />
                                <label for="star2-${joueur.id_selection}" title="2 étoiles"></label>
                                
                                <input type="radio" id="star1-${joueur.id_selection}" name="note" value="1" ${noteValue == 1 ? 'checked' : ''} />
                                <label for="star1-${joueur.id_selection}" title="1 étoile"></label>
                            </div>
                            
                            <div style="margin-top: 10px;">
                                <button type="submit" class="btn btn-edit">Enregistrer l'évaluation</button>
                            </div>
                        </form>
                    </div>
                `;
            });
            
            return html;
        }
        
        // Fonction pour soumettre l'évaluation d'un joueur
        async function submitEvaluation(data, token, idMatch) {
            try {
                const response = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/index.php?action=evaluer&id_match=${idMatch}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify(data)
                });
                
                const responseData = await response.json();
                
                if (responseData.success) {
                    document.getElementById('success-message').textContent = 'Évaluation enregistrée avec succès.';
                    
                    // Effacer le message de succès après 2 secondes
                    setTimeout(() => {
                        document.getElementById('success-message').textContent = '';
                    }, 2000);
                } else {
                    document.getElementById('error-message').textContent = responseData.error || 'Erreur lors de l\'enregistrement de l\'évaluation.';
                }
            } catch (error) {
                console.error('Erreur:', error);
                document.getElementById('error-message').textContent = 'Erreur lors de l\'envoi des données.';
            }
        }
    </script>
</body>
</html>