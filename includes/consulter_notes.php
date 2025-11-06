<?php
include('../includes/bdd.php');

$id_etud = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_etud > 0) {
    // ✅ Requête SQL corrigée selon ta base
    $sql = "SELECT e.nom AS nom_etud, e.prenom AS prenom_etud, m.nom AS matiere, n.cc, n.exam
            FROM note n
            JOIN etud e ON n.Id_etud = e.Id_etud
            JOIN matiere m ON n.Id_mat = m.Id_mat
            WHERE n.Id_etud = ?";
    
    $stmt = $bdd->prepare($sql);
    $stmt->execute([$id_etud]);
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $notes = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Consultation des notes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <h2 class="text-center mb-4">Consultation des Notes</h2>

    <?php if (!empty($notes)): ?>
      <h5 class="mb-3 text-center">
        Élève : <?= htmlspecialchars($notes[0]['prenom_etud'] . ' ' . $notes[0]['nom_etud']) ?>
      </h5>

      <table class="table table-bordered text-center">
        <thead class="table-dark">
          <tr>
            <th>Matière</th>
            <th>Contrôle Continu (CC)</th>
            <th>Examen</th>
            <th>Moyenne</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($notes as $n): 
            $moy = ($n['cc'] + $n['exam']) / 2;
          ?>
          <tr>
            <td><?= htmlspecialchars($n['matiere']) ?></td>
            <td><?= $n['cc'] ?></td>
            <td><?= $n['exam'] ?></td>
            <td><?= number_format($moy, 2) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div class="alert alert-warning text-center">
        Aucune note trouvée pour cet élève.
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
