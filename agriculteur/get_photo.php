<?php
// agriculteur/get_photo.php - Sert la photo d'un ouvrier depuis la DB
require_once '../config.php';
$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT photo FROM ouvrier WHERE id_ouvrier=?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row && $row['photo']) {
        header('Content-Type: image/jpeg');
        echo $row['photo'];
        exit;
    }
}
// Image par défaut si pas de photo
header('Location: ../img/default_avatar.png');
?>
