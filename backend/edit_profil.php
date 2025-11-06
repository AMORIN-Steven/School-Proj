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

// Récupérer les infos actuelles
$stmt = $bdd->prepare("SELECT nom, prenom, mail, tel FROM user WHERE Id_user = ?");
$stmt->execute([$_SESSION['Id_user']]);
$utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['name'] ?? '';
    $prenom = $_POST['prénom'] ?? '';
    $email = $_POST['email'] ?? '';
    $tel = $_POST['tel'] ?? '';

    // Mettre à jour les informations
    $stmt = $bdd->prepare("UPDATE user SET nom = ?, prenom = ?, mail = ?, tel = ? WHERE Id_user = ?");
    if ($stmt->execute([$nom, $prenom, $email, $tel, $_SESSION['Id_user']])) {
        $message = "✅ Profil modifié avec succès";
        // Recharger les données
        $utilisateur = [
            'nom' => $nom,
            'prenom' => $prenom,
            'mail' => $email,
            'tel' => $tel
        ];
    } else {
        $message = "❌ Erreur lors de la modification";
    }
}

// Inclure le HTML
include '../edit_profil.html';
?>