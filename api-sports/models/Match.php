<?php
// Modèle Match
class GameMatch
{
    private $conn;
    private $table = "match_"; // Nom de la table

    public function __construct($db)
    {
        $this->conn = $db;
    }
    // Récupérer tous les matchs
    public function obtenirTousLesMatchs()
    {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ajouter un match
    public function ajouterMatch($data)
    {
        $statut = (strtotime($data['date_match'] . ' ' . $data['heure_match']) > time()) ? 'À venir' : 'Terminé';

        $query = "INSERT INTO " . $this->table . " (date_match, heure_match, nom_equipe_adverse, lieu_de_rencontre, statut) 
                  VALUES (:date_match, :heure_match, :nom_equipe_adverse, :lieu_de_rencontre, :statut)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':date_match', $data['date_match']);
        $stmt->bindParam(':heure_match', $data['heure_match']);
        $stmt->bindParam(':nom_equipe_adverse', $data['nom_equipe_adverse']);
        $stmt->bindParam(':lieu_de_rencontre', $data['lieu_de_rencontre']);
        $stmt->bindParam(':statut', $statut);

        return $stmt->execute();
    }

    // Récuperer un match
    public function obtenirMatch($id_match)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id_match = :id_match";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_match', $id_match);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // MAJ d'un match
    public function mettreAJourMatch($data)
    {
        $statut = (strtotime($data['date_match'] . ' ' . $data['heure_match']) > time()) ? 'À venir' : 'Terminé';

        $query = "UPDATE " . $this->table . " 
                  SET date_match = :date_match, 
                      heure_match = :heure_match, 
                      lieu_de_rencontre = :lieu_de_rencontre, 
                      statut = :statut";

        if (isset($data['resultat'])) {
            $query .= ", resultat = :resultat";
        }

        $query .= " WHERE id_match = :id_match";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':date_match', $data['date_match']);
        $stmt->bindParam(':heure_match', $data['heure_match']);
        $stmt->bindParam(':lieu_de_rencontre', $data['lieu_de_rencontre']);
        $stmt->bindParam(':statut', $statut);
        $stmt->bindParam(':id_match', $data['id_match']);

        if (isset($data['resultat'])) {
            $stmt->bindParam(':resultat', $data['resultat']);
        }

        return $stmt->execute();
    }

    // Obtenir les matchs par status (A venir ou terminé)
    public function obtenirMatchsParStatut($statut)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE statut = :statut";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':statut', $statut);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Suppression d'un match
    public function supprimerMatch($id_match)
    {
        $query = "DELETE FROM " . $this->table . " WHERE id_match = :id_match";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_match', $id_match);
        return $stmt->execute();
    }

    // Vérif si un match est terminé
    public function estMatchDansLePasse($id_match)
    {
        $query = "SELECT date_match, heure_match FROM " . $this->table . " WHERE id_match = :id_match";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_match', $id_match);
        $stmt->execute();

        $match = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($match) {
            $matchDateTime = $match['date_match'] . ' ' . $match['heure_match'];
            return strtotime($matchDateTime) < time(); // Retourne `true` si la date/heure est passée
        }

        return null; // Retourne null si le match n'est pas trouvé
    }
}
