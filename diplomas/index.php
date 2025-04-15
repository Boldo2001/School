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

// Haal alle diploma's op met gerelateerde gegevens
$query = "
    SELECT d.*, 
           p.voornaam, p.achternaam, 
           dt.naam AS diploma_type_naam,
           e.naam AS examinator_naam
    FROM diplomas d
    JOIN personen p ON d.persoon_id = p.id
    JOIN diploma_types dt ON d.diploma_type_id = dt.id
    JOIN examinateurs e ON d.examinator_id = e.id
    ORDER BY d.geldig_tot DESC
";
$stmt = $pdo->query($query);
$diplomas = $stmt->fetchAll();
?>

<div class="page-header">
    <h2>Diploma Overzicht</h2>
    <a href="add.php" class="btn">Nieuw Diploma Toevoegen</a>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Diploma Nr</th>
            <th>Naam</th>
            <th>Diploma Type</th>
            <th>Behaald op</th>
            <th>Geldig tot</th>
            <th>Status</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($diplomas as $diploma): ?>
        <tr>
            <td><?= htmlspecialchars($diploma['diploma_nummer']) ?></td>
            <td><?= htmlspecialchars($diploma['voornaam'] . ' ' . $diploma['achternaam']) ?></td>
            <td><?= htmlspecialchars($diploma['diploma_type_naam']) ?></td>
            <td><?= date('d-m-Y', strtotime($diploma['behaald_datum'])) ?></td>
            <td><?= date('d-m-Y', strtotime($diploma['geldig_tot'])) ?></td>
            <td>
                <?php 
                $today = new DateTime();
                $expiry = new DateTime($diploma['geldig_tot']);
                
                if ($diploma['is_ingetrokken']) {
                    echo '<span class="status-badge revoked">Ingetrokken</span>';
                } elseif ($diploma['is_verlengd']) {
                    echo '<span class="status-badge extended">Verlengd</span>';
                } elseif ($expiry < $today) {
                    echo '<span class="status-badge expired">Verlopen</span>';
                } else {
                    echo '<span class="status-badge valid">Geldig</span>';
                }
                ?>
            </td>
            <td>
                <a href="view.php?id=<?= $diploma['id'] ?>" class="btn">Bekijken</a>
                <a href="edit.php?id=<?= $diploma['id'] ?>" class="btn">Bewerken</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>