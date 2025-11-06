<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['Id_user'])) {
    header('Location: Connexion.php');
    exit();
}

// Connexion à la base de données
$host = 'localhost';
$dbname = 'ecole_benin';
$user = 'root';
$pass = '';

try {
    $bdd = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ancien_mdp = $_POST['password'] ?? '';
    $nouveau_mdp = $_POST['new_password'] ?? '';

    // Récupérer le mot de passe actuel
    $stmt = $bdd->prepare("SELECT motdepasse FROM user WHERE Id_user = ?");
    $stmt->execute([$_SESSION['Id_user']]);
    $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifier l'ancien mot de passe
    if ($ancien_mdp === $utilisateur['motdepasse']) {
        // Mettre à jour avec le nouveau mot de passe
        $stmt = $bdd->prepare("UPDATE user SET motdepasse = ? WHERE Id_user = ?");
        $stmt->execute([$nouveau_mdp, $_SESSION['Id_user']]);
        $message = "✅ Mot de passe modifié avec succès";
    } else {
        $message = "❌ L'ancien mot de passe est incorrect";
    }
}

// Inclure le HTML
include '../edit_password.html';
?>