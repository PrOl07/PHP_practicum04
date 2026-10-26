<?php
require_once 'db.php';

// Отримуємо жанр із форми пошуку.
$genre = trim($_GET['genre'] ?? '');
$limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT);

if ($limit === false || $limit === null || $limit < 1) {
    $limit = 5;
}

// Вибірка за жанром або всіх фільмів.
if ($genre !== '') {
    $stmt = $pdo->prepare(
        'SELECT * FROM movies WHERE genre = :genre ORDER BY title'
    );
    $stmt->execute([':genre' => $genre]);
    $movies = $stmt->fetchAll();
} elseif (isset($_GET['top'])) {
    // LIMIT передаємо як ціле число.
    $limit = min($limit, 100);

    $stmt = $pdo->prepare(
        'SELECT * FROM movies ORDER BY rating DESC LIMIT :limit'
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $movies = $stmt->fetchAll();
} else {
    $stmt = $pdo->query('SELECT * FROM movies ORDER BY id');
    $movies = $stmt->fetchAll();
}

// Допоміжна функція для безпечного HTML-виведення.
function h($value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Кінотека</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 16px;
            background: #f4f6f9;
            color: #222;
        }
        h1, h2 { color: #253858; }
        section {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
        }
        input, button {
            padding: 9px;
            margin: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th { background: #e8edf5; }
        a { margin-right: 8px; }
    </style>
</head>
<body>
    <h1>Моя кінотека</h1>

    <section>
        <h2>Додати фільм</h2>
        <form action="add.php" method="post">
            <input name="title" placeholder="Назва" required maxlength="255">
            <input name="director" placeholder="Режисер" required maxlength="150">
            <input name="year" type="number" placeholder="Рік"
                   min="1888" max="2100" required>
            <input name="genre" placeholder="Жанр" required maxlength="100">
            <input name="rating" type="number" placeholder="Рейтинг"
                   min="0" max="10" step="0.1" required>
            <button type="submit">Додати</button>
        </form>
    </section>

    <section>
        <h2>Пошук за жанром</h2>
        <form action="index.php" method="get">
            <input name="genre" value="<?= h($genre) ?>"
                   placeholder="Наприклад, Horror">
            <button type="submit">Знайти</button>
            <a href="index.php">Скинути фільтри</a>
        </form>

        <h2>Найкращі фільми</h2>
        <form action="index.php" method="get">
            <input type="number" name="limit" value="<?= h($limit) ?>"
                   min="1" max="100" required>
            <button type="submit" name="top" value="1">
                Показати топ
            </button>
        </form>
    </section>

    <section>
        <h2>Список фільмів</h2>

        <?php if (count($movies) === 0): ?>
            <p>Фільмів не знайдено.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Назва</th>
                        <th>Режисер</th>
                        <th>Рік</th>
                        <th>Жанр</th>
                        <th>Рейтинг</th>
                        <th>Дії</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movies as $movie): ?>
                        <tr>
                            <td><?= h($movie['id']) ?></td>
                            <td><?= h($movie['title']) ?></td>
                            <td><?= h($movie['director']) ?></td>
                            <td><?= h($movie['year']) ?></td>
                            <td><?= h($movie['genre']) ?></td>
                            <td><?= h($movie['rating']) ?></td>
                            <td>
                                <a href="edit.php?id=<?= h($movie['id']) ?>">
                                    Редагувати
                                </a>

                                <form action="delete.php" method="post"
                                      style="display:inline"
                                      onsubmit="return confirm('Видалити цей фільм?')">
                                    <input type="hidden" name="id"
                                           value="<?= h($movie['id']) ?>">
                                    <button type="submit">Видалити</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</body>
</html>
