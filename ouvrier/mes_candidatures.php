<?php
// ouvrier/mes_candidatures.php
session_start();
if (!isset($_SESSION['ouvrier'])) { header('Location: login.php'); exit; }
require_once '../config.php';

$ov    = $_SESSION['ouvrier'];
$id_ov = $ov['id_ouvrier'];

$stmt = $pdo->prepare("
    SELECT c.*,
           o.date_debut, o.date_fin, o.prix_journee, o.adresse AS offre_adresse,
           tf.libelle AS fruit,
           g.libelle  AS gouvernorat,
           ag.nom AS ag_nom, ag.prenom AS ag_prenom
    FROM candidature c
    JOIN offre o       ON c.id_offre       = o.id_offre
    JOIN type_fruit tf ON o.id_type_fruit  = tf.id_type_fruit
    JOIN gouvernorat g ON o.id_gouvernorat = g.id_gouvernorat
    JOIN agriculteur ag ON o.id_agriculteur = ag.id_agriculteur
    WHERE c.id_ouvrier = ?
    ORDER BY c.date_candidature DESC
");
$stmt->execute([$id_ov]);
$candidatures = $stmt->fetchAll();

function fruitEmoji($f) {
    $m = ['Olives'=>'🫒','Agrumes'=>'🍊','Tomates'=>'🍅','Dattes'=>'🌴','Grenades'=>'🍎','Figues'=>'🌿'];
    return $m[$f] ?? '🌾';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mes Candidatures - Uber-Cueillette</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav>
  <a href="../index.php" class="nav-brand">🌿 Uber<span>-Cueillette</span></a>
  <div class="nav-links">
    <a href="dashboard.php">Tableau de bord</a>
    <a href="offres.php">Offres</a>
    <a href="mes_candidatures.php" class="active">Mes candidatures</a>
    <a href="mes_chantiers.php">Mes chantiers</a>
    <a href="profil.php">Mon profil</a>
    <a href="logout.php" class="btn-logout">Déconnexion</a>
  </div>
</nav>

<div class="hero">
  <h1>📋 Mes Candidatures</h1>
  <p>Suivez l'état de toutes vos candidatures</p>
</div>

<div class="container">

  <?php if (empty($candidatures)): ?>
    <div class="card">
      <div class="empty-state">
        <div class="icon">📭</div>
        <p>Vous n'avez pas encore postulé à une offre.</p>
        <a href="offres.php" class="btn btn-primary mt-2">Voir les offres disponibles</a>
      </div>
    </div>
  <?php else: ?>

    <!-- Résumé rapide -->
    <?php
    $nb_enc  = count(array_filter($candidatures, fn($c) => $c['decision'] === 'encours'));
    $nb_acc  = count(array_filter($candidatures, fn($c) => $c['decision'] === 'accepte'));
    $nb_ref  = count(array_filter($candidatures, fn($c) => $c['decision'] === 'refuse'));
    ?>
    <div style="display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1.5rem;">
      <span class="badge badge-warning" style="font-size:.95rem; padding:.5rem 1rem;">
        ⏳ En attente : <?= $nb_enc ?>
      </span>
      <span class="badge badge-success" style="font-size:.95rem; padding:.5rem 1rem;">
        ✅ Acceptées : <?= $nb_acc ?>
      </span>
      <span class="badge badge-danger" style="font-size:.95rem; padding:.5rem 1rem;">
        ❌ Refusées : <?= $nb_ref ?>
      </span>
    </div>

    <div class="table-wrap card" style="padding:0;">
      <table>
        <thead>
          <tr>
            <th>Offre</th>
            <th>Gouvernorat</th>
            <th>Période</th>
            <th>Prix/jour</th>
            <th>Agriculteur</th>
            <th>Date candidature</th>
            <th>Décision</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($candidatures as $c): ?>
          <tr>
            <td>
              <?= fruitEmoji($c['fruit']) ?>
              <?= htmlspecialchars($c['fruit']) ?>
            </td>
            <td>📍 <?= htmlspecialchars($c['gouvernorat']) ?></td>
            <td>
              <?= date('d/m/Y', strtotime($c['date_debut'])) ?>
              → <?= date('d/m/Y', strtotime($c['date_fin'])) ?>
            </td>
            <td><?= number_format($c['prix_journee'], 2) ?> DT</td>
            <td><?= htmlspecialchars($c['ag_prenom'].' '.$c['ag_nom']) ?></td>
            <td><?= date('d/m/Y H:i', strtotime($c['date_candidature'])) ?></td>
            <td>
              <?php if ($c['decision'] === 'encours'): ?>
                <span class="badge badge-warning">⏳ En attente</span>
              <?php elseif ($c['decision'] === 'accepte'): ?>
                <span class="badge badge-success">✅ Acceptée</span>
              <?php else: ?>
                <span class="badge badge-danger">❌ Refusée</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

</div>
</body>
</html>
