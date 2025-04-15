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

// Haal alle personen op met aantal diploma's
$query = "
    SELECT p.*, 
           COUNT(d.id) AS aantal_diplomas
    FROM personen p
    LEFT JOIN diplomas d ON p.id = d.persoon_id
    GROUP BY p.id
    ORDER BY p.achternaam, p.voornaam
";
$stmt = $pdo->query($query);
$personen = $stmt->fetchAll();
?>

<div class="page-header">
    <h2>Personen Overzicht</h2>
    <a href="add.php" class="btn">Nieuwe Persoon Toevoegen</a>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Naam</th>
            <th>Geboortedatum</th>
            <th>Aantal Diploma's</th>
            <th>Laatste Diploma</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($personen as $persoon): 
            // Haal laatste diploma op voor deze persoon
            $stmt = $pdo->prepare("
                SELECT d.behaald_datum, dt.naam AS diploma_naam
                FROM diplomas d
                JOIN diploma_types dt ON d.diploma_type_id = dt.id
                WHERE d.persoon_id = ?
                ORDER BY d.behaald_datum DESC
                LIMIT 1
            ");
            $stmt->execute([$persoon['id']]);
            $laatste_diploma = $stmt->fetch();
        ?>
        <tr>
            <td>
                <a href="view.php?id=<?= $persoon['id'] ?>">
                    <?= htmlspecialchars($persoon['voornaam'] . ' ' . $persoon['achternaam']) ?>
                </a>
            </td>
            <td><?= $persoon['geboortedatum'] ? date('d-m-Y', strtotime($persoon['geboortedatum'])) : 'Onbekend' ?></td>
            <td><?= $persoon['aantal_diplomas'] ?></td>
            <td>
                <?php if ($laatste_diploma): ?>
                    <?= htmlspecialchars($laatste_diploma['diploma_naam']) ?><br>
                    <small><?= date('d-m-Y', strtotime($laatste_diploma['behaald_datum'])) ?></small>
                <?php else: ?>
                    Geen diploma's
                <?php endif; ?>
            </td>
            <td>
                <a href="view.php?id=<?= $persoon['id'] ?>" class="btn">Bekijken</a>
                <a href="edit.php?id=<?= $persoon['id'] ?>" class="btn">Bewerken</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>