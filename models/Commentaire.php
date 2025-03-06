<?php
// Modèle Commentaire
class Commentaire {
    private $conn;
    private $table = "commentaire";  

    public function __construct($db) {
        $this->conn = $db;
    }

    // Ajouter un commentaire
    public function ajouterCommentaire($data) {
        $query = "INSERT INTO " . $this->table . " (sujet_commentaire, texte_commentaire, numero_licence) 
                  VALUES (:sujet_commentaire, :texte_commentaire, :numero_licence)";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":sujet_commentaire", $data['sujet_commentaire']);
        $stmt->bindParam(":texte_commentaire", $data['texte_commentaire']);
        $stmt->bindParam(":numero_licence", $data['numero_licence']);

        return $stmt->execute();
    }

    // Récupérer commentaire pour un joueur donné avec son numero de licence
    public function obtenirCommentairesParJoueur($numero_licence) {
        $query = "SELECT * FROM " . $this->table . " WHERE numero_licence = :numero_licence";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':numero_licence', $numero_licence);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtenir le dérnier commentaire d'un joueur donné
    public function obtenirDernierCommentaireParJoueur($numero_licence) {
        $query = "SELECT texte_commentaire 
                  FROM " . $this->table . " 
                  WHERE numero_licence = :numero_licence 
                  ORDER BY id_commentaire DESC 
                  LIMIT 1";
    
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':numero_licence', $numero_licence);
        $stmt->execute();
    
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['texte_commentaire'] ?? 'Pas de commentaire'; 
    }
    
    
}
?>
