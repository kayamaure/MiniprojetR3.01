<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
} ?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du Match</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>
    <?php include '../../views/header.php'; ?>
    
    <div class="table-container">
        <h1>Détails du Match</h1>
        
        <!-- Message d'erreur -->
        <div id="error-message" style="color: red;"></div>
        <!-- Message de succès -->
        <div id="success-message" style="color: green;"></div>
        
        <a href="../matchs/index.php" class="btn btn-back">Retour à la liste des matchs</a>
        
        <!-- Informations du match -->
        <div id="match-details">
            <!-- Les détails du match seront insérés ici par JavaScript -->
            <p>Chargement des détails du match...</p>
        </div>
        
        <h2>Actions disponibles</h2>
        <div id="actions" class="actions">
            <!-- Les actions seront insérées ici par JavaScript -->
            <p>Chargement des actions disponibles...</p>
        </div>
        
        <div class="table-container-feuille">
            <h2>Liste des Joueurs pour ce Match</h2>
            
            <h3>Titulaires</h3>
            <div id="titulaires-container">
                <!-- La liste des titulaires sera insérée ici par JavaScript -->
                <p>Chargement des titulaires...</p>
            </div>
            
            <h3>Remplaçants</h3>
            <div id="remplacants-container">
                <!-- La liste des remplaçants sera insérée ici par JavaScript -->
                <p>Chargement des remplaçants...</p>
            </div>
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
            
            // Récupérer les détails du match et les joueurs
            fetchMatchDetails(idMatch, token);
        });
        
        // Fonction pour récupérer les détails du match et les joueurs
        async function fetchMatchDetails(idMatch, token) {
            try {
                const response = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/public/index.php?action=feuille_match&sub_action=afficher&id_match=${idMatch}`, {
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
                displayMatchDetails(data.match, idMatch);
                
                // Afficher les titulaires
                displayPlayers('titulaires-container', data.titulaires, 'Aucun titulaire trouvé.');
                
                // Afficher les remplaçants
                displayPlayers('remplacants-container', data.remplacants, 'Aucun remplaçant trouvé.');
                
            } catch (error) {
                console.error('Erreur:', error);
                document.getElementById('error-message').textContent = 'Erreur lors de la récupération des détails du match.';
            }
        }
        
        // Fonction pour afficher les détails du match et les actions disponibles
        function displayMatchDetails(match, idMatch) {
            if (!match) {
                document.getElementById('match-details').innerHTML = '<p>Aucune information sur ce match.</p>';
                return;
            }
            
            // Afficher les détails du match
            document.getElementById('match-details').innerHTML = `
                <p><strong>Date :</strong> ${match.date_match}</p>
                <p><strong>Heure :</strong> ${match.heure_match}</p>
                <p><strong>Équipe Adverse :</strong> ${match.nom_equipe_adverse}</p>
                <p><strong>Lieu :</strong> ${match.lieu_de_rencontre}</p>
                <p><strong>Résultat :</strong> ${match.resultat || 'N/A'}</p>
            `;
            
            // Afficher les actions disponibles selon l'état de la feuille et le statut du match
            let actionsHtml = '';
            
            if (match.etat_feuille === 'Non validé' || match.statut === 'À venir') {
                actionsHtml += `
                <a href="#" class="btn btn-add" onclick="validerFeuille('${idMatch}', localStorage.getItem('authToken')); return false;">
    Valider la Feuille de Match
</a>
     `;
            } else if (match.etat_feuille === 'Validé' && match.statut === 'À venir') {
                actionsHtml += '<p>La feuille de match est validée, mais vous pouvez toujours la modifier.</p>';
            }
            
            // Actions disponibles, même après validation
            if (match.statut === 'À venir') {
                actionsHtml += `
                    <a href="ajouter.php?id_match=${idMatch}" class="btn btn-add">
                        Ajouter Joueur
                    </a>
                    <a href="modifier.php?id_match=${idMatch}" class="btn btn-edit">
                        Modifier les Joueurs
                    </a>
                    <a href="supprimer.php?id_match=${idMatch}" class="btn btn-delete">
                        Supprimer Joueurs de la Sélection
                    </a>
                `;
            }
            
            if (match.statut === 'Terminé') {
                actionsHtml += `
                    <a href="evaluer.php?id_match=${idMatch}" class="btn btn-add">
                        Évaluer les joueurs
                    </a>
                `;
            }
            
            document.getElementById('actions').innerHTML = actionsHtml;
        }
        
        // Fonction pour afficher les joueurs dans un tableau
        function displayPlayers(containerId, players, emptyMessage) {
            const container = document.getElementById(containerId);
            
            if (!players || players.length === 0) {
                container.innerHTML = `<p>${emptyMessage}</p>`;
                return;
            }
            
            let html = `
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Poste</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            players.forEach(joueur => {
                html += `
                    <tr>
                        <td>${joueur.nom_joueur || 'N/A'}</td>
                        <td>${joueur.prenom_joueur || 'N/A'}</td>
                        <td>${joueur.poste || 'N/A'}</td>
                    </tr>
                `;
            });
            
            html += `
                    </tbody>
                </table>
            `;
            
            container.innerHTML = html;
        }
        
        // Fonction pour valider la feuille de match
        async function validerFeuille(idMatch, token) {
    try {
        console.log("🔄 Validation en cours...");
        console.log("➡️ idMatch:", idMatch);
        console.log("🛡️ token:", token);

        const response = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/public/index.php?action=feuille_match&sub_action=valider_feuille&id_match=${idMatch}`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        const data = await response.json();

        console.log("✅ Réponse brute:", response);
        console.log("📦 Données JSON:", data);

        const successMessage = data.success || data.message;

        if (successMessage) {
            document.getElementById('success-message').style.color = 'green';
            document.getElementById('success-message').textContent = '✅ ' + successMessage + ' Redirection en cours...';

            // Redirection après 2 secondes
            setTimeout(() => {
    window.location.href = '/MiniprojetR3.01/frontend/views/matchs/index.php';
}, 2000);
        } else {
            document.getElementById('error-message').textContent = data.error || '❌ Une erreur est survenue lors de la validation.';
        }
    } catch (error) {
        console.error('🚨 Erreur dans validerFeuille:', error);
        document.getElementById('error-message').textContent = '❌ Erreur technique lors de la validation de la feuille de match.';
    }
}



    </script>
</body>
</html>