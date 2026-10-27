<?php
// ouvrier/offres.php
session_start();
if (!isset($_SESSION['ouvrier'])) { header('Location: login.php'); exit; }
require_once '../config.php';

$ov    = $_SESSION['ouvrier'];
$id_ov = $ov['id_ouvrier'];
$msg   = '';

// Récupérer listes pour filtres
$fruits       = $pdo->query("SELECT * FROM type_fruit ORDER BY libelle")->fetchAll();
$gouvernorats = $pdo->query("SELECT * FROM gouvernorat ORDER BY libelle")->fetchAll();

// Filtres
$filtre_fruit  = (int)($_GET['fruit'] ?? 0);
$filtre_gouv   = (int)($_GET['gouv'] ?? 0);
$tri_prix      = $_GET['tri'] ?? '';

// Postuler
if (isset($_GET['postuler'])) {
    $id_offre = (int)$_GET['postuler'];
    // Vérifier si déjà postulé
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM candidature WHERE id_offre=? AND id_ouvrier=?");
    $stmt->execute([$id_offre, $id_ov]);
    if ($stmt->fetchColumn() > 0) {
        $msg = '<div class="alert alert-danger">❌ Vous avez déjà postulé à cette offre.</div>';
    } else {
        $pdo->prepare("INSERT INTO candidature (id_offre, id_ouvrier) VALUES (?,?)")
            ->execute([$id_offre, $id_ov]);
        $msg = '<div class="alert alert-success">✅ Votre candidature a été soumise avec succès !</div>';
    }
}

// Construction requête avec filtres
$where = ["o.date_limite >= CURDATE()"];
$params = [];

if ($filtre_fruit > 0) {
    $where[] = "o.id_type_fruit = ?";
    $params[] = $filtre_fruit;
}
if ($filtre_gouv > 0) {
    $where[] = "o.id_gouvernorat = ?";
    $params[] = $filtre_gouv;
}

// Exclure offres clôturées (quota atteint)
$where[] = "(SELECT COUNT(*) FROM candidature WHERE id_offre=o.id_offre AND decision='accepte') < o.nombre_ouvriers";

$whereSQL = "WHERE " . implode(" AND ", $where);
$orderSQL = ($tri_prix === 'asc') ? "ORDER BY o.prix_journee ASC"
          : (($tri_prix === 'desc') ? "ORDER BY o.prix_journee DESC"
          : "ORDER BY o.date_debut ASC");

$stmt = $pdo->prepare("
    SELECT o.*,
           tf.libelle AS fruit,
           g.libelle  AS gouvernorat,
           ag.nom AS ag_nom, ag.prenom AS ag_prenom,
           (SELECT COUNT(*) FROM candidature WHERE id_offre=o.id_offre AND decision='accepte') AS nb_acceptes,
           (SELECT COUNT(*) FROM candidature WHERE id_offre=o.id_offre AND id_ouvrier=?) AS deja_postule
    FROM offre o
    JOIN type_fruit tf ON o.id_type_fruit = tf.id_type_fruit
    JOIN gouvernorat g ON o.id_gouvernorat = g.id_gouvernorat
    JOIN agriculteur ag ON o.id_agriculteur = ag.id_agriculteur
    $whereSQL
    $orderSQL
");
$stmt->execute(array_merge([$id_ov], $params));
$offres = $stmt->fetchAll();

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
  <title>Offres disponibles - Uber-Cueillette</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav>
  <a href="../index.php" class="nav-brand">🌿 Uber<span>-Cueillette</span></a>
  <div class="nav-links">
    <a href="dashboard.php">Tableau de bord</a>
    <a href="offres.php" class="active">Offres</a>
    <a href="mes_candidatures.php">Mes candidatures</a>
    <a href="mes_chantiers.php">Mes chantiers</a>
    <a href="profil.php">Mon profil</a>
    <a href="logout.php" class="btn-logout">Déconnexion</a>
  </div>
</nav>

<div class="hero">
  <h1>🌾 Offres de récolte disponibles</h1>
  <p>Trouvez le chantier qui vous convient et postulez en un clic</p>
</div>

<div class="container">
  <?= $msg ?>

  <!-- Filtres -->
  <form method="GET" action="offres.php">
    <div class="filters">
      <div class="form-group">
        <label>🍅 Type de fruit</label>
        <select name="fruit" class="form-control">
          <option value="0">Tous les fruits</option>
          <?php foreach ($fruits as $f): ?>
            <option value="<?= $f['id_type_fruit'] ?>"
              <?= $filtre_fruit == $f['id_type_fruit'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($f['libelle']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>📍 Gouvernorat</label>
        <select name="gouv" class="form-control">
          <option value="0">Tous les gouvernorats</option>
          <?php foreach ($gouvernorats as $g): ?>
            <option value="<?= $g['id_gouvernorat'] ?>"
              <?= $filtre_gouv == $g['id_gouvernorat'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($g['libelle']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>💰 Trier par prix</label>
        <select name="tri" class="form-control">
          <option value="">-- Aucun tri --</option>
          <option value="asc"  <?= $tri_prix === 'asc'  ? 'selected' : '' ?>>Prix croissant</option>
          <option value="desc" <?= $tri_prix === 'desc' ? 'selected' : '' ?>>Prix décroissant</option>
        </select>
      </div>
      <div class="form-group" style="align-self:flex-end;">
        <button type="submit" class="btn btn-primary">🔍 Filtrer</button>
        <a href="offres.php" class="btn btn-outline" style="margin-left:.5rem;">↺ Reset</a>
      </div>
    </div>
  </form>

  <div class="text-muted mb-2" style="font-size:.9rem;">
    <?= count($offres) ?> offre(s) trouvée(s)
  </div>

  <?php if (empty($offres)): ?>
    <div class="card">
      <div class="empty-state">
        <div class="icon">🔍</div>
        <p>Aucune offre disponible pour ces critères.</p>
        <a href="offres.php" class="btn btn-outline mt-2">Voir toutes les offres</a>
      </div>
    </div>
  <?php else: ?>
    <div class="offre-grid">
      <?php foreach ($offres as $o):
        $duree    = (strtotime($o['date_fin']) - strtotime($o['date_debut'])) / 86400 + 1;
        $gain_tot = $duree * $o['prix_journee'];
        $restants = $o['nombre_ouvriers'] - $o['nb_acceptes'];
      ?>
      <div class="offre-card">
        <div class="offre-card-header">
          <span class="emoji"><?= fruitEmoji($o['fruit']) ?></span>
          <div>
            <h3><?= htmlspecialchars($o['fruit']) ?></h3>
            <div style="font-size:.85rem; opacity:.85;">
              📍 <?= htmlspecialchars($o['gouvernorat']) ?>
            </div>
          </div>
        </div>
        <div class="offre-card-body">
          <div class="offre-price">
            <?= number_format($o['prix_journee'], 2) ?> DT <span>/ jour</span>
          </div>
          <div class="offre-meta">📅 Début : <strong><?= date('d/m/Y', strtotime($o['date_debut'])) ?></strong></div>
          <div class="offre-meta">📅 Fin : <strong><?= date('d/m/Y', strtotime($o['date_fin'])) ?></strong></div>
          <div class="offre-meta">⏱ Durée : <strong><?= (int)$duree ?> jour(s)</strong></div>
          <div class="offre-meta">💵 Gain estimé : <strong><?= number_format($gain_tot, 2) ?> DT</strong></div>
          <div class="offre-meta">📍 Adresse : <strong><?= htmlspecialchars($o['adresse'] ?? '—') ?></strong></div>
          <div class="offre-meta">👷 Places restantes : <strong><?= $restants ?>/<?= $o['nombre_ouvriers'] ?></strong></div>
          <div class="offre-meta">⏳ Limite : <strong><?= date('d/m/Y', strtotime($o['date_limite'])) ?></strong></div>
          <div class="offre-meta">🧑‍🌾 Agriculteur : <strong><?= htmlspecialchars($o['ag_prenom'].' '.$o['ag_nom']) ?></strong></div>
        </div>
        <div class="offre-card-footer">
          <?php if ($o['deja_postule']): ?>
            <span class="badge badge-info">✅ Déjà postulé</span>
          <?php else: ?>
            <a href="offres.php?postuler=<?= $o['id_offre'] ?><?=
                $filtre_fruit ? '&fruit='.$filtre_fruit : '' ?><?=
                $filtre_gouv  ? '&gouv='.$filtre_gouv   : '' ?><?=
                $tri_prix     ? '&tri='.$tri_prix        : '' ?>"
               class="btn btn-primary btn-sm"
               onclick="return confirm('Postuler à cette offre de récolte de <?= htmlspecialchars($o['fruit']) ?> ?')">
              👆 Postuler
            </a>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
</body>
</html>
