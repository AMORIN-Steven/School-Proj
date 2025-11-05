<?php
include 'db.php';
$nom = $_POST['nom'];
$sql = "INSERT INTO matieres (nom) VALUES (?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $nom);
$stmt->execute();
echo "Matière ajoutée";
?>