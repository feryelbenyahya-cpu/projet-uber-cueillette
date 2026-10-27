<?php
// agriculteur/ajouter_offre.php
session_start();
if (!isset($_SESSION['agriculteur'])) { header('Location: login.php'); exit; }
require_once '../config.php';

$ag = $_SESSION['agriculteur'];
$errors = [];
$success = false;

// Récupérer les listes
$fruits = $pdo->query("SELECT * FROM type_fruit ORDER BY libelle")->fetchAll();
$gouvernorats = $pdo->query("SELECT * FROM gouvernorat ORDER BY libelle")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_fruit     = (int)($_POST['id_type_fruit'] ?? 0);
    $id_gouv      = (int)($_POST['id_gouvernorat'] ?? 0);
    $adresse      = trim($_POST['adresse'] ?? '');
    $date_debut   = $_POST['date_debut'] ?? '';
    $date_fin     = $_POST['date_fin'] ?? '';
    $nb_ouvriers  = (int)($_POST['nombre_ouvriers'] ?? 0);
    $prix_journee = (float)($_POST['prix_journee'] ?? 0);
    $date_limite  = $_POST['date_limite'] ?? '';
    $today = date('Y-m-d');

    if (!$id_fruit)     $errors[] = "Veuillez sélectionner le type de fruit.";
    if (!$id_gouv)      $errors[] = "Veuillez sélectionner le gouvernorat.";
    if (empty($adresse)) $errors[] = "L'adresse du site est obligatoire.";
    if (!$date_debut || $date_debut < $today) $errors[] = "La date de début doit être aujourd'hui ou future.";
    if (!$date_fin || $date_fin <= $date_debut) $errors[] = "La date de fin doit être après la date de début.";
    if (!$date_limite || $date_limite > $date_debut) $errors[] = "La date limite doit être avant la date de début.";
    if ($nb_ouvriers < 1) $errors[] = "Le nombre d'ouvriers doit être supérieur à 0.";
    if ($prix_journee <= 0) $errors[] = "Le prix à la journée doit être positif.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO offre (id_type_fruit, id_gouvernorat, adresse, date_debut, date_fin,
                               nombre_ouvriers, prix_journee, date_limite, id_agriculteur)
            VALUES (?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([$id_fruit, $id_gouv, $adresse, $date_debut, $date_fin,
                        $nb_ouvriers, $prix_journee, $date_limite, $ag['id_agriculteur']]);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ajouter une offre - Uber-Cueillette</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav>
  <a href="../index.php" class="nav-brand">🌿 Uber<span>-Cueillette</span></a>
  <div class="nav-links">
    <a href="dashboard.php">Tableau de bord</a>
    <a href="mes_offres.php">Mes offres</a>
    <a href="ajouter_offre.php" class="active">+ Nouvelle offre</a>
    <a href="profil.php">Mon profil</a>
    <a href="logout.php" class="btn-logout">Déconnexion</a>
  </div>
</nav>

<div class="hero">
  <h1>🌾 Publier une offre de récolte</h1>
  <p>Trouvez les ouvriers dont vous avez besoin pour votre chantier</p>
</div>

<div class="container-sm">
  <div class="card">
    <div class="card-title">📋 Détails de l'offre</div>

    <?php if ($success): ?>
      <div class="alert alert-success">
        ✅ Offre publiée avec succès !
        <a href="mes_offres.php">Voir mes offres</a>
      </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <?php foreach ($errors as $e): ?><div>• <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST" onsubmit="return validateOffre()">
      <div class="form-group">
        <label for="id_type_fruit">Type de fruit <span class="required">*</span></label>
        <select class="form-control" id="id_type_fruit" name="id_type_fruit">
          <option value="">-- Sélectionnez --</option>
          <?php foreach ($fruits as $f): ?>
            <option value="<?= $f['id_type_fruit'] ?>"
              <?= (($_POST['id_type_fruit'] ?? '') == $f['id_type_fruit']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($f['libelle']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="id_gouvernorat">Gouvernorat <span class="required">*</span></label>
        <select class="form-control" id="id_gouvernorat" name="id_gouvernorat">
          <option value="">-- Sélectionnez --</option>
          <?php foreach ($gouvernorats as $g): ?>
            <option value="<?= $g['id_gouvernorat'] ?>"
              <?= (($_POST['id_gouvernorat'] ?? '') == $g['id_gouvernorat']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($g['libelle']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="adresse">Adresse du site de récolte <span class="required">*</span></label>
        <input type="text" class="form-control" id="adresse" name="adresse"
               value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>"
               placeholder="Ex: Route de Sfax, km 5">
        <div class="error-msg" id="adresse_err"></div>
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
        <div class="form-group">
          <label for="date_debut">Date de début <span class="required">*</span></label>
          <input type="date" class="form-control" id="date_debut" name="date_debut"
                 value="<?= htmlspecialchars($_POST['date_debut'] ?? '') ?>">
          <div class="error-msg" id="date_debut_err"></div>
        </div>
        <div class="form-group">
          <label for="date_fin">Date de fin <span class="required">*</span></label>
          <input type="date" class="form-control" id="date_fin" name="date_fin"
                 value="<?= htmlspecialchars($_POST['date_fin'] ?? '') ?>">
          <div class="error-msg" id="date_fin_err"></div>
        </div>
      </div>

      <div class="form-group">
        <label for="date_limite">Date limite de candidature <span class="required">*</span></label>
        <input type="date" class="form-control" id="date_limite" name="date_limite"
               value="<?= htmlspecialchars($_POST['date_limite'] ?? '') ?>">
        <div class="error-msg" id="date_limite_err"></div>
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
        <div class="form-group">
          <label for="nombre_ouvriers">Nombre d'ouvriers <span class="required">*</span></label>
          <input type="number" class="form-control" id="nombre_ouvriers" name="nombre_ouvriers"
                 min="1" value="<?= htmlspecialchars($_POST['nombre_ouvriers'] ?? '') ?>"
                 placeholder="Ex: 10">
          <div class="error-msg" id="nombre_ouvriers_err"></div>
        </div>
        <div class="form-group">
          <label for="prix_journee">Prix à la journée (DT) <span class="required">*</span></label>
          <input type="number" class="form-control" id="prix_journee" name="prix_journee"
                 min="0" step="0.5" value="<?= htmlspecialchars($_POST['prix_journee'] ?? '') ?>"
                 placeholder="Ex: 25.0">
          <div class="error-msg" id="prix_journee_err"></div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-block">Publier l'offre</button>
    </form>
  </div>
</div>

<script src="../js/validation.js"></script>
</body>
</html>
