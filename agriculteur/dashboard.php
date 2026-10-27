<?php
// agriculteur/dashboard.php
session_start();
if (!isset($_SESSION['agriculteur'])) { header('Location: login.php'); exit; } // Vérifier que l'agriculteur est connecté, sinon rediriger vers la page de connexion
require_once '../config.php'; 

$ag = $_SESSION['agriculteur']; // Récupérer les informations de l'agriculteur connecté à partir de la session
$id_ag = $ag['id_agriculteur'];// Stocker l'ID de l'agriculteur dans une variable pour les requêtes suivantes

// Statistiques
$stmt = $pdo->prepare("SELECT COUNT(*) FROM offre WHERE id_agriculteur=?"); // 	Seulement les offres de cet agriculteur
$stmt->execute([$id_ag]);
$nb_offres = $stmt->fetchColumn();// fetchColumn() est utilisé pour récupérer la première colonne de la première ligne du résultat, qui correspond au nombre total d'offres publiées par cet agriculteur

$stmt = $pdo->prepare(" SELECT COUNT(*) FROM candidature c 
JOIN offre o ON c.id_offre=o.id_offre 
WHERE o.id_agriculteur=? AND c.decision='encours' ");
$stmt->execute([$id_ag]);
$nb_en_attente = $stmt->fetchColumn(); //sans fetchColumn(), cette valeur reste dans la base de données, elle n'arrive jamais dans PHP.



$stmt = $pdo->prepare("SELECT COUNT(*) FROM candidature c
  JOIN offre o ON c.id_offre=o.id_offre
  WHERE o.id_agriculteur=? AND c.decision='accepte'
"); // Compter le nombre de candidatures acceptées pour les offres de cet agriculteur en utilisant une jointure entre les tables candidature et offre, et en filtrant par l'ID de l'agriculteur et la décision "accepte"
$stmt->execute([$id_ag]); // Exécuter la requête en passant l'ID de l'agriculteur comme paramètre pour sécuriser la requête contre les injections SQL
$nb_acceptes = $stmt->fetchColumn(); // Récupérer le résultat de la requête, qui correspond au nombre total de candidatures acceptées pour les offres de cet agriculteur, et le stocker dans la variable $nb_acceptes pour l'afficher sur le tableau de bord
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Agriculteur - Uber-Cueillette</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav>
  <a href="../index.php" class="nav-brand">🌿 Uber<span>-Cueillette</span></a>
  <div class="nav-links">
    <a href="dashboard.php" class="active">Tableau de bord</a>
    <a href="mes_offres.php">Mes offres</a>
    <a href="ajouter_offre.php">+ Nouvelle offre</a>
    <a href="profil.php">Mon profil</a>
    <a href="logout.php" class="btn-logout">Déconnexion</a>
  </div>
</nav>

<div class="hero">
  <h1>Bonjour, <?= htmlspecialchars($ag['prenom']) ?> 👋</h1> <!-- htmlspecialchars() pour éviter les attaques XSS , XSS:est une attaque de type Cross-Site Scripting -->
  <p>Gérez vos offres de récolte et vos ouvriers</p>
</div>

<div class="container">

  <!-- Stats -->
  <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:1.5rem; margin-bottom:2rem;">
    <div class="card text-center" style="border-top:4px solid var(--olive);">
      <div style="font-size:2.5rem; font-weight:700; color:var(--olive);"><?= $nb_offres ?></div>
      <p style="font-weight:600;">Mes offres</p>
      <p class="text-muted" style="font-size:.85rem;">Total publiées</p>
    </div>
    <div class="card text-center" style="border-top:4px solid var(--earth);">
      <div style="font-size:2.5rem; font-weight:700; color:var(--earth);"><?= $nb_en_attente ?></div>
      <p style="font-weight:600;">En attente</p>
      <p class="text-muted" style="font-size:.85rem;">Candidatures à traiter</p>
    </div>
    <div class="card text-center" style="border-top:4px solid var(--success);">
      <div style="font-size:2.5rem; font-weight:700; color:var(--success);"><?= $nb_acceptes ?></div>
      <p style="font-weight:600;">Ouvriers acceptés</p>
      <p class="text-muted" style="font-size:.85rem;">Au total</p>
    </div>
  </div>

  <!-- Actions rapides -->
  <div class="card">
    <div class="card-title">⚡ Actions rapides</div>
    <div class="flex gap-2 flex-wrap">
      <a href="ajouter_offre.php" class="btn btn-primary">🌾 Ajouter une offre</a>
      <a href="mes_offres.php" class="btn btn-outline">📋 Voir mes offres</a>
      <a href="profil.php" class="btn btn-outline">👤 Mon profil</a>
    </div>
  </div>

  <!-- Dernières candidatures en attente -->
  <div class="card mt-2">
    <div class="card-title">⏳ Candidatures en attente</div>
    <?php
    $stmt = $pdo->prepare(" SELECT c.*, o.adresse AS offre_adresse,
             tf.libelle AS fruit, g.libelle AS gouvernorat,
             ov.nom, ov.prenom, ov.photo,
             (SELECT AVG(note) FROM candidature WHERE id_ouvrier=ov.id_ouvrier AND note IS NOT NULL) AS moy_note
      FROM candidature c
      JOIN offre o ON c.id_offre=o.id_offre
      JOIN type_fruit tf ON o.id_type_fruit=tf.id_type_fruit
      JOIN gouvernorat g ON o.id_gouvernorat=g.id_gouvernorat
      JOIN ouvrier ov ON c.id_ouvrier=ov.id_ouvrier
      WHERE o.id_agriculteur=? AND c.decision='encours' 
      ORDER BY c.date_candidature DESC
      LIMIT 5   "); //Seulement 5 (pour ne pas surcharger la page) --/--- ligne 100:  Seulement les offres de cet agriculteur , et les candidatures en attente 
    $stmt->execute([$id_ag]);
    $candidatures = $stmt->fetchAll(); // fetchAll() récupère toutes les lignes du résultat sous forme de tableaux
    ?>

    <!-- Affichage du tableau des candidatures en attente, ou d'un message si aucune candidature n'est en attente -->
    <?php if (empty($candidatures)): ?> <!-- empty() vérifie si le tableau $candidatures est vide, c'est-à-dire s'il n'y a aucune candidature en attente pour les offres de cet agriculteur -->
      <div class="empty-state">
        <div class="icon">✅</div>
        <p>Aucune candidature en attente pour le moment.</p>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Ouvrier</th>
              <th>Offre</th>
              <th>Note moy.</th>
              <th>Date candidature</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($candidatures as $c): ?> <!-- Parcourir chaque candidature en attente et afficher les informations correspondantes dans le tableau -->
            <tr>
              <td><?= htmlspecialchars($c['prenom'].' '.$c['nom']) ?></td> 
              <td><?= htmlspecialchars($c['fruit'].' - '.$c['gouvernorat']) ?></td>
              <td>
                <?php if ($c['moy_note'] !== null): ?> <!-- Vérifier si la note moyenne de l'ouvrier n'est pas nulle, c'est-à-dire s'il a déjà été noté pour d'autres offres, et afficher la note avec une étoile -->
                  <span class="stars">★</span> <?= number_format($c['moy_note'],1) ?>/10 <!-- number_format() pour formater la note avec une décimale -->
                <?php else: ?> <!-- Si la note moyenne est nulle, afficher "Nouveau" avec une couleur grise pour indiquer que cet ouvrier n'a pas encore été noté pour d'autres offres -->
                  <span class="text-muted">Nouveau</span>
                <?php endif; ?>
              </td>
              <td><?= date('d/m/Y', strtotime($c['date_candidature'])) ?></td> <!-- Afficher la date de candidature au format jour/mois/année en utilisant la fonction date() et strtotime() pour convertir la date de candidature en timestamp -->
              <td>
                <a href="postulants.php?id=<?php echo $c['id_offre']; ?>" class="btn btn-sm btn-outline">Gérer</a> <!-- : lien vers la page de gestion des postulants pour cette offre ,  -->
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>
</body>
</html>
