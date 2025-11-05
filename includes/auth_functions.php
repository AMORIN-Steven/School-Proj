<?php
// Démarre la session seulement si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function connecterUtilisateur($mail, $motdepasse, $bdd) {
    // Rechercher l'utilisateur
    $req = $bdd->prepare("SELECT * FROM user WHERE mail = ? AND motdepasse = ?");
    $req->execute([$mail, $motdepasse]);
    $user = $req->fetch();

    if ($user) {
        // Créer la session
        $_SESSION['user_id'] = $user['Id_user'];
        $_SESSION['user_nom'] = $user['nom'];
        $_SESSION['user_prenom'] = $user['prenom'];
        $_SESSION['user_status'] = $user['status'];
        $_SESSION['user_mail'] = $user['mail'];
        $_SESSION['logged_in'] = true;
        
        return true;
    }
    return false;
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
?>