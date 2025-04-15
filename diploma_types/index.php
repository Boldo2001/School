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

// Haal alle diploma types op met aantal uitgereikte diploma's
$query = "
    SELECT dt.*, 
           COUNT(d.id) AS aantal_uitgereikt,
           MIN(d.behaald_datum) AS eerste_uitgifte,
           MAX(d.behaald_datum) AS laatste_uitgifte
    FROM diploma_types dt
    LEFT JOIN diplomas d ON dt.id = d.diploma_type_id
    GROUP BY dt.id
    ORDER BY dt.naam
";
$stmt = $pdo->query($query);
$diplomaTypes = $stmt->fetchAll();
?>

<div class="page-header">
    <h2>Diploma Types Overzicht</h2>
    <a href="add.php" class="btn">Nieuw Diploma Type Toevoegen</a>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Naam</th>
            <th>Geldigheid (jaren)</th>
            <th>Aantal Uitgereikt</th>
            <th>Eerste Uitgifte</th>
            <th>Laatste Uitgifte</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($diplomaTypes as $type): ?>
        <tr>
            <td>
                <a href="view.php?id=<?= $type['id'] ?>">
                    <?= htmlspecialchars($type['naam']) ?>
                </a>
            </td>
            <td><?= $type['geldigheid_jaren'] ?></td>
            <td><?= $type['aantal_uitgereikt'] ?></td>
            <td>
                <?= $type['eerste_uitgifte'] ? date('d-m-Y', strtotime($type['eerste_uitgifte'])) : 'N.v.t.' ?>
            </td>
            <td>
                <?= $type['laatste_uitgifte'] ? date('d-m-Y', strtotime($type['laatste_uitgifte'])) : 'N.v.t.' ?>
            </td>
            <td>
                <a href="view.php?id=<?= $type['id'] ?>" class="btn">Bekijken</a>
                <a href="edit.php?id=<?= $type['id'] ?>" class="btn">Bewerken</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>