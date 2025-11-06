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

// Récupérer les infos de l'utilisateur
$stmt = $bdd->prepare("SELECT nom, prenom, mail, tel FROM user WHERE Id_user = ?");
$stmt->execute([$_SESSION['Id_user']]);
$utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

// Inclure le HTML
include '../profil.html';
?>