<?php
include 'db.php';
$sql = "SELECT nom FROM matieres";
$result = $conn->query($sql);
$matieres = [];
while ($row = $result->fetch_assoc()) {
  $matieres[] = $row['nom'];
}
echo json_encode($matieres);
?>