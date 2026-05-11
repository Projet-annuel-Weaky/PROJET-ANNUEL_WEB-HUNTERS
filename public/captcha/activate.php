<?php
include_once 'config.php';
require_once __DIR__ . "/../../src/services/AdminService.php";

AdminService::requireAdmin('../login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id'])) {
    $id = intval($_POST['id']);
    $active = isset($_POST['active']) ? (int) $_POST['active'] : 0;

    $sql = "UPDATE captcha_images SET active = :active WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':active', $active, PDO::PARAM_INT);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    logAction("activate: Set active=$active for id=$id.");
}

header('Location: ../manage_captcha.php');
exit;
