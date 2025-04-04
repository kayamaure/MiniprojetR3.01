<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Match</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php 
    // Inclusion du header
    include '../../views/header.php';
    ?>

    <div class="container">
        <h1>Modifier le Match</h1>
        
        <!-- Message d'erreur -->
        <div id="error-message" style="color: red;"></div>
        
        <form id="match-form">
            <input type="hidden" name="id_match" id="id_match">
            
            <!-- Date du match -->
            <div>
                <label for="date_match">Date :</label>
                <input 
                    type="date" 
                    name="date_match" 
                    id="date_match" 
                    required>
            </div>

            <!-- Heure du match -->
            <div>
                <label for="heure_match">Heure :</label>
                <input 
                    type="time" 
                    name="heure_match" 
                    id="heure_match" 
                    required>
            </div>

            <!-- Lieu de rencontre -->
            <div>
                <label for="lieu_match">Lieu de rencontre :</label>
                <select 
                    name="lieu_match" 
                    id="lieu_match" 
                    required>
                    <option value="">-- Sélectionner --</option>
                    <option value="Domicile">Domicile</option>
                    <option value="Extérieur">Extérieur</option>
                </select>
            </div>
            
            <!-- Nom de l'équipe adverse -->
            <div>
                <label for="nom_adversaire">Nom de l'équipe adverse :</label>
                <input 
                    type="text" 
                    name="nom_adversaire" 
                    id="nom_adversaire" 
                    required>
            </div>

            <!-- Résultat (s'affiche uniquement si le match est dans le passé) -->
            <div id="resultat-container" style="display: none;">
                <label for="score_equipe">Score de l'équipe :</label>
                <input 
                    type="number" 
                    name="score_equipe" 
                    id="score_equipe" 
                    min="0">
                
                <label for="score_adversaire">Score de l'adversaire :</label>
                <input 
                    type="number" 
                    name="score_adversaire" 
                    id="score_adversaire" 
                    min="0">
            </div>

            <!-- Boutons -->
            <button type="submit" class="btn btn-edit">Modifier</button>
            <a href="index.php" class="btn btn-back">Retour</a>
        </form>
    </div>

    <?php include '../../views/footer.php'; ?>
    
    <script>
        // Vérifier l'authentification au chargement de la page
        document.addEventListener('DOMContentLoaded', async function() {
            const token = localStorage.getItem('authToken');
            if (!token) {
                // Rediriger vers la page de connexion si pas de token
                window.location.href = '/MiniprojetR3.01/frontend/views/connexion.php';
                return;
            }
            
            // Récupérer l'ID du match depuis l'URL
            const urlParams = new URLSearchParams(window.location.search);
            const matchId = urlParams.get('id_match');
            
            if (!matchId) {
                document.getElementById('error-message').textContent = 'ID du match non spécifié.';
                return;
            }
            
            // Récupérer les informations du match
            try {
                const response = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/index.php?action=match&id_match=${matchId}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });
                
                const data = await response.json();
                
                if (data.error) {
                    document.getElementById('error-message').textContent = data.error;
                    return;
                }
                
                // Remplir le formulaire avec les données du match
                document.getElementById('id_match').value = data.id_match;
                document.getElementById('date_match').value = data.date_match;
                document.getElementById('heure_match').value = data.heure_match;
                document.getElementById('lieu_match').value = data.lieu_de_rencontre;
                document.getElementById('nom_adversaire').value = data.nom_equipe_adverse;
                
                // Vérifier si le match est dans le passé
                const matchDate = new Date(data.date_match + 'T' + data.heure_match);
                const now = new Date();
                const isMatchInThePast = matchDate < now;
                
                if (isMatchInThePast) {
                    // Afficher les champs de score si le match est dans le passé
                    document.getElementById('resultat-container').style.display = 'block';
                    document.getElementById('score_equipe').value = data.score_equipe || 0;
                    document.getElementById('score_adversaire').value = data.score_adversaire || 0;
                }
                
            } catch (error) {
                console.error('Error fetching match:', error);
                document.getElementById('error-message').textContent = 'Erreur lors de la récupération des données du match.';
            }
            
            // Gestion du formulaire de modification
            document.getElementById('match-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                try {
                    const token = localStorage.getItem('authToken');
                    const formData = new FormData(this);
                    const matchData = {};
                    
                    // Convertir FormData en objet
                    for (const [key, value] of formData.entries()) {
                        // Mapper les noms de champs du formulaire vers les noms de champs de l'API
                        if (key === 'lieu_match') {
                            matchData['lieu_de_rencontre'] = value;
                        } else if (key === 'nom_adversaire') {
                            matchData['nom_equipe_adverse'] = value;
                        } else if (key === 'score_equipe') {
                            matchData['score_domicile'] = value;
                        } else if (key === 'score_adversaire') {
                            matchData['score_exterieur'] = value;
                        } else {
                            matchData[key] = value;
                        }
                    }
                    
                    const response = await fetch('http://127.0.0.1/MiniprojetR3.01/api-sports/index.php?action=modifier_match', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${token}`
                        },
                        body: JSON.stringify(matchData)
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Rediriger vers la liste des matchs
                        window.location.href = 'index.php';
                    } else {
                        document.getElementById('error-message').textContent = data.error || 'Erreur lors de la modification du match.';
                    }
                } catch (error) {
                    console.error('Error updating match:', error);
                    document.getElementById('error-message').textContent = 'Erreur lors de la modification du match.';
                }
            });
        });
    </script>
</body>
</html>
