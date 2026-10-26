<?php
require_once 'db.php';

function h($value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $title = trim($_POST['title'] ?? '');
    $director = trim($_POST['director'] ?? '');
    $year = filter_input(INPUT_POST, 'year', FILTER_VALIDATE_INT);
    $genre = trim($_POST['genre'] ?? '');
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_FLOAT);

    if (
        !$id || $title === '' || $director === '' || $genre === '' ||
        strlen($title) > 255 || strlen($director) > 150 ||
        strlen($genre) > 100 ||
        $year === false || $year === null ||
        $year < 1888 || $year > 2100 ||
        $rating === false || $rating === null ||
        $rating < 0 || $rating > 10
    ) {
        http_response_code(400);
        exit('Некоректні дані. Перевірте введені значення.');
    }

    $stmt = $pdo->prepare(
        'UPDATE movies
         SET title = :title,
             director = :director,
             year = :year,
             genre = :genre,
             rating = :rating
         WHERE id = :id'
    );

    $stmt->execute([
        ':title' => $title,
        ':director' => $director,
        ':year' => $year,
        ':genre' => $genre,
        ':rating' => $rating,
        ':id' => $id,
    ]);

    header('Location: index.php');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    http_response_code(400);
    exit('Не вказано коректний ID фільму.');
}

$stmt = $pdo->prepare('SELECT * FROM movies WHERE id = :id');
$stmt->execute([':id' => $id]);
$movie = $stmt->fetch();

if (!$movie) {
    http_response_code(404);
    exit('Фільм не знайдено.');
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редагування фільму</title>
</head>
<body>
    <h1>Редагувати фільм</h1>

    <form action="edit.php" method="post">
        <input type="hidden" name="id" value="<?= h($movie['id']) ?>">

        <p>
            <label>Назва:
                <input name="title" maxlength="255"
                       value="<?= h($movie['title']) ?>" required>
            </label>
        </p>

        <p>
            <label>Режисер:
                <input name="director" maxlength="150"
                       value="<?= h($movie['director']) ?>" required>
            </label>
        </p>

        <p>
            <label>Рік:
                <input type="number" name="year" min="1888" max="2100"
                       value="<?= h($movie['year']) ?>" required>
            </label>
        </p>

        <p>
            <label>Жанр:
                <input name="genre" maxlength="100"
                       value="<?= h($movie['genre']) ?>" required>
            </label>
        </p>

        <p>
            <label>Рейтинг:
                <input type="number" name="rating" min="0" max="10"
                       step="0.1" value="<?= h($movie['rating']) ?>" required>
            </label>
        </p>

        <button type="submit">Зберегти зміни</button>
        <a href="index.php">Скасувати</a>
    </form>
</body>
</html>
