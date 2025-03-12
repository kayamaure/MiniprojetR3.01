<?php
// Modèle Statistiques

class Statistiques
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Nombre total de matchs gagnés, nuls, perdus
    public function obtenirStatistiquesMatchs()
    {
        $query = "
            SELECT 
                COUNT(CASE WHEN resultat = 'Victoire' THEN 1 END) AS matchs_gagnes,
                COUNT(CASE WHEN resultat = 'Match Nul' THEN 1 END) AS matchs_nuls,
                COUNT(CASE WHEN resultat = 'Défaite' THEN 1 END) AS matchs_perdus
            FROM match_;
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Pourcentage de matchs gagnés, nuls, perdus
    public function obtenirPourcentagesMatchs()
    {
        $query = "
            SELECT 
                COUNT(*) AS total_matchs,
                COUNT(CASE WHEN resultat = 'Victoire' THEN 1 END) * 100.0 / COUNT(*) AS pourcentage_gagnes,
                COUNT(CASE WHEN resultat = 'Match Nul' THEN 1 END) * 100.0 / COUNT(*) AS pourcentage_nuls,
                COUNT(CASE WHEN resultat = 'Défaite' THEN 1 END) * 100.0 / COUNT(*) AS pourcentage_perdus
            FROM match_;
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 1. Obtenir le statut actuel d'un joueur
    public function obtenirStatutJoueur($numeroLicence)
    {
        $query = "SELECT nom, prenom, statut FROM joueur WHERE numero_licence = :numero_licence";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':numero_licence', $numeroLicence);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 2. Obtenir le poste préféré d'un joueur (poste où il a joué le plus souvent)
    public function obtenirPostePrefere($numeroLicence)
    {
        $query = "
            SELECT poste, COUNT(poste) AS occurrences 
            FROM participer 
            WHERE numero_licence = :numero_licence 
            GROUP BY poste 
            ORDER BY occurrences DESC 
            LIMIT 1
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':numero_licence', $numeroLicence);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['poste'] ?? 'Non défini';
    }

    // 3. Obtenir le nombre de titularisations d'un joueur
    public function obtenirNombreTitularisations($numeroLicence)
    {
        $query = "
            SELECT COUNT(*) AS titularisations 
            FROM participer 
            WHERE numero_licence = :numero_licence AND role = 'Titulaire'
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':numero_licence', $numeroLicence);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['titularisations'] ?? 0;
    }

    // 4. Obtenir le nombre de remplacements d'un joueur
    public function obtenirNombreRemplacements($numeroLicence)
    {
        $query = "
            SELECT COUNT(*) AS remplacements 
            FROM participer 
            WHERE numero_licence = :numero_licence AND role = 'Remplaçant'
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':numero_licence', $numeroLicence);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['remplacements'] ?? 0;
    }

    // 5. Obtenir la moyenne des évaluations d'un joueur
    public function obtenirMoyenneEvaluations($numeroLicence)
    {
        $query = "
            SELECT AVG(evaluation) AS moyenne 
            FROM participer 
            WHERE numero_licence = :numero_licence AND evaluation IS NOT NULL
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':numero_licence', $numeroLicence);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['moyenne'] ? round($result['moyenne'], 2) : 0.0;
    }

    // 6. Obtenir le pourcentage de matchs gagnés par un joueur
    public function obtenirPourcentageMatchsGagnes($numeroLicence)
    {
        $query = "
            SELECT COUNT(m.id_match) AS total_matchs, 
                   COUNT(CASE WHEN m.resultat = 'Victoire' THEN 1 END) AS matchs_gagnes
            FROM participer p
            JOIN match_ m ON p.id_match = m.id_match
            WHERE p.numero_licence = :numero_licence
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':numero_licence', $numeroLicence);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['total_matchs'] > 0) {
            return round(($result['matchs_gagnes'] / $result['total_matchs']) * 100, 2);
        }
        return 0.0;
    }

    // 7. Obtenir le nombre de sélections consécutives d'un joueur
    public function obtenirSelectionsConsecutives($numeroLicence)
    {
        $query = "
            SELECT id_match
            FROM participer
            WHERE numero_licence = :numero_licence
            ORDER BY id_match ASC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':numero_licence', $numeroLicence);
        $stmt->execute();
        $matches = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($matches)) {
            return 0;
        }

        $maxStreak = $currentStreak = 1;

        for ($i = 1; $i < count($matches); $i++) {
            if ($matches[$i] - $matches[$i - 1] === 1) {
                $currentStreak++;
                $maxStreak = max($maxStreak, $currentStreak);
            } else {
                $currentStreak = 1;
            }
        }

        return $maxStreak;
    }
}
