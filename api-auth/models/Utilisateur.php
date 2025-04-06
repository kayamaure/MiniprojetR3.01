<?php
class Utilisateur {
    private $pdo;

    public function __construct($db) {
        $this->pdo = $db;
    }

    public function creerUtilisateur($nom_utilisateur, $mot_de_passe) {
        $stmt = $this->pdo->prepare("INSERT INTO utilisateur (nom_utilisateur, mot_de_passe) VALUES (:nom, :mdp)");
        return $stmt->execute([
            'nom' => $nom_utilisateur,
            'mdp' => $mot_de_passe
        ]);
    }
    

    public function trouverParNomUtilisateur($nom_utilisateur) {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateur WHERE nom_utilisateur = :username");
        $stmt->execute(['username' => $nom_utilisateur]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function enregistrerJeton($id_utilisateur, $jeton, $expiration) {
        $stmt = $this->pdo->prepare("INSERT INTO tokens (id_utilisateur, token, date_expiration) VALUES (:id_utilisateur, :token, :expiration)");
        return $stmt->execute([
            'id_utilisateur' => $id_utilisateur,
            'token' => $jeton,
            'expiration' => $expiration
        ]);
    }

    public function mettreAJourDerniereConnexion($id_utilisateur) {
        $stmt = $this->pdo->prepare("UPDATE utilisateur SET derniere_connexion = CURRENT_TIMESTAMP WHERE id_utilisateur = :id");
        $stmt->execute(['id' => $id_utilisateur]);
    }
    
    public function supprimerToken($token) {
        $stmt = $this->pdo->prepare("DELETE FROM tokens WHERE token = :token");
        return $stmt->execute(['token' => $token]);
    }

    public function getById($id_utilisateur) {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = :id");
        $stmt->execute(['id' => $id_utilisateur]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function changerMotDePasse($id_utilisateur, $nouveau_hash) {
        $stmt = $this->pdo->prepare("UPDATE utilisateur SET mot_de_passe = :mdp WHERE id_utilisateur = :id");
        return $stmt->execute([
            'mdp' => $nouveau_hash,
            'id' => $id_utilisateur
        ]);
    }
    
    
}
?>
