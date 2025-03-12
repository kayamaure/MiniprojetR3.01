<?php include '../views/header.php'; 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}  ?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ajouter un Match</title>
    <link rel="stylesheet" href="../views/css/style.css">
</head>

<body>



    <div class="container">
        <h1>Ajouter un Match</h1>
        <form action="MatchsController.php?action=ajouter" method="POST">

            <!-- Équipe adverse -->
            <div>
                <label for="nom_equipe_adverse">Nom de l'équipe adverse :</label>
                <input
                    type="text"
                    name="nom_equipe_adverse"
                    id="nom_equipe_adverse"
                    placeholder="Ex: CF Exemple">

            </div>


            <!-- Date et heure du match -->
            <div>
                <label for="date_match">Date du match :</label>
                <input
                    type="date"
                    name="date_match"
                    id="date_match"
                    required>

                <label for="heure_match">Heure du match :</label>
                <input
                    type="time"
                    name="heure_match"
                    id="heure_match">
            </div>


            <!-- Lieu de rencontre (domicile ou extérieur) -->
            <div>
                <label for="lieu_de_rencontre">Lieu de la rencontre :</label>
                <select name="lieu_de_rencontre" id="lieu_de_rencontre">
                    <option value="">-- Sélectionner --</option>
                    <option value="Domicile">Domicile</option>
                    <option value="Extérieur">Extérieur</option>
                </select>
            </div>

            <!-- Bouton de soumission -->
            <button type="submit">Ajouter</button>
            <div style="text-align: center; margin-top: 20px;">
                <a href="../controllers/MatchsController.php?action=liste" class="btn btn-back">Retour</a>
            </div>
        </form>
    </div>

</body>

</html>
<?php include '../views/footer.php'; ?>