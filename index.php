<?php
// index.php - Page d'accueil Uber-Cueillette
session_start(); 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Uber-Cueillette - Accueil</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- NAVBAR -->
<nav>
  <a href="index.php" class="nav-brand">🌿 Uber<span>-Cueillette</span></a>
  <div class="nav-links">
    <?php if (isset($_SESSION['agriculteur'])): ?> <!-- Si l'agriculteur est connecté ? , afficher les liens de son espace -->
      <a href="agriculteur/dashboard.php">Mon espace</a> 
      <a href="agriculteur/logout.php" class="btn-logout">Déconnexion</a>
    <?php elseif (isset($_SESSION['ouvrier'])): ?>
      <a href="ouvrier/dashboard.php">Mon espace</a>
      <a href="ouvrier/logout.php" class="btn-logout">Déconnexion</a>
    <?php else: ?>
      <a href="agriculteur/login.php">Agriculteur</a>
      <a href="ouvrier/login.php">Ouvrier</a>
    <?php endif; ?>
  </div>
</nav>

<!-- HERO -->
<div class="hero">
  <h1>🌾 Uber-Cueillette</h1>
  <p>La plateforme qui met en relation agriculteurs et ouvriers pour les saisons de récolte</p>
</div>

<!-- CONTENU PRINCIPAL -->
<div class="container">

  <?php if (!isset($_SESSION['agriculteur']) && !isset($_SESSION['ouvrier'])): ?>
  <!-- Choix du rôle -->
  <div class="text-center mb-2">
    <h2 style="font-family:'Playfair Display',serif; color:var(--olive-dark); font-size:1.8rem;">
      Bienvenue sur Uber-Cueillette
    </h2>
    <p class="text-muted mt-1">Choisissez votre rôle pour commencer</p>
  </div>

  <div class="home-roles">
    <a href="agriculteur/login.php" class="role-card agriculteur">
      <div class="icon"><img src="agriculteur.jpg" alt="Agriculteur" width="100%" height="100%"></div>
      <h2>Agriculteur</h2>
      <p>Publiez des offres de récolte et gérez vos chantiers</p>
      <div class="mt-2">
        <span class="btn btn-outline" style="margin-top:.5rem;">Se connecter</span>
      </div>
    </a>
    <a href="ouvrier/login.php" class="role-card ouvrier">
      <div class="icon"><img src="ouvrier.jpg" alt="Ouvrier" width="100%" height="100%"></div>
      <h2>Ouvrier de récolte</h2>
      <p>Consultez les offres, postulez et suivez vos chantiers</p>
      <div class="mt-2">
        <span class="btn btn-outline" style="margin-top:.5rem; border-color:var(--earth); color:var(--earth);">Se connecter</span>
      </div>
    </a>
  </div>

  <!-- Stats / présentation -->
  <div class="card text-center" style="margin-top:1rem;">
    <div style="display:grid; grid-template-columns: repeat(3,1fr); gap:2rem; padding:1rem;">
      <div>
        <div style="font-size:2.5rem; font-weight:700; color:var(--olive);">🫒</div>
        <p style="font-weight:600; margin-top:.5rem;">Olives, Agrumes, Tomates...</p>
        <p class="text-muted" style="font-size:.85rem;">Tous types de récolte</p>
      </div>
      <div>
        <div style="font-size:2.5rem; font-weight:700; color:var(--olive);">24</div>
        <p style="font-weight:600; margin-top:.5rem;">Gouvernorats couverts</p>
        <p class="text-muted" style="font-size:.85rem;">Partout en Tunisie</p>
      </div>
      <div>
        <div style="font-size:2.5rem; font-weight:700; color:var(--olive);">⭐</div>
        <p style="font-weight:600; margin-top:.5rem;">Système de notation</p>
        <p class="text-muted" style="font-size:.85rem;">Qualité garantie</p>
      </div>
    </div>
  </div>

  <?php elseif (isset($_SESSION['agriculteur'])): ?>
    <script>window.location.href='agriculteur/dashboard.php';</script> <!-- Redirection vers le dashboard de l'agriculteur -->
  <?php else: ?>
    <script>window.location.href='ouvrier/dashboard.php';</script> <!-- Redirection vers le dashboard de l'ouvrier -->
  <?php endif; ?>

</div>

<footer style="text-align:center; padding:1.5rem; color:var(--grey); font-size:.85rem; border-top:1px solid var(--grey-light); margin-top:3rem;">
  &copy; 2026 Uber-Cueillette — ISG Tunis | Zakaria ben Mohamed
</footer>

</body>
</html>
