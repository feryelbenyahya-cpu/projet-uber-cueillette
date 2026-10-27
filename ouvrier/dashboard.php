<?php
// ouvrier/dashboard.php
session_start();
if (!isset($_SESSION['ouvrier'])) { header('Location: login.php'); exit; }
require_once '../config.php';

$ov = $_SESSION['ouvrier'];
$id_ov = $ov['id_ouvrier'];

// Stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM candidature WHERE id_ouvrier=?");
$stmt->execute([$id_ov]);
$nb_candidatures = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM candidature WHERE id_ouvrier=? AND decision='accepte'");
$stmt->execute([$id_ov]);
$nb_acceptes = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT AVG(note) FROM candidature WHERE id_ouvrier=? AND note IS NOT NULL");
$stmt->execute([$id_ov]);
$moy_note = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT SUM(remuneration) FROM candidature WHERE id_ouvrier=? AND remuneration IS NOT NULL");
$stmt->execute([$id_ov]);
$total_gains = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Ouvrier - Uber-Cueillette</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav>
  <a href="../index.php" class="nav-brand">🌿 Uber<span>-Cueillette</span></a>
  <div class="nav-links">
    <a href="dashboard.php" class="active">Tableau de bord</a>
    <a href="offres.php">Offres disponibles</a>
    <a href="mes_candidatures.php">Mes candidatures</a>
    <a href="mes_chantiers.php">Mes chantiers</a>
    <a href="profil.php">Mon profil</a>
    <a href="logout.php" class="btn-logout">Déconnexion</a>
  </div>
</nav>

<div class="hero">
  <h1>Bonjour, <?= htmlspecialchars($ov['prenom']) ?> 👋</h1>
  <p>Consultez les offres et suivez vos chantiers</p>
</div>

<div class="container">

  <!-- Stats -->
  <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:1.5rem; margin-bottom:2rem;">
    <div class="card text-center" style="border-top:4px solid var(--olive);">
      <div style="font-size:2.5rem; font-weight:700; color:var(--olive);"><?= $nb_candidatures ?></div>
      <p style="font-weight:600;">Candidatures</p>
      <p class="text-muted" style="font-size:.85rem;">Total soumises</p>
    </div>
    <div class="card text-center" style="border-top:4px solid var(--success);">
      <div style="font-size:2.5rem; font-weight:700; color:var(--success);"><?= $nb_acceptes ?></div>
      <p style="font-weight:600;">Acceptées</p>
      <p class="text-muted" style="font-size:.85rem;">Chantiers réalisés</p>
    </div>
    <div class="card text-center" style="border-top:4px solid #f59e0b;">
      <div style="font-size:2.5rem; font-weight:700; color:#f59e0b;">
        <?= $moy_note ? number_format($moy_note, 1) : '—' ?>
      </div>
      <p style="font-weight:600;">Note moyenne</p>
      <p class="text-muted" style="font-size:.85rem;">Sur 10</p>
    </div>
    <div class="card text-center" style="border-top:4px solid var(--earth);">
      <div style="font-size:2.5rem; font-weight:700; color:var(--earth);">
        <?= $total_gains ? number_format($total_gains, 0) : '0' ?> DT
      </div>
      <p style="font-weight:600;">Gains totaux</p>
      <p class="text-muted" style="font-size:.85rem;">Rémunérations perçues</p>
    </div>
  </div>

  <!-- Actions rapides -->
  <div class="card">
    <div class="card-title">⚡ Actions rapides</div>
    <div class="flex gap-2 flex-wrap">
      <a href="offres.php" class="btn btn-primary">🌾 Voir les offres</a>
      <a href="mes_candidatures.php" class="btn btn-outline">📋 Mes candidatures</a>
      <a href="mes_chantiers.php" class="btn btn-outline">🏗️ Mes chantiers</a>
      <a href="profil.php" class="btn btn-outline">👤 Mon profil</a>
    </div>
  </div>

  <!-- Profil ouvrier -->
  <div class="card mt-2">
    <div class="card-title">👷 Mon profil</div>
    <div class="profile-header">
      <?php if ($ov['photo']): ?>
        <img src="get_photo.php?id=<?= $id_ov ?>" class="profile-avatar">
      <?php else: ?>
        <div class="profile-avatar-placeholder"><img src="ouvrier.jpg" alt="" width="100%" height="100%"></div>
      <?php endif; ?>
      <div>
        <h3 style="font-family:'Playfair Display',serif;">
          <?= htmlspecialchars($ov['prenom'].' '.$ov['nom']) ?>
        </h3>
        <p class="text-muted">@<?= htmlspecialchars($ov['pseudo']) ?></p>
        <?php if ($ov['description']): ?>
          <p class="mt-1" style="font-size:.9rem;"><?= htmlspecialchars($ov['description']) ?></p>
        <?php endif; ?>
        <?php if ($moy_note): ?>
          <div class="stars mt-1">★★★★★ <?= number_format($moy_note, 1) ?>/10</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>
</body>
</html>
