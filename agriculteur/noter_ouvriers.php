<?php
// agriculteur/noter_ouvriers.php
session_start();
if (!isset($_SESSION['agriculteur'])) { header('Location: login.php'); exit; }
require_once '../config.php';

$ag = $_SESSION['agriculteur'];
$id_ag = $ag['id_agriculteur'];
$id_offre = (int)($_GET['id'] ?? 0);
$msg = '';

// Vérifier offre
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

// Traitement notation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = false;
    foreach ($_POST['candidatures'] as $id_c => $data) {
        $note = isset($data['note']) && $data['note'] !== '' ? (float)$data['note'] : null;
        $commentaire = trim($data['commentaire'] ?? '');
        $remuneration = isset($data['remuneration']) ? (float)$data['remuneration'] : null;

        if ($note !== null && ($note < 0 || $note > 10)) {
            $msg = '<div class="alert alert-danger">La note doit être entre 0 et 10.</div>';
            $errors = true;
            break;
        }

        $stmt = $pdo->prepare("
            UPDATE candidature SET note=?, commentaire=?, remuneration=?
            WHERE id_candidature=? AND id_offre=?
        ");
        $stmt->execute([$note, $commentaire ?: null, $remuneration, (int)$id_c, $id_offre]);
    }
    if (!$errors) {
        $msg = '<div class="alert alert-success">✅ Notes et rémunérations enregistrées avec succès.</div>';
    }
}

// Ouvriers acceptés
$stmt = $pdo->prepare("
    SELECT c.*, ov.nom, ov.prenom, ov.photo
    FROM candidature c
    JOIN ouvrier ov ON c.id_ouvrier=ov.id_ouvrier
    WHERE c.id_offre=? AND c.decision='accepte'
    ORDER BY ov.nom
");
$stmt->execute([$id_offre]);
$ouvriers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Noter les ouvriers - Uber-Cueillette</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav>
  <a href="../index.php" class="nav-brand">🌿 Uber<span>-Cueillette</span></a>
  <div class="nav-links">
    <a href="dashboard.php">Tableau de bord</a>
    <a href="mes_offres.php">Mes offres</a>
    <a href="logout.php" class="btn-logout">Déconnexion</a>
  </div>
</nav>

<div class="hero">
  <h1>⭐ Évaluation des ouvriers</h1>
  <p>Offre : <?= htmlspecialchars($offre['fruit']) ?> — <?= htmlspecialchars($offre['gouvernorat']) ?></p>
</div>

<div class="container">
  <?= $msg ?>

  <div class="alert alert-info">
    📅 Chantier terminé le <?= date('d/m/Y', strtotime($offre['date_fin'])) ?>.
    Évaluez chaque ouvrier ayant participé.
  </div>

  <?php if (empty($ouvriers)): ?>
    <div class="card"><div class="empty-state">
      <div class="icon">👻</div>
      <p>Aucun ouvrier accepté pour ce chantier.</p>
    </div></div>
  <?php else: ?>
    <form method="POST">
      <?php foreach ($ouvriers as $ov): ?>
      <div class="card" style="display:flex; gap:1.5rem; align-items:flex-start; flex-wrap:wrap;">
        <!-- Photo -->
        <div style="flex-shrink:0;">
          <?php if ($ov['photo']): ?>
            <img src="get_photo.php?id=<?= $ov['id_ouvrier'] ?>"
                 style="width:70px;height:70px;border-radius:50%;object-fit:cover;border:3px solid var(--olive-light);">
          <?php else: ?>
            <div class="profile-avatar-placeholder" style="width:70px;height:70px;">👷</div>
          <?php endif; ?>
        </div>
        <!-- Infos + notation -->
        <div style="flex:1;">
          <h3 style="font-family:'Playfair Display',serif; color:var(--olive-dark); margin-bottom:.8rem;">
            <?= htmlspecialchars($ov['prenom'].' '.$ov['nom']) ?>
          </h3>
          <div style="display:grid; grid-template-columns:1fr 1fr 2fr; gap:1rem; flex-wrap:wrap;">
            <div class="form-group" style="margin:0;">
              <label>Note (0-10) <span class="required">*</span></label>
              <input type="number" class="form-control"
                     name="candidatures[<?= $ov['id_candidature'] ?>][note]"
                     min="0" max="10" step="0.25"
                     value="<?= $ov['note'] ?? '' ?>"
                     placeholder="Ex: 8">
            </div>
            <div class="form-group" style="margin:0;">
              <label>Rémunération (DT)</label>
              <input type="number" class="form-control"
                     name="candidatures[<?= $ov['id_candidature'] ?>][remuneration]"
                     min="0" step="0.5"
                     value="<?= $ov['remuneration'] ?? '' ?>"
                     placeholder="Ex: 450">
            </div>
            <div class="form-group" style="margin:0;">
              <label>Commentaire</label>
              <input type="text" class="form-control"
                     name="candidatures[<?= $ov['id_candidature'] ?>][commentaire]"
                     maxlength="50"
                     value="<?= htmlspecialchars($ov['commentaire'] ?? '') ?>"
                     placeholder="Travail sérieux, ponctuel...">
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>

      <div class="mt-2">
        <button type="submit" class="btn btn-primary">💾 Enregistrer les évaluations</button>
        <a href="mes_offres.php" class="btn btn-outline" style="margin-left:.5rem;">Annuler</a>
      </div>
    </form>
  <?php endif; ?>
</div>
</body>
</html>
