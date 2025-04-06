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
        
        /* Ajout de styles pour rendre les étoiles visibles par défaut */
        .rating > label:after {
            content: "\2606"; /* Caractère étoile non remplie */
            color: #FFD700;
            position: absolute;
            left: 0;
        }
        
        .rating > input:checked ~ label:after {
            content: "\2605"; /* Caractère étoile remplie quand sélectionnée */
            color: #FFD700;
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
                const response = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/public/index.php?action=feuille_match&sub_action=evaluer&id_match=${idMatch}`, {
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
                displayEvaluationForm(data.joueurs || [], idMatch, token);
                
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
await submitEvaluation(formData, token, idMatch);


                });
            });
        }
        
        // Fonction pour créer les formulaires d'évaluation individuels
        function createEvaluationForms(players, idMatch, token) {
            let html = '';
            
            players.forEach(joueur => {
                const formId = `form-${joueur.numero_licence}`;
                // Utiliser note au lieu de note_joueur et définir une valeur par défaut à 1
                const noteValue = joueur.evaluation || 1;
                
                html += `
    <div class="player-evaluation">
        <h3>${joueur.nom_joueur || 'N/A'} ${joueur.prenom_joueur || 'N/A'} - ${joueur.poste || 'N/A'}</h3>
        <form class="evaluation-form" id="${formId}">
            <input type="hidden" name="numero_licence" value="${joueur.numero_licence}">
            <input type="hidden" name="id_match" value="${idMatch}">
            
            <label for="evaluation-${joueur.numero_licence}">Note (1 à 5):</label>
            <input type="number" 
                   id="evaluation-${joueur.numero_licence}" 
                   name="evaluation" 
                   min="1" 
                   max="5" 
                   value="${noteValue}" 
                   required 
                   style="width: 60px; margin-left: 10px;">
            
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
 // Fonction pour soumettre l'évaluation d'un joueur
async function submitEvaluation(formData, token, idMatch) {
    const data = {
        numero_licence: formData.get('numero_licence'),
        id_match: formData.get('id_match'),
        evaluation: parseInt(formData.get('evaluation'))
    };

    try {
    const response = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/public/index.php?action=feuille_match&sub_action=valider_evaluation&id_match=${idMatch}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify(data)
    });

    const text = await response.text(); // <-- Affiche le contenu brut
    console.log('Réponse brute:', text);

    let responseData;
    try {
        responseData = JSON.parse(text); // <-- Essaye de parser
    } catch (e) {
        document.getElementById('error-message').textContent = "Erreur : réponse invalide reçue du serveur.";
        return;
    }

    if (responseData.success) {
        document.getElementById('success-message').textContent = 'Évaluation enregistrée avec succès.';
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