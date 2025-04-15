<?php
session_start();
if (!isset($_SESSION['ingelogd'])) {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/header.php';

// Haal alle examinateurs op met aantal afgenomen examens
$query = "
    SELECT e.*, 
           COUNT(d.id) AS aantal_examens,
           MIN(d.behaald_datum) AS eerste_examen,
           MAX(d.behaald_datum) AS laatste_examen
    FROM examinateurs e
    LEFT JOIN diplomas d ON e.id = d.examinator_id
    GROUP BY e.id
    ORDER BY e.naam
";
$stmt = $pdo->query($query);
$examinateurs = $stmt->fetchAll();
?>

<div class="page-header">
    <h2>Examinateurs Overzicht</h2>
    <a href="add.php" class="btn">Nieuwe Examinator Toevoegen</a>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Naam</th>
            <th>Organisatie</th>
            <th>Aantal Examens</th>
            <th>Eerste Examen</th>
            <th>Laatste Examen</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($examinateurs as $examinator): ?>
        <tr>
            <td>
                <a href="view.php?id=<?= $examinator['id'] ?>">
                    <?= htmlspecialchars($examinator['naam']) ?>
                </a>
            </td>
            <td><?= htmlspecialchars($examinator['organisatie'] ?? 'Onbekend') ?></td>
            <td><?= $examinator['aantal_examens'] ?></td>
            <td>
                <?= $examinator['eerste_examen'] ? date('d-m-Y', strtotime($examinator['eerste_examen'])) : 'N.v.t.' ?>
            </td>
            <td>
                <?= $examinator['laatste_examen'] ? date('d-m-Y', strtotime($examinator['laatste_examen'])) : 'N.v.t.' ?>
            </td>
            <td>
                <a href="view.php?id=<?= $examinator['id'] ?>" class="btn">Bekijken</a>
                <a href="edit.php?id=<?= $examinator['id'] ?>" class="btn">Bewerken</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>