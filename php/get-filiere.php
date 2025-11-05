<?php
include 'db.php';
$sql = "SELECT nom FROM filieres";
$result = $conn->query($sql);
$filieres = [];
while ($row = $result->fetch_assoc()) {
  $filieres[] = $row['nom'];
}
echo json_encode($filieres);
?>