<?php
// agriculteur/profil.php
session_start();
if (!isset($_SESSION['agriculteur'])) { header('Location: login.php'); exit; }
require_once '../config.php';

$ag = $_SESSION['agriculteur'];
$id_ag = $ag['id_agriculteur'];
$msg = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom     = trim($_POST['nom'] ?? '');
    $prenom  = trim($_POST['prenom'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (!$nom)    $errors[] = "Le nom est obligatoire.";
    if (!$prenom) $errors[] = "Le prénom est obligatoire.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";

    // Vérifier email unique sauf soi-même
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM agriculteur WHERE email=? AND id_agriculteur!=?");
    $stmt->execute([$email, $id_ag]);
    if ($stmt->fetchColumn() > 0) $errors[] = "Cet email est déjà utilisé.";

    $new_password = null;
    if (!empty($password)) {
        if (!preg_match('/^[A-Za-z0-9]{7,}[$#]$/', $password))
            $errors[] = "Nouveau mot de passe invalide (≥8 chars, finir par \$ ou #).";
        elseif ($password !== $password2)
            $errors[] = "Les mots de passe ne correspondent pas.";
        else
            $new_password = password_hash($password, PASSWORD_DEFAULT);
    }

    if (empty($errors)) {
        if ($new_password) {
            $stmt = $pdo->prepare("UPDATE agriculteur SET nom=?,prenom=?,email=?,adresse=?,password=? WHERE id_agriculteur=?");
            $stmt->execute([$nom, $prenom, $email, $adresse, $new_password, $id_ag]);
        } else {
            $stmt = $pdo->prepare("UPDATE agriculteur SET nom=?,prenom=?,email=?,adresse=? WHERE id_agriculteur=?");
            $stmt->execute([$nom, $prenom, $email, $adresse, $id_ag]);
        }
        // Rafraîchir la session
        $stmt = $pdo->prepare("SELECT * FROM agriculteur WHERE id_agriculteur=?");
        $stmt->execute([$id_ag]);
        $_SESSION['agriculteur'] = $stmt->fetch();
        $ag = $_SESSION['agriculteur'];
        $msg = '<div class="alert alert-success">✅ Profil mis à jour avec succès.</div>';
    }
}
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
    <a href="mes_offres.php">Mes offres</a>
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

  <div class="card">
    <div class="profile-header">
      <div class="profile-avatar-placeholder"><img src="agriculteur.jpg" alt="" width="150%" height="150%"></div>
      <div>
        <h2 style="font-family:'Playfair Display',serif;">
          <?= htmlspecialchars($ag['prenom'].' '.$ag['nom']) ?>
        </h2>
        <p class="text-muted">@<?= htmlspecialchars($ag['pseudo']) ?></p>
        <p class="text-muted">CIN : <?= htmlspecialchars($ag['CIN']) ?></p>
      </div>
    </div>

    <div class="card-title">✏️ Modifier mes informations</div>

    <form method="POST">
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
        <div class="form-group">
          <label for="nom">Nom</label>
          <input type="text" class="form-control" id="nom" name="nom"
                 value="<?= htmlspecialchars($ag['nom']) ?>">
        </div>
        <div class="form-group">
          <label for="prenom">Prénom</label>
          <input type="text" class="form-control" id="prenom" name="prenom"
                 value="<?= htmlspecialchars($ag['prenom']) ?>">
        </div>
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" class="form-control" id="email" name="email"
               value="<?= htmlspecialchars($ag['email']) ?>">
      </div>
      <div class="form-group">
        <label for="adresse">Adresse</label>
        <input type="text" class="form-control" id="adresse" name="adresse"
               value="<?= htmlspecialchars($ag['adresse'] ?? '') ?>">
      </div>
      <hr style="border:none; border-top:1px solid var(--grey-light); margin:1.5rem 0;">
      <p class="text-muted" style="font-size:.85rem; margin-bottom:1rem;">
        Laissez vide pour ne pas changer le mot de passe.
      </p>
      <div class="form-group">
        <label for="password">Nouveau mot de passe</label>
        <input type="password" class="form-control" id="password" name="password"
               placeholder="≥8 chars, finir par $ ou #">
      </div>
      <div class="form-group">
        <label for="password2">Confirmer le mot de passe</label>
        <input type="password" class="form-control" id="password2" name="password2"
               placeholder="Retapez le nouveau mot de passe">
      </div>
      <button type="submit" class="btn btn-primary btn-block">💾 Enregistrer</button>
    </form>
  </div>
</div>

<script src="../js/validation.js"></script>
</body>
</html>
