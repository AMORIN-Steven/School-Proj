<?php
include 'db.php';
$etudiant = $_POST['etudiant'];
$filiere = $_POST['filiere'];
$matiere = $_POST['matiere'];
$note = $_POST['note'];
$sql = "UPDATE notes SET note = ? WHERE etudiant = ? AND filiere = ? AND matiere = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("dsss", $note, $etudiant, $filiere, $matiere);
$stmt->execute();
echo "Note modifiée";
?>