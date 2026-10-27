<?php
// agriculteur/mes_offres.php
session_start();
if (!isset($_SESSION['agriculteur'])) { header('Location: login.php'); exit; }
require_once '../config.php';

$ag = $_SESSION['agriculteur'];
$id_ag = $ag['id_agriculteur'];
$msg = '';

// Suppression d'une offre
if (isset($_GET['delete'])) {
    $id_del = (int)$_GET['delete'];
    // Vérifier qu'aucun postulant et que l'offre appartient à cet agriculteur
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM candidature WHERE id_offre=?");
    $stmt->execute([$id_del]);
    $nb_candidats = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT id_agriculteur FROM offre WHERE id_offre=?");
    $stmt->execute([$id_del]);
    $row = $stmt->fetch();

    if ($row && $row['id_agriculteur'] == $id_ag && $nb_candidats == 0) {
        $pdo->prepare("DELETE FROM offre WHERE id_offre=?")->execute([$id_del]);
        $msg = '<div class="alert alert-success">✅ Offre supprimée avec succès.</div>';
    } else {
        $msg = '<div class="alert alert-danger">❌ Suppression impossible : des candidats ont postulé à cette offre.</div>';
    }
}

// Lister les offres
$stmt = $pdo->prepare("
    SELECT o.*, tf.libelle AS fruit, g.libelle AS gouvernorat,
           (SELECT COUNT(*) FROM candidature WHERE id_offre=o.id_offre) AS nb_candidats,
           (SELECT COUNT(*) FROM candidature WHERE id_offre=o.id_offre AND decision='accepte') AS nb_acceptes
    FROM offre o
    JOIN type_fruit tf ON o.id_type_fruit=tf.id_type_fruit
    JOIN gouvernorat g ON o.id_gouvernorat=g.id_gouvernorat
    WHERE o.id_agriculteur=?
    ORDER BY o.date_debut DESC
");
$stmt->execute([$id_ag]);
$offres = $stmt->fetchAll();

function fruitEmoji($fruit) {
    $map = ['Olives'=>'🫒','Agrumes'=>'🍊','Tomates'=>'🍅','Dattes'=>'🌴','Grenades'=>'🍎','Figues'=>'🌿'];
    return $map[$fruit] ?? '🌾';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mes Offres - Uber-Cueillette</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav>
  <a href="../index.php" class="nav-brand">🌿 Uber<span>-Cueillette</span></a>
  <div class="nav-links">
    <a href="dashboard.php">Tableau de bord</a>
    <a href="mes_offres.php" class="active">Mes offres</a>
    <a href="ajouter_offre.php">+ Nouvelle offre</a>
    <a href="profil.php">Mon profil</a>
    <a href="logout.php" class="btn-logout">Déconnexion</a>
  </div>
</nav>

<div class="hero">
  <h1>📋 Mes Offres de Récolte</h1>
  <p>Gérez toutes vos offres publiées</p>
</div>

<div class="container">
  <?= $msg ?>

  <div class="flex justify-between align-center mb-2 flex-wrap gap-1">
    <div style="color:var(--grey); font-size:.9rem;"><?= count($offres) ?> offre(s) publiée(s)</div>
    <a href="ajouter_offre.php" class="btn btn-primary">🌾 Nouvelle offre</a>
  </div>

  <?php if (empty($offres)): ?>
    <div class="card">
      <div class="empty-state">
        <div class="icon">📭</div>
        <p>Vous n'avez pas encore publié d'offre.</p>
        <a href="ajouter_offre.php" class="btn btn-primary mt-2">Publier ma première offre</a>
      </div>
    </div>
  <?php else: ?>
    <div class="offre-grid">
      <?php foreach ($offres as $o):
        $today = date('Y-m-d');
        $cloturee = ($o['date_limite'] < $today) || ($o['nb_acceptes'] >= $o['nombre_ouvriers']);
        $duree = (strtotime($o['date_fin']) - strtotime($o['date_debut'])) / 86400 + 1;
      ?>
      <div class="offre-card">
        <div class="offre-card-header">
          <span class="emoji"><?= fruitEmoji($o['fruit']) ?></span>
          <div>
            <h3><?= htmlspecialchars($o['fruit']) ?></h3>
            <div style="font-size:.85rem; opacity:.85;">📍 <?= htmlspecialchars($o['gouvernorat']) ?></div>
          </div>
        </div>
        <div class="offre-card-body">
          <div class="offre-price">
            <?= number_format($o['prix_journee'], 2) ?> DT <span>/ jour</span>
          </div>
          <div class="offre-meta">📅 Début : <strong><?= date('d/m/Y', strtotime($o['date_debut'])) ?></strong></div>
          <div class="offre-meta">📅 Fin : <strong><?= date('d/m/Y', strtotime($o['date_fin'])) ?></strong></div>
          <div class="offre-meta">⏱ Durée : <strong><?= $duree ?> jour(s)</strong></div>
          <div class="offre-meta">👷 Ouvriers : <strong><?= $o['nb_acceptes'] ?>/<?= $o['nombre_ouvriers'] ?></strong></div>
          <div class="offre-meta">⏳ Limite candidature : <strong><?= date('d/m/Y', strtotime($o['date_limite'])) ?></strong></div>
          <div class="mt-1">
            <?php if ($cloturee): ?>
              <span class="badge badge-danger">🔒 Clôturée</span>
            <?php else: ?>
              <span class="badge badge-success">✅ Active</span>
            <?php endif; ?>
            <?php if ($o['nb_candidats'] > 0): ?>
              <span class="badge badge-info"><?= $o['nb_candidats'] ?> candidat(s)</span>
            <?php endif; ?>
          </div>
        </div>
        <div class="offre-card-footer flex gap-1 flex-wrap">
          <a href="postulants.php?id=<?= $o['id_offre'] ?>" class="btn btn-sm btn-outline">👥 Postulants</a>
          <?php if ($today > $o['date_fin']): ?>
            <a href="noter_ouvriers.php?id=<?= $o['id_offre'] ?>" class="btn btn-sm btn-warning">⭐ Noter</a>
          <?php endif; ?>
          <?php if ($o['nb_candidats'] == 0): ?>
            <a href="mes_offres.php?delete=<?= $o['id_offre'] ?>"
               class="btn btn-sm btn-danger"
               onclick="return confirm('Supprimer cette offre ?')">🗑 Supprimer</a>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
</body>
</html>
