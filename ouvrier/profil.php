<?php
// ouvrier/profil.php
session_start();
if (!isset($_SESSION['ouvrier'])) { header('Location: login.php'); exit; }
require_once '../config.php';

$ov    = $_SESSION['ouvrier'];
$id_ov = $ov['id_ouvrier'];
$msg   = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom         = trim($_POST['nom']         ?? '');
    $prenom      = trim($_POST['prenom']      ?? '');
    $email       = trim($_POST['email']       ?? '');
    $description = trim($_POST['description'] ?? '');
    $password    = $_POST['password']  ?? '';
    $password2   = $_POST['password2'] ?? '';

    if (!$nom)    $errors[] = "Le nom est obligatoire.";
    if (!$prenom) $errors[] = "Le prénom est obligatoire.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ouvrier WHERE email=? AND id_ouvrier!=?");
    $stmt->execute([$email, $id_ov]);
    if ($stmt->fetchColumn() > 0) $errors[] = "Cet email est déjà utilisé.";

    // Photo
    $photo_data = null;
    $update_photo = false;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($_FILES['photo']['type'], $allowed))
            $errors[] = "Format de photo invalide.";
        elseif ($_FILES['photo']['size'] > 2 * 1024 * 1024)
            $errors[] = "La photo ne doit pas dépasser 2 Mo.";
        else {
            $photo_data  = file_get_contents($_FILES['photo']['tmp_name']);
            $update_photo = true;
        }
    }

    $new_password = null;
    if (!empty($password)) {
        if (!preg_match('/^[A-Za-z0-9]{7,}[$#]$/', $password))
            $errors[] = "Mot de passe invalide (≥8 chars, finir par \$ ou #).";
        elseif ($password !== $password2)
            $errors[] = "Les mots de passe ne correspondent pas.";
        else
            $new_password = password_hash($password, PASSWORD_DEFAULT);
    }

    if (empty($errors)) {
        if ($update_photo && $new_password) {
            $stmt = $pdo->prepare("UPDATE ouvrier SET nom=?,prenom=?,email=?,description=?,photo=?,password=? WHERE id_ouvrier=?");
            $stmt->execute([$nom, $prenom, $email, $description, $photo_data, $new_password, $id_ov]);
        } elseif ($update_photo) {
            $stmt = $pdo->prepare("UPDATE ouvrier SET nom=?,prenom=?,email=?,description=?,photo=? WHERE id_ouvrier=?");
            $stmt->execute([$nom, $prenom, $email, $description, $photo_data, $id_ov]);
        } elseif ($new_password) {
            $stmt = $pdo->prepare("UPDATE ouvrier SET nom=?,prenom=?,email=?,description=?,password=? WHERE id_ouvrier=?");
            $stmt->execute([$nom, $prenom, $email, $description, $new_password, $id_ov]);
        } else {
            $stmt = $pdo->prepare("UPDATE ouvrier SET nom=?,prenom=?,email=?,description=? WHERE id_ouvrier=?");
            $stmt->execute([$nom, $prenom, $email, $description, $id_ov]);
        }
        // Rafraîchir session
        $stmt = $pdo->prepare("SELECT * FROM ouvrier WHERE id_ouvrier=?");
        $stmt->execute([$id_ov]);
        $_SESSION['ouvrier'] = $stmt->fetch();
        $ov = $_SESSION['ouvrier'];
        $msg = '<div class="alert alert-success">✅ Profil mis à jour avec succès.</div>';
    }
}

// Statistiques
$stmt = $pdo->prepare("SELECT AVG(note) AS moy, COUNT(note) AS nb FROM candidature WHERE id_ouvrier=? AND note IS NOT NULL");
$stmt->execute([$id_ov]);
$stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mon Profil - Uber-Cueillette</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav>
  <a href="../index.php" class="nav-brand">🌿 Uber<span>-Cueillette</span></a>
  <div class="nav-links">
    <a href="dashboard.php">Tableau de bord</a>
    <a href="offres.php">Offres</a>
    <a href="mes_candidatures.php">Mes candidatures</a>
    <a href="mes_chantiers.php">Mes chantiers</a>
    <a href="profil.php" class="active">Mon profil</a>
    <a href="logout.php" class="btn-logout">Déconnexion</a>
  </div>
</nav>

<div class="hero">
  <h1>👤 Mon Profil</h1>
  <p>Consultez et modifiez vos informations personnelles</p>
</div>

<div class="container-sm">
  <?= $msg ?>
  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <?php foreach ($errors as $e): ?><div>• <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- Aperçu profil public -->
  <div class="card">
    <div class="profile-header">
      <?php if ($ov['photo']): ?>
        <img src="get_photo.php?id=<?= $id_ov ?>" class="profile-avatar">
      <?php else: ?>
        <div class="profile-avatar-placeholder"><img src="ouvrier.jpg" alt="" width="200%" height="150%"></div>
      <?php endif; ?>
      <div>
        <h2 style="font-family:'Playfair Display',serif;">
          <?= htmlspecialchars($ov['prenom'].' '.$ov['nom']) ?>
        </h2>
        <p class="text-muted">@<?= htmlspecialchars($ov['pseudo']) ?></p>
        <p class="text-muted">CIN : <?= htmlspecialchars($ov['CIN']) ?></p>
        <?php if ($stats['moy']): ?>
          <div class="stars mt-1">
            ★★★★★ <strong><?= number_format($stats['moy'], 1) ?>/10</strong>
            <span class="text-muted">(<?= $stats['nb'] ?> évaluation(s))</span>
          </div>
        <?php else: ?>
          <p class="text-muted mt-1">Pas encore évalué</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Formulaire modification -->
  <div class="card">
    <div class="card-title">✏️ Modifier mes informations</div>

    <form method="POST" enctype="multipart/form-data">
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
        <div class="form-group">
          <label for="nom">Nom</label>
          <input type="text" class="form-control" id="nom" name="nom"
                 value="<?= htmlspecialchars($ov['nom']) ?>">
        </div>
        <div class="form-group">
          <label for="prenom">Prénom</label>
          <input type="text" class="form-control" id="prenom" name="prenom"
                 value="<?= htmlspecialchars($ov['prenom']) ?>">
        </div>
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" class="form-control" id="email" name="email"
               value="<?= htmlspecialchars($ov['email']) ?>">
      </div>
      <div class="form-group">
        <label for="description">Description (expérience, niveau éducatif...)</label>
        <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($ov['description'] ?? '') ?></textarea>
      </div>
      <div class="form-group">
        <label for="photo">Changer la photo (laisser vide pour conserver)</label>
        <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
        <img id="photo_preview" src="" alt="Aperçu"
             style="display:none; width:80px; height:80px; border-radius:50%; object-fit:cover; margin-top:.5rem;">
      </div>

      <hr style="border:none; border-top:1px solid var(--grey-light); margin:1.5rem 0;">
      <p class="text-muted" style="font-size:.85rem; margin-bottom:1rem;">
        Laissez vide pour ne pas changer le mot de passe.
      </p>
      <div class="form-group">
        <label for="password">Nouveau mot de passe</label>
        <input type="password" class="form-control" id="password" name="password"
               placeholder="≥8 chars, finir par $ ou #">
        <div class="error-msg" id="password_err"></div>
      </div>
      <div class="form-group">
        <label for="password2">Confirmer le mot de passe</label>
        <input type="password" class="form-control" id="password2" name="password2">
        <div class="error-msg" id="password2_err"></div>
      </div>

      <button type="submit" class="btn btn-primary btn-block">💾 Enregistrer</button>
    </form>
  </div>
</div>

<script src="../js/validation.js"></script>
</body>
</html>
