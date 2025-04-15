<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/header.php';

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

<div class="container">
    <h2>Diploma Details</h2>

    <div class="card" style="margin-bottom: 30px;">
        <h3><?= htmlspecialchars($diploma['diploma_type_naam']) ?></h3>

        <div class="detail-group"><strong>Diploma Nummer:</strong> <?= htmlspecialchars($diploma['diploma_nummer']) ?></div>

        <div class="detail-group"><strong>Status:</strong>
            <?php 
            $today = new DateTime();
            $expiry = new DateTime($diploma['geldig_tot']);
            if ($diploma['is_ingetrokken']) {
                echo '<span class="badge red">Ingetrokken</span>';
            } elseif ($diploma['is_verlengd']) {
                echo '<span class="badge green">Verlengd</span>';
            } elseif ($expiry < $today) {
                echo '<span class="badge red">Verlopen</span>';
            } else {
                echo '<span class="badge green">Geldig</span>';
            }
            ?>
        </div>

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
            ?>
            <?= date('d-m-Y', strtotime($origineel['behaald_datum'])) ?>
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
