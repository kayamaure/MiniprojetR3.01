<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../header.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Statistiques des Joueurs</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>
    <div class="container">
        <h1>Statistiques des Joueurs</h1>

        <!-- Formulaire dynamique -->
        <form id="stat-form">
            <label for="numero_licence">Sélectionnez un joueur :</label>
            <select name="numero_licence" id="numero_licence" required>
                <option value="">-- Choisir un joueur --</option>
            </select>
            <button type="submit">Afficher les statistiques</button>
        </form>

        <!-- Conteneur pour les résultats -->
        <div id="stats-container" style="margin-top: 2rem;"></div>

        <!-- Bouton retour -->
        <div class="return-button">
            <a href="statistiques.php" class="btn btn-back">Retour</a>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const token = localStorage.getItem("authToken");
            const joueurSelect = document.getElementById("numero_licence");
            const form = document.getElementById("stat-form");
            const statsContainer = document.getElementById("stats-container");

            // Charger dynamiquement les joueurs
            async function loadJoueurs() {
                try {
                    const res = await fetch("http://127.0.0.1/MiniprojetR3.01/api-sports/public/index.php?action=statistiques&type=joueurs", {
                        headers: { "Authorization": `Bearer ${token}` }
                    });
                    const joueurs = await res.json();

                    if (Array.isArray(joueurs)) {
                        joueurs.forEach(joueur => {
                            const option = document.createElement("option");
                            option.value = joueur.numero_licence;
                            option.textContent = `${joueur.nom} ${joueur.prenom}`;
                            joueurSelect.appendChild(option);
                        });
                    } else {
                        joueurSelect.innerHTML = `<option disabled>Erreur lors du chargement</option>`;
                    }
                } catch (e) {
                    console.error(e);
                    joueurSelect.innerHTML = `<option disabled>Erreur serveur</option>`;
                }
            }

            // Récupérer et afficher les statistiques d’un joueur
            async function fetchStatistiques(numeroLicence) {
                try {
                    const response = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/public/index.php?action=statistiques&type=joueur-stats&numero_licence=${numeroLicence}`, {
                        headers: { "Authorization": `Bearer ${token}` }
                    });
                    const data = await response.json();

                    if (data.error) {
                        statsContainer.innerHTML = `<p style="color:red">${data.error}</p>`;
                        return;
                    }

                    statsContainer.innerHTML = `
                        <h2>Statistiques du Joueur</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>Statut</th>
                                    <th>Poste Préféré</th>
                                    <th>Titularisations</th>
                                    <th>Remplacements</th>
                                    <th>Moy. Évaluations</th>
                                    <th>% Matchs Gagnés</th>
                                    <th>Sélections Consécutives</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>${data.statut?.statut ?? 'N/A'}</td>
                                    <td>${data.poste_prefere ?? 'N/A'}</td>
                                    <td>${data.titularisations ?? 0}</td>
                                    <td>${data.remplacements ?? 0}</td>
                                    <td>${data.moyenne_evaluations?.toFixed(2) ?? '0.00'}</td>
                                    <td>${data.pourcentage_matchs_gagnes?.toFixed(2) ?? '0.00'}%</td>
                                    <td>${data.selections_consecutives ?? 0}</td>
                                </tr>
                            </tbody>
                        </table>
                    `;
                } catch (err) {
                    console.error(err);
                    statsContainer.innerHTML = `<p style="color:red">Erreur lors de la récupération des statistiques.</p>`;
                }
            }

            // Au submit du formulaire
            form.addEventListener("submit", (e) => {
                e.preventDefault();
                const licence = joueurSelect.value;
                if (licence) fetchStatistiques(licence);
            });

            // Charger les joueurs au chargement de la page
            loadJoueurs();
        });
    </script>
</body>

</html>
