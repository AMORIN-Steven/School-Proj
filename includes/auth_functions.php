<?php
// Démarre la session seulement si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function connecterUtilisateur($mail, $motdepasse, $bdd) {
    try {
        // Rechercher l'utilisateur par email seulement
        $req = $bdd->prepare("SELECT * FROM user WHERE mail = ?");
        $req->execute([$mail]);
        $user = $req->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Vérifier le mot de passe EN CLAIR (pour la démo)
            if ($motdepasse === $user['motdepasse']) {
                // Créer la session
                $_SESSION['user_id'] = $user['Id_user'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_status'] = $user['status'];
                $_SESSION['user_mail'] = $user['mail'];
                $_SESSION['logged_in'] = true;
                
                return true;
            }
        }
        return false;
    } catch (PDOException $e) {
        error_log("Erreur connexion: " . $e->getMessage());
        return false;
    }
}

function estConnecte() {
    return isset($_SESSION['user_id']) && $_SESSION['logged_in'] === true;
}

function deconnecterUtilisateur() {
    // Détruire la session
    session_destroy();
    
    // Rediriger vers la page de connexion
    header('Location: connexion.php');
    exit();
}

function verifierConnexion() {
    if (!estConnecte()) {
        header('Location: connexion.php');
        exit();
    }
}

function getUtilisateurConnecte() {
    if (estConnecte()) {
        return [
            'id' => $_SESSION['user_id'],
            'nom' => $_SESSION['user_nom'],
            'prenom' => $_SESSION['user_prenom'],
            'status' => $_SESSION['user_status'],
            'mail' => $_SESSION['user_mail']
        ];
    }
    return null;
}
?>