<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$title = trim($_POST['title'] ?? '');
$director = trim($_POST['director'] ?? '');
$year = filter_input(INPUT_POST, 'year', FILTER_VALIDATE_INT);
$genre = trim($_POST['genre'] ?? '');
$rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_FLOAT);

if (
    $title === '' ||
    $director === '' ||
    $genre === '' ||
    strlen($title) > 255 ||
    strlen($director) > 150 ||
    strlen($genre) > 100 ||
    $year === false || $year === null ||
    $year < 1888 || $year > 2100 ||
    $rating === false || $rating === null ||
    $rating < 0 || $rating > 10
) {
    http_response_code(400);
    exit('Некоректні дані фільму. Поверніться назад і перевірте форму.');
}

$stmt = $pdo->prepare(
    'INSERT INTO movies (title, director, year, genre, rating)
     VALUES (:title, :director, :year, :genre, :rating)'
);

$stmt->execute([
    ':title' => $title,
    ':director' => $director,
    ':year' => $year,
    ':genre' => $genre,
    ':rating' => $rating,
]);

header('Location: index.php');
exit;
