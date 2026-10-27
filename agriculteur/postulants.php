<?php
// agriculteur/postulants.php
session_start();
if (!isset($_SESSION['agriculteur'])) { header('Location: login.php'); exit; }
require_once '../config.php';

$ag = $_SESSION['agriculteur'];
$id_ag = $ag['id_agriculteur'];
$id_offre = (int)($_GET['id'] ?? 0);
$msg = '';

// Vérifier que l'offre appartient à cet agriculteur
$stmt = $pdo->prepare("
    SELECT o.*, tf.libelle AS fruit, g.libelle AS gouvernorat
    FROM offre o
    JOIN type_fruit tf ON o.id_type_fruit=tf.id_type_fruit
    JOIN gouvernorat g ON o.id_gouvernorat=g.id_gouvernorat
    WHERE o.id_offre=? AND o.id_agriculteur=?
");
$stmt->execute([$id_offre, $id_ag]);
$offre = $stmt->fetch();

if (!$offre) { header('Location: mes_offres.php'); exit; }

// Action : accepter/refuser
if (isset($_GET['action']) && isset($_GET['id_c'])) {
    $action = $_GET['action'];
    $id_c   = (int)$_GET['id_c'];

    if ($action === 'accepter') {
        // Vérifier quota
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM candidature WHERE id_offre=? AND decision='accepte'");
        $stmt->execute([$id_offre]);
        $nb_acc = $stmt->fetchColumn();

        if ($nb_acc >= $offre['nombre_ouvriers']) {
            $msg = '<div class="alert alert-danger">❌ Quota d\'ouvriers atteint. Offre clôturée.</div>';
        } else {
            $pdo->prepare("UPDATE candidature SET decision='accepte' WHERE id_candidature=?")
                ->execute([$id_c]);
            $msg = '<div class="alert alert-success">✅ Candidature acceptée.</div>';
        }
    } elseif ($action === 'refuser') {
        $pdo->prepare("UPDATE candidature SET decision='refuse' WHERE id_candidature=?")
            ->execute([$id_c]);
        $msg = '<div class="alert alert-success">✅ Candidature refusée.</div>';
    }
}

// Lister postulants
$stmt = $pdo->prepare("
    SELECT c.*, ov.nom, ov.prenom, ov.photo, ov.description,
           (SELECT AVG(note) FROM candidature WHERE id_ouvrier=ov.id_ouvrier AND note IS NOT NULL) AS moy_note,
           (SELECT GROUP_CONCAT(commentaire SEPARATOR ' | ') FROM candidature WHERE id_ouvrier=ov.id_ouvrier AND commentaire IS NOT NULL) AS all_comments
    FROM candidature c
    JOIN ouvrier ov ON c.id_ouvrier=ov.id_ouvrier
    WHERE c.id_offre=?
    ORDER BY c.date_candidature DESC
");
$stmt->execute([$id_offre]);
$postulants = $stmt->fetchAll();

// Compte acceptés
$stmt = $pdo->prepare("SELECT COUNT(*) FROM candidature WHERE id_offre=? AND decision='accepte'");
$stmt->execute([$id_offre]);
$nb_acceptes = $stmt->fetchColumn();
$quota_atteint = ($nb_acceptes >= $offre['nombre_ouvriers']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Postulants - Uber-Cueillette</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav>
  <a href="../index.php" class="nav-brand">🌿 Uber<span>-Cueillette</span></a>
  <div class="nav-links">
    <a href="dashboard.php">Tableau de bord</a>
    <a href="mes_offres.php">Mes offres</a>
    <a href="ajouter_offre.php">+ Nouvelle offre</a>
    <a href="profil.php">Mon profil</a>
    <a href="logout.php" class="btn-logout">Déconnexion</a>
  </div>
</nav>

<div class="hero">
  <h1>👥 Postulants</h1>
  <p>Offre : <?= htmlspecialchars($offre['fruit']) ?> — <?= htmlspecialchars($offre['gouvernorat']) ?></p>
</div>

<div class="container">
  <?= $msg ?>

  <div class="card mb-2">
    <div class="flex justify-between align-center flex-wrap gap-1">
      <div>
        <div class="offre-meta">📅 Du <?= date('d/m/Y', strtotime($offre['date_debut'])) ?>
          au <?= date('d/m/Y', strtotime($offre['date_fin'])) ?></div>
        <div class="offre-meta">💰 <?= number_format($offre['prix_journee'],2) ?> DT/jour</div>
      </div>
      <div>
        <span class="badge badge-info">Acceptés : <?= $nb_acceptes ?>/<?= $offre['nombre_ouvriers'] ?></span>
        <?php if ($quota_atteint): ?>
          <span class="badge badge-danger ml-1">🔒 Quota atteint</span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if (empty($postulants)): ?>
    <div class="card"><div class="empty-state">
      <div class="icon">👻</div>
      <p>Aucun postulant pour cette offre.</p>
    </div></div>
  <?php else: ?>
    <?php foreach ($postulants as $p): ?>
    <div class="card" style="display:flex; gap:1.5rem; align-items:flex-start; flex-wrap:wrap;">
      <!-- Photo -->
      <div style="flex-shrink:0;">
        <?php if ($p['photo']): ?>
          <img src="get_photo.php?id=<?= $p['id_ouvrier'] ?>"
               style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid var(--olive-light);">
        <?php else: ?>
          <div class="profile-avatar-placeholder" style="width:80px;height:80px;">👷</div>
        <?php endif; ?>
      </div>
      <!-- Infos -->
      <div style="flex:1;">
        <h3 style="font-family:'Playfair Display',serif; color:var(--olive-dark);">
          <?= htmlspecialchars($p['prenom'].' '.$p['nom']) ?>
        </h3>
        <div class="offre-meta mt-1">
          <?php if ($p['moy_note'] !== null): ?>
            <span class="stars">★★★★★</span>
            Note moyenne : <strong><?= number_format($p['moy_note'],1) ?>/10</strong>
          <?php else: ?>
            <span class="text-muted">Aucune évaluation antérieure</span>
          <?php endif; ?>
        </div>
        <?php if ($p['description']): ?>
          <p class="offre-meta mt-1"><?= htmlspecialchars($p['description']) ?></p>
        <?php endif; ?>
        <?php if ($p['all_comments']): ?>
          <p class="offre-meta mt-1" style="font-style:italic;">
            💬 <?= htmlspecialchars(substr($p['all_comments'], 0, 150)) ?>...
          </p>
        <?php endif; ?>
        <div class="mt-1">
          <span class="badge <?=
            $p['decision']==='accepte' ? 'badge-success' :
            ($p['decision']==='refuse' ? 'badge-danger' : 'badge-warning')
          ?>">
            <?= $p['decision'] === 'encours' ? '⏳ En attente' :
               ($p['decision'] === 'accepte' ? '✅ Accepté' : '❌ Refusé') ?>
          </span>
        </div>
      </div>
      <!-- Actions -->
      <div style="flex-shrink:0; display:flex; flex-direction:column; gap:.5rem;">
        <?php if ($p['decision'] === 'encours'): ?>
          <?php if (!$quota_atteint): ?>
            <a href="?id=<?= $id_offre ?>&action=accepter&id_c=<?= $p['id_candidature'] ?>"
               class="btn btn-success btn-sm"
               onclick="return confirm('Accepter ce candidat ?')">✅ Accepter</a>
          <?php endif; ?>
          <a href="?id=<?= $id_offre ?>&action=refuser&id_c=<?= $p['id_candidature'] ?>"
             class="btn btn-danger btn-sm"
             onclick="return confirm('Refuser ce candidat ?')">❌ Refuser</a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <div class="mt-2">
    <a href="mes_offres.php" class="btn btn-outline">← Retour à mes offres</a>
  </div>
</div>
</body>
</html>
