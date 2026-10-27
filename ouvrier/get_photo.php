<?php
// ouvrier/get_photo.php - Sert la photo d'un ouvrier depuis la base de données
require_once '../config.php';
$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT photo FROM ouvrier WHERE id_ouvrier = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row && $row['photo']) {
        header('Content-Type: image/jpeg');
        echo $row['photo'];
        exit;
    }
}
// Rediriger vers une image par défaut si pas de photo
header('Content-Type: image/svg+xml');
echo '<svg xmlns="http://www.w3.org/2000/svg" width="150%" height="150%"">
  <circle cx="50" cy="50" r="50" fill="#7aad3a"/>
  <text x="50" y="65" width="150%" height="150%" text-anchor="middle" fill="white">👷</text>
</svg>';
?>
