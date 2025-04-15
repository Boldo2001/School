<?php
session_start();
if (!isset($_SESSION['gebruiker_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$diplomaId = $_GET['id'];

$query = "
    SELECT d.*, 
           p.voornaam, p.achternaam, p.geboortedatum,
           dt.naam AS diploma_type_naam, dt.geldigheid_jaren,
           e.naam AS examinator_naam, e.organisatie AS examinator_organisatie
    FROM diplomas d
    JOIN personen p ON d.persoon_id = p.id
    JOIN diploma_types dt ON d.diploma_type_id = dt.id
    JOIN examinateurs e ON d.examinator_id = e.id
    WHERE d.id = ?
";
$stmt = $pdo->prepare($query);
$stmt->execute([$diplomaId]);
$diploma = $stmt->fetch();

if (!$diploma) {
    echo '<div class="alert error">Diploma niet gevonden</div>';
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM resultaten WHERE diploma_id = ?");
$stmt->execute([$diplomaId]);
$resultaten = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Diploma Details</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            color: #fff;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .valid { background-color: #22c55e; }
        .almost { background-color: #f97316; }
        .expired { background-color: #ef4444; }
        .revoked { background-color: #6b7280; }
        .extended { background-color: #3b82f6; }
        .card h3 { margin-bottom: 10px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Diploma Details</h2>

    <?php
        $today = new DateTime();
        $expiry = new DateTime($diploma['geldig_tot']);
        $interval = $today->diff($expiry);
        $verloopt_binnenkort = $expiry > $today && $interval->days <= 60;
    ?>

    <div>
        <?php
        if ($diploma['is_ingetrokken']) {
            echo '<span class="status-badge revoked">Ingetrokken</span>';
        } elseif ($diploma['is_verlengd']) {
            echo '<span class="status-badge extended">Verlengd</span>';
        } elseif ($expiry < $today) {
            echo '<span class="status-badge expired">Verlopen</span>';
        } elseif ($verloopt_binnenkort) {
            echo '<span class="status-badge almost">Verloopt bijna</span>';
        } else {
            echo '<span class="status-badge valid">Geldig</span>';
        }
        ?>
    </div>

    <div class="card" style="margin-bottom: 30px;">
        <h3><?= htmlspecialchars($diploma['diploma_type_naam']) ?></h3>

        <div class="detail-group"><strong>Diploma Nummer:</strong> <?= htmlspecialchars($diploma['diploma_nummer']) ?></div>
        <div class="detail-group"><strong>Behaald door:</strong> <?= htmlspecialchars($diploma['voornaam'] . ' ' . $diploma['achternaam']) ?></div>
        <div class="detail-group"><strong>Geboortedatum:</strong> <?= date('d-m-Y', strtotime($diploma['geboortedatum'])) ?></div>
        <div class="detail-group"><strong>Behaald op:</strong> <?= date('d-m-Y', strtotime($diploma['behaald_datum'])) ?></div>
        <div class="detail-group"><strong>Geldig tot:</strong> <?= date('d-m-Y', strtotime($diploma['geldig_tot'])) ?></div>
        <div class="detail-group"><strong>Examinateur:</strong> <?= htmlspecialchars($diploma['examinator_naam']) ?> (<?= htmlspecialchars($diploma['examinator_organisatie']) ?>)</div>

        <?php if ($diploma['is_verlengd']): ?>
        <div class="detail-group">
            <strong>Origineel behaald op:</strong>
            <?php 
            $stmt = $pdo->prepare("SELECT behaald_datum FROM diplomas WHERE id = ?");
            $stmt->execute([$diploma['origineel_diploma_id']]);
            $origineel = $stmt->fetch();
            echo date('d-m-Y', strtotime($origineel['behaald_datum']));
            ?>
        </div>
        <?php endif; ?>

        <?php if ($diploma['is_ingetrokken']): ?>
        <div class="detail-group"><strong>Reden intrekking:</strong> <?= htmlspecialchars($diploma['intrekking_reden']) ?></div>
        <?php endif; ?>
    </div>

    <?php if (!empty($resultaten)): ?>
    <div class="card">
        <h3>Resultaten</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Onderdeel</th>
                    <th>Score</th>
                    <th>Opmerking</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultaten as $resultaat): ?>
                <tr>
                    <td><?= htmlspecialchars($resultaat['onderdeel']) ?></td>
                    <td><?= htmlspecialchars($resultaat['score']) ?></td>
                    <td><?= htmlspecialchars($resultaat['opmerking']) ?? '-' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <div style="margin-top: 30px;">
        <a href="index.php" class="btn">← Terug naar overzicht</a>
        <a href="edit.php?id=<?= $diploma['id'] ?>" class="btn">Bewerken</a>
    </div>
</div>
</body>
</html>
