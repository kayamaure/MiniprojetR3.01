<?php
require_once __DIR__ . '/../config/database.php';
// Modèle Joueur

class Joueur
{
    private $conn;
    private $table_name = "joueur";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Obtenir tous les joueurs
    public function obtenirTousLesJoueurs()
    {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ajouter un joueur
    public function ajouterJoueur($data)
    {
        $query = "INSERT INTO " . $this->table_name . " (numero_licence, nom, prenom, date_naissance, taille, poids, statut) 
                  VALUES (:numero_licence, :nom, :prenom, :date_naissance, :taille, :poids, :statut)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":numero_licence", $data['numero_licence']);
        $stmt->bindParam(":nom", $data['nom']);
        $stmt->bindParam(":prenom", $data['prenom']);
        $stmt->bindParam(":date_naissance", $data['date_naissance']);
        $stmt->bindParam(":taille", $data['taille']);
        $stmt->bindParam(":poids", $data['poids']);
        $stmt->bindParam(":statut", $data['statut']);

        return $stmt->execute();
    }

    // Mettre à jour un joueur
    public function mettreAJourJoueur($data)
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET nom = :nom, prenom = :prenom, date_naissance = :date_naissance, taille = :taille, poids = :poids, statut = :statut
                  WHERE numero_licence = :numero_licence";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":numero_licence", $data['numero_licence']);
        $stmt->bindParam(":nom", $data['nom']);
        $stmt->bindParam(":prenom", $data['prenom']);
        $stmt->bindParam(":date_naissance", $data['date_naissance']);
        $stmt->bindParam(":taille", $data['taille']);
        $stmt->bindParam(":poids", $data['poids']);
        $stmt->bindParam(":statut", $data['statut']);

        return $stmt->execute();
    }

    // Supprimer un joueur
    public function supprimerJoueur($numero_licence)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE numero_licence = :numero_licence";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":numero_licence", $numero_licence);

        return $stmt->execute();
    }

    // Obtenir un joueur par son numéro de licence
    public function obtenirJoueur($numero_licence)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE numero_licence = :numero_licence";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":numero_licence", $numero_licence);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtenir les joueurs actifs
    public function obtenirJoueursActifs()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE statut = 'Actif'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
