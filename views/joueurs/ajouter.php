<?php  
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} ?>

<!DOCTYPE html>
<html>

<head>
    <title>Ajouter un joueur</title>
    <link rel="stylesheet" href="../views/css/style.css">
</head>

<body>
    <div class="table-container">
        <h1>Ajouter un nouveau joueur</h1>
        <form action="JoueursController.php?action=ajouter" method="post">
            <label for="numero_licence">Numéro de licence:</label>
            <input type="text" name="numero_licence" id="numero_licence" required><br>

            <label for="nom">Nom:</label>
            <input type="text" name="nom" id="nom" required><br>

            <label for="prenom">Prénom:</label>
            <input type="text" name="prenom" id="prenom" required><br>

            <label for="date_naissance">Date de naissance:</label>
            <input type="date" name="date_naissance" id="date_naissance" required><br>

            <label for="taille">Taille (cm):</label>
            <input type="number" name="taille" id="taille" required><br>

            <label for="poids">Poids (kg):</label>
            <input type="number" name="poids" id="poids" required><br>

            <label>Statut:</label><br>
            <input type="radio" name="statut" value="Actif" id="actif" checked>
            <label for="actif">Actif</label><br>

            <input type="radio" name="statut" value="Blessé" id="blesse">
            <label for="blesse">Blessé</label><br>

            <input type="radio" name="statut" value="Suspendu" id="suspendu">
            <label for="suspendu">Suspendu</label><br>

            <input type="radio" name="statut" value="Absent" id="absent">
            <label for="absent">Absent</label><br><br>

            <button type="submit">Ajouter</button>
        </form>
        <a href="../controllers/JoueursController.php?action=liste" class="btn btn-back">Retour</a>
    </div>
</body>

</html>
<?php include '../views/footer.php'; ?>