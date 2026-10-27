<?php
// agriculteur/login.php
session_start();
if (isset($_SESSION['agriculteur'])) { header('Location: dashboard.php'); exit; }
require_once '../config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Si le formulaire est soumis 
    $pseudo   = trim($_POST['pseudo'] ?? ''); // Récupérer le pseudo du formulaire, si non défini, utiliser une chaîne vide
    $password = $_POST['password'] ?? '';

    if ($pseudo && $password) { // Vérifier que les champs ne sont pas vides 
        $stmt = $pdo->prepare("SELECT * FROM agriculteur WHERE pseudo = ?"); // Préparer une requête pour sélectionner l'agriculteur avec le pseudo donné ; $pdo->prepare(...) : Prépare une requête SQL sécurisée (évite les injections SQL). ,
        $stmt->execute([$pseudo]);// Exécuter la requête avec le pseudo comme paramètre
        $agriculteur = $stmt->fetch(); // fetch() : Récupère la première ligne du résultat de la requête sous forme de tableau associatif. Si aucun résultat, retourne false. 

        if ($agriculteur && password_verify($password, $agriculteur['password'])) { // Vérifier que l'agriculteur existe et que le mot de passe correspond (password_verify() : Vérifie que le mot de passe fourni correspond au hash stocké dans la base de données.)
            $_SESSION['agriculteur'] = $agriculteur;
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Pseudo ou mot de passe incorrect."; 
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion Agriculteur - Uber-Cueillette</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav>
  <a href="../index.php" class="nav-brand">🌿 Uber<span>-Cueillette</span></a>
  <div class="nav-links">
    <a href="inscription.php">S'inscrire</a>
    <a href="../ouvrier/login.php">Espace ouvrier</a>
  </div>
</nav>

<div class="container-sm">
  <div class="card mt-3">
    <div class="card-title"><img src="agriculteur.jpg" alt="Agriculteur" width="55%" height="55%"> Connexion Agriculteur</div>

    <?php if ($error): ?> <!-- Si une $erreur n'est pas vide, afficher un message d'erreur -->
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div> <!-- htmlspecialchars() : Échappe les caractères spéciaux pour éviter les attaques XSS. Affiche le message d'erreur de manière sécurisée. -->
    <?php endif; ?>

    <form id="formLogin" method="POST" onsubmit="return validateLogin()">
      <div class="form-group">
        <label for="pseudo">Pseudo <span class="required">*</span></label>
        <input type="text" class="form-control" id="pseudo" name="pseudo"
               value="<?= htmlspecialchars($_POST['pseudo'] ?? '') ?>" placeholder="Votre pseudo"> <!-- value="<?= htmlspecialchars($_POST['pseudo'] ?? '') ?>" :Garde la valeur saisie si le formulaire échoue (évite de retaper) . -->
        <div class="error-msg" id="pseudo_err"></div>
      </div>
      <div class="form-group">
        <label for="password">Mot de passe <span class="required">*</span></label>
        <input type="password" class="form-control" id="password" name="password"
               placeholder="Votre mot de passe">
        <div class="error-msg" id="password_err"></div>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
    </form>

    <p class="text-center mt-2 text-muted">
      Pas encore inscrit ? <a href="inscription.php" style="color:var(--olive);">S'inscrire</a>
    </p>
  </div>
</div>

<script src="../js/validation.js"></script>
</body>
</html>
