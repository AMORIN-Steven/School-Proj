<?php
include 'db.php';
$nom = $_POST['nom'];
$sql = "INSERT INTO filieres (nom) VALUES (?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $nom);
$stmt->execute();
echo "Filière ajoutée";
?>