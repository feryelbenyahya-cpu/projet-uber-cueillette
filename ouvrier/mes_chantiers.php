<?php
// ouvrier/mes_chantiers.php
session_start();
if (!isset($_SESSION['ouvrier'])) { header('Location: login.php'); exit; }
require_once '../config.php';

$ov    = $_SESSION['ouvrier'];
$id_ov = $ov['id_ouvrier'];

// Chantiers clôturés : date_fin passée ET candidature acceptée
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
      AND c.decision   = 'accepte'
      AND o.date_fin   < CURDATE()
    ORDER BY o.date_fin DESC
");
$stmt->execute([$id_ov]);
$chantiers = $stmt->fetchAll();

// Calculs globaux
$total_gains = array_sum(array_column($chantiers, 'remuneration'));
$notes       = array_filter(array_column($chantiers, 'note'), fn($n) => $n !== null);
$moy_note    = count($notes) ? array_sum($notes) / count($notes) : null;

function fruitEmoji($f) {
    $m = ['Olives'=>'🫒','Agrumes'=>'🍊','Tomates'=>'🍅','Dattes'=>'🌴','Grenades'=>'🍎','Figues'=>'🌿'];
    return $m[$f] ?? '🌾';
}

function renderStars($note) {
    if ($note === null) return '<span class="text-muted">Non noté</span>';
    $filled  = round($note / 2);   // sur 5 étoiles (note /10)
    $stars   = str_repeat('★', $filled) . str_repeat('☆', 5 - $filled);
    return "<span class='stars'>$stars</span> <strong>$note/10</strong>";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mes Chantiers - Uber-Cueillette</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav>
  <a href="../index.php" class="nav-brand">🌿 Uber<span>-Cueillette</span></a>
  <div class="nav-links">
    <a href="dashboard.php">Tableau de bord</a>
    <a href="offres.php">Offres</a>
    <a href="mes_candidatures.php">Mes candidatures</a>
    <a href="mes_chantiers.php" class="active">Mes chantiers</a>
    <a href="profil.php">Mon profil</a>
    <a href="logout.php" class="btn-logout">Déconnexion</a>
  </div>
</nav>

<div class="hero">
  <h1>🏗️ Mes Chantiers Clôturés</h1>
  <p>Consultez vos anciens chantiers, vos notes et vos gains</p>
</div>

<div class="container">

  <!-- Bilan global -->
  <?php if (!empty($chantiers)): ?>
  <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:1.5rem; margin-bottom:2rem;">
    <div class="card text-center" style="border-top:4px solid var(--olive);">
      <div style="font-size:2rem; font-weight:700; color:var(--olive);"><?= count($chantiers) ?></div>
      <p style="font-weight:600;">Chantiers réalisés</p>
    </div>
    <div class="card text-center" style="border-top:4px solid var(--earth);">
      <div style="font-size:2rem; font-weight:700; color:var(--earth);">
        <?= number_format($total_gains, 2) ?> DT
      </div>
      <p style="font-weight:600;">Gains totaux</p>
    </div>
    <div class="card text-center" style="border-top:4px solid #f59e0b;">
      <div style="font-size:2rem; font-weight:700; color:#f59e0b;">
        <?= $moy_note ? number_format($moy_note, 1) : '—' ?>
      </div>
      <p style="font-weight:600;">Note moyenne /10</p>
    </div>
  </div>
  <?php endif; ?>

  <?php if (empty($chantiers)): ?>
    <div class="card">
      <div class="empty-state">
        <div class="icon">🏗️</div>
        <p>Vous n'avez pas encore participé à un chantier clôturé.</p>
        <a href="offres.php" class="btn btn-primary mt-2">Trouver un chantier</a>
      </div>
    </div>
  <?php else: ?>
    <?php foreach ($chantiers as $ch):
      $duree    = (strtotime($ch['date_fin']) - strtotime($ch['date_debut'])) / 86400 + 1;
    ?>
    <div class="card" style="border-left: 5px solid var(--olive-light);">
      <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1rem;">

        <!-- Infos chantier -->
        <div style="flex:1; min-width:220px;">
          <h3 style="font-family:'Playfair Display',serif; color:var(--olive-dark); margin-bottom:.5rem;">
            <?= fruitEmoji($ch['fruit']) ?> <?= htmlspecialchars($ch['fruit']) ?>
            <span style="font-size:.9rem; color:var(--grey);">— <?= htmlspecialchars($ch['gouvernorat']) ?></span>
          </h3>
          <div class="offre-meta">📅 <?= date('d/m/Y', strtotime($ch['date_debut'])) ?> → <?= date('d/m/Y', strtotime($ch['date_fin'])) ?> (<?= (int)$duree ?> jours)</div>
          <div class="offre-meta">📍 <?= htmlspecialchars($ch['offre_adresse'] ?? '—') ?></div>
          <div class="offre-meta">🧑‍🌾 Agriculteur : <strong><?= htmlspecialchars($ch['ag_prenom'].' '.$ch['ag_nom']) ?></strong></div>
          <div class="offre-meta">💰 Prix/jour : <strong><?= number_format($ch['prix_journee'], 2) ?> DT</strong></div>
        </div>

        <!-- Note & gains -->
        <div style="text-align:right; min-width:200px;">
          <div style="margin-bottom:.8rem;">
            <div style="font-size:.8rem; color:var(--grey); margin-bottom:.3rem;">Évaluation de l'agriculteur</div>
            <?= renderStars($ch['note']) ?>
          </div>
          <?php if ($ch['commentaire']): ?>
            <div style="font-size:.85rem; font-style:italic; color:var(--grey); margin-bottom:.8rem;">
              💬 "<?= htmlspecialchars($ch['commentaire']) ?>"
            </div>
          <?php endif; ?>
          <div style="background:var(--earth-light); border-radius:8px; padding:.8rem 1.2rem; display:inline-block;">
            <div style="font-size:.8rem; color:var(--grey);">Rémunération perçue</div>
            <div style="font-size:1.5rem; font-weight:700; color:var(--earth);">
              <?= $ch['remuneration'] !== null ? number_format($ch['remuneration'], 2).' DT' : '— DT' ?>
            </div>
          </div>
        </div>

      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>

</div>
</body>
</html>
