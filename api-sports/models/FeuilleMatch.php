<?php
// Modèle FeuilleMatch
class FeuilleMatch
{
    private $conn;
    private $table = "participer";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Récupérer les joueurs d'une feuille de match
    public function obtenirJoueursParMatch($id_match)
    {
        $query = "SELECT j.nom AS nom_joueur, j.prenom AS prenom_joueur, p.role, p.poste, p.numero_licence
                FROM " . $this->table . " p
                JOIN joueur j ON p.numero_licence = j.numero_licence
                WHERE p.id_match = :id_match";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_match', $id_match);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer tous les joueurs sélectionnés avec ID de sélection pour la modification
    public function obtenirJoueursSelectionnes($id_match)
    {
        $query = "SELECT p.id AS id_selection, j.nom, j.prenom, p.role, p.poste, j.numero_licence
                FROM " . $this->table . " p
                JOIN joueur j ON p.numero_licence = j.numero_licence
                WHERE p.id_match = :id_match
                ORDER BY p.role ASC, p.poste ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_match', $id_match);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Mettre à jour le rôle et le poste d'un joueur dans la sélection
    public function mettreAJourJoueurSelection($id_selection, $role = null, $poste = null)
    {
        // Vérifier si l'ID de sélection est fourni
        if (empty($id_selection)) {
            return false;
        }

        // Construire la requête de mise à jour en fonction des paramètres fournis
        $updateFields = [];
        $params = [':id_selection' => $id_selection];

        if ($role !== null) {
            $updateFields[] = "role = :role";
            $params[':role'] = $role;
        }

        if ($poste !== null) {
            $updateFields[] = "poste = :poste";
            $params[':poste'] = $poste;
        }

        // Si aucun champ à mettre à jour, retourner vrai (rien à faire)
        if (empty($updateFields)) {
            return true;
        }

        // Construire la requête SQL
        $query = "UPDATE " . $this->table . " SET " . implode(", ", $updateFields) . " WHERE id = :id_selection";
        $stmt = $this->conn->prepare($query);

        // Lier les paramètres
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        // Exécuter la requête
        $result = $stmt->execute();

        // Si la mise à jour a réussi, mettre à jour l'état de la feuille de match
        if ($result) {
            // D'abord, récupérer l'id_match associé à cette sélection
            $queryGetMatch = "SELECT id_match FROM " . $this->table . " WHERE id = :id_selection";
            $stmtGetMatch = $this->conn->prepare($queryGetMatch);
            $stmtGetMatch->bindParam(':id_selection', $id_selection);
            $stmtGetMatch->execute();
            $matchData = $stmtGetMatch->fetch(PDO::FETCH_ASSOC);

            if ($matchData) {
                $queryUpdateEtat = "UPDATE match_ SET etat_feuille = 'Non validé' WHERE id_match = :id_match";
                $stmtUpdateEtat = $this->conn->prepare($queryUpdateEtat);
                $stmtUpdateEtat->bindParam(':id_match', $matchData['id_match']);
                $stmtUpdateEtat->execute();
            }
        }

        return $result;
    }

    // Ajouter un joueur a une selection pour un match donné
    public function ajouterJoueur($data)
    {
        // Vérifier si le joueur est déjà enregistré pour ce match
        $queryCheck = "SELECT COUNT(*) as count FROM " . $this->table . " 
                       WHERE numero_licence = :numero_licence AND id_match = :id_match";
        $stmtCheck = $this->conn->prepare($queryCheck);
        $stmtCheck->bindParam(':numero_licence', $data['numero_licence']);
        $stmtCheck->bindParam(':id_match', $data['id_match']);
        $stmtCheck->execute();
        $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            throw new Exception("Le joueur avec le numéro de licence " . $data['numero_licence'] . " est déjà sélectionné pour ce match.");
        }

        // Récupérer le nom et le prénom depuis la table `joueur`
        $queryJoueur = "SELECT nom, prenom FROM joueur WHERE numero_licence = :numero_licence";
        $stmtJoueur = $this->conn->prepare($queryJoueur);
        $stmtJoueur->bindParam(':numero_licence', $data['numero_licence']);
        $stmtJoueur->execute();
        $joueur = $stmtJoueur->fetch(PDO::FETCH_ASSOC);

        if (!$joueur) {
            throw new Exception("Joueur introuvable avec le numéro de licence " . $data['numero_licence']);
        }

        // Insérer le joueur dans la table `participer`
        $query = "INSERT INTO " . $this->table . " (numero_licence, nom_joueur, prenom_joueur, id_match, role, poste) 
                  VALUES (:numero_licence, :nom, :prenom, :id_match, :role, :poste)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':numero_licence', $data['numero_licence']);
        $stmt->bindParam(':nom', $joueur['nom']);
        $stmt->bindParam(':prenom', $joueur['prenom']);
        $stmt->bindParam(':id_match', $data['id_match']);
        $stmt->bindParam(':role', $data['role']);
        $stmt->bindParam(':poste', $data['poste']);

        return $stmt->execute();
    }

    // Modifier la selection pour un match
    public function modifierSelection($data)
    {
        // Vérifier si les données sont présentes
        if (empty($data)) {
            throw new Exception("Aucun joueur sélectionné pour ce match.");
        }

        $query = "UPDATE " . $this->table . " 
                  SET role = :role, poste = :poste 
                  WHERE numero_licence = :numero_licence AND id_match = :id_match";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':role', $data['role']);
        $stmt->bindParam(':poste', $data['poste']);
        $stmt->bindParam(':numero_licence', $data['numero_licence']);
        $stmt->bindParam(':id_match', $data['id_match']);

        $result = $stmt->execute();

        // Si la modification a réussi, mettre à jour l'état de la feuille
        if ($result) {
            $queryUpdateEtat = "UPDATE match_ SET etat_feuille = 'Non validé' WHERE id_match = :id_match";
            $stmtUpdateEtat = $this->conn->prepare($queryUpdateEtat);
            $stmtUpdateEtat->bindParam(':id_match', $data['id_match']);
            $stmtUpdateEtat->execute();
        }

        return $result;
    }
    // MAJ de l'évaluation d'un joueur
    public function mettreAJourEvaluation($data)
    {
        // Vérifier si les données sont présentes
        if (empty($data['id_selection']) || !isset($data['evaluation'])) {
            return false;
        }

        // Préparer la requête pour mettre à jour l'évaluation
        $query = "UPDATE " . $this->table . " SET evaluation = :evaluation WHERE id = :id_selection";
        $stmt = $this->conn->prepare($query);

        // Bind des paramètres
        $stmt->bindParam(':evaluation', $data['evaluation'], PDO::PARAM_INT);
        $stmt->bindParam(':id_selection', $data['id_selection'], PDO::PARAM_INT);

        $result = $stmt->execute();

        // Si la mise à jour réussit, mettre à jour l'état de la feuille de match si l'id_match est fourni
        if ($result && isset($data['id_match'])) {
            $queryUpdateEtat = "UPDATE match_ SET etat_feuille = 'Non validé' WHERE id_match = :id_match";
            $stmtUpdateEtat = $this->conn->prepare($queryUpdateEtat);
            $stmtUpdateEtat->bindParam(':id_match', $data['id_match'], PDO::PARAM_INT);
            $stmtUpdateEtat->execute();
        }

        return $result;
    }

    // Récupérer les titulaires d'un match
    public function obtenirTitulairesParMatch($id_match)
    {
        $query = "SELECT j.nom AS nom_joueur, j.prenom AS prenom_joueur, p.role, p.poste, j.numero_licence
                  FROM " . $this->table . " p
                  JOIN joueur j ON p.numero_licence = j.numero_licence
                  WHERE p.id_match = :id_match AND p.role = 'Titulaire'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_match', $id_match);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer les remplaçants d'un match
    public function obtenirRemplacantsParMatch($id_match)
    {
        $query = "SELECT j.nom AS nom_joueur, j.prenom AS prenom_joueur, p.role, p.poste, j.numero_licence
                  FROM " . $this->table . " p
                  JOIN joueur j ON p.numero_licence = j.numero_licence
                  WHERE p.id_match = :id_match AND p.role = 'Remplacant'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_match', $id_match);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return !empty($results) ? $results : [];
    }
    // Récupérer les joueurs non selectionnés d'un match donné
    public function obtenirJoueursNonSelectionnes($id_match)
    {
        $query = "
            SELECT j.numero_licence, j.nom, j.prenom, j.taille, j.poids
            FROM joueur j
            WHERE j.statut = 'Actif'
            AND j.numero_licence NOT IN (
                SELECT p.numero_licence
                FROM participer p
                WHERE p.id_match = :id_match
            )
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_match', $id_match);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Supprimer un joueur d'une selection par son numero de licence et l'id de match
    public function supprimerJoueurParLicenceEtMatch($numero_licence, $id_match)
    {
        // Supprimer le joueur du match
        $query = "DELETE FROM " . $this->table . " 
                  WHERE numero_licence = :numero_licence AND id_match = :id_match";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':numero_licence', $numero_licence);
        $stmt->bindParam(':id_match', $id_match);
        $result = $stmt->execute();

        // Mettre à jour l'état de la feuille si la suppression réussit
        if ($result) {
            $queryUpdateEtat = "UPDATE match_ SET etat_feuille = 'Non validé' WHERE id_match = :id_match";
            $stmtUpdateEtat = $this->conn->prepare($queryUpdateEtat);
            $stmtUpdateEtat->bindParam(':id_match', $id_match);
            $stmtUpdateEtat->execute();
        }

        return $result;
    }

    // MAJ de l'évaluation de l'état de la feuille de match
    public function mettreAJourEtatMatch($id_match, $statut)
    {
        $query = "UPDATE `match_` SET etat_feuille = :statut WHERE id_match = :id_match";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':statut', $statut);
        $stmt->bindParam(':id_match', $id_match);
        return $stmt->execute();
    }

    // Obtenir la moyenne des evaluation d'un joueur
    public function obtenirMoyenneEvaluation($numero_licence)
    {
        $query = "
            SELECT AVG(evaluation) AS moyenne 
            FROM participer 
            WHERE numero_licence = :numero_licence AND evaluation IS NOT NULL
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':numero_licence', $numero_licence);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['moyenne'] ? round($result['moyenne'], 2) : 0.0;
    }

    // Ajouter un joueur a une selection pour un match donné
    public function ajouterJoueurAuMatch($data)
    {
        // Vérifier si le joueur est déjà enregistré pour ce match
        $queryCheck = "SELECT COUNT(*) as count FROM " . $this->table . " 
                       WHERE numero_licence = :numero_licence AND id_match = :id_match";
        $stmtCheck = $this->conn->prepare($queryCheck);
        $stmtCheck->bindParam(':numero_licence', $data['numero_licence']);
        $stmtCheck->bindParam(':id_match', $data['id_match']);
        $stmtCheck->execute();
        $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            return false; // Le joueur est déjà dans la feuille de match
        }

        // Ajouter le joueur à la feuille de match
        $query = "INSERT INTO " . $this->table . " (numero_licence, id_match, role, poste) 
                  VALUES (:numero_licence, :id_match, :role, :poste)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':numero_licence', $data['numero_licence']);
        $stmt->bindParam(':id_match', $data['id_match']);
        $stmt->bindParam(':role', $data['role']);
        $stmt->bindParam(':poste', $data['poste']);

        return $stmt->execute();
    }

    // Supprimer un joueur de la feuille de match
    public function supprimerJoueurDuMatch($id_selection)
    {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id_selection";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_selection', $id_selection);
        $result = $stmt->execute();

        // Mettre à jour l'état de la feuille si la suppression réussit
        if ($result) {
            $queryGetMatch = "SELECT id_match FROM " . $this->table . " WHERE id = :id_selection";
            $stmtGetMatch = $this->conn->prepare($queryGetMatch);
            $stmtGetMatch->bindParam(':id_selection', $id_selection);
            $stmtGetMatch->execute();
            $matchData = $stmtGetMatch->fetch(PDO::FETCH_ASSOC);

            if ($matchData) {
                $queryUpdateEtat = "UPDATE match_ SET etat_feuille = 'Non validé' WHERE id_match = :id_match";
                $stmtUpdateEtat = $this->conn->prepare($queryUpdateEtat);
                $stmtUpdateEtat->bindParam(':id_match', $matchData['id_match']);
                $stmtUpdateEtat->execute();
            }
        }

        return $result;
    }
}
