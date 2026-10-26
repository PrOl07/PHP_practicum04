<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    http_response_code(400);
    exit('Некоректний ID фільму.');
}

$stmt = $pdo->prepare('DELETE FROM movies WHERE id = :id');
$stmt->execute([':id' => $id]);

header('Location: index.php');
exit;
