<?php
class Utilisateur {
    private $pdo;

    public function __construct($db) {
        $this->pdo = $db;
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
}
?>
