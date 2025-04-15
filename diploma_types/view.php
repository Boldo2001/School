<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/header.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$typeId = $_GET['id'];

// Haal diploma type details op
$stmt = $pdo->prepare("SELECT * FROM diploma_types WHERE id = ?");
$stmt->execute([$typeId]);
$diplomaType = $stmt->fetch();

if (!$diplomaType) {
    echo '<div class="alert error">Diploma type niet gevonden</div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Haal recente diploma's op voor dit type
$stmt = $pdo->prepare("
    SELECT d.*, p.voornaam, p.achternaam
    FROM diplomas d
    JOIN personen p ON d.persoon_id = p.id
    WHERE d.diploma_type_id = ?
    ORDER BY d.behaald_datum DESC
    LIMIT 10
");
$stmt->execute([$typeId]);
$recenteDiplomas = $stmt->fetchAll();

// Tel diploma's per status
$statusCount = $pdo->prepare("
    SELECT 
        SUM(CASE WHEN geldig_tot >= CURDATE() AND is_ingetrokken = 0 THEN 1 ELSE 0 END) AS geldig,
        SUM(CASE WHEN geldig_tot < CURDATE() AND is_ingetrokken = 0 THEN 1 ELSE 0 END) AS verlopen,
        SUM(CASE WHEN is_ingetrokken = 1 THEN 1 ELSE 0 END) AS ingetrokken
    FROM diplomas
    WHERE diploma_type_id = ?
");
$statusCount->execute([$typeId]);
$status = $statusCount->fetch();
?>

<h2>Diploma Type: <?= htmlspecialchars($diplomaType['naam']) ?></h2>

<div class="detail-card">
    <div class="detail-row">
        <div class="detail-group">
            <label>Naam:</label>
            <p><?= htmlspecialchars($diplomaType['naam']) ?></p>
        </div>
        <div class="detail-group">
            <label>Geldigheid:</label>
            <p><?= $diplomaType['geldigheid_jaren'] ?> jaar</p>
        </div>
    </div>

    <?php if (!empty($diplomaType['beschrijving'])): ?>
    <div class="detail-group">
        <label>Beschrijving:</label>
        <p><?= nl2br(htmlspecialchars($diplomaType['beschrijving'])) ?></p>
    </div>
    <?php endif; ?>
</div>

<div class="stats-row">
    <div class="stat-card">
        <h3>Totaal Uitgereikt</h3>
        <p><?= $status['geldig'] + $status['verlopen'] + $status['ingetrokken'] ?></p>
    </div>
    <div class="stat-card valid">
        <h3>Geldig</h3>
        <p><?= $status['geldig'] ?></p>
    </div>
    <div class="stat-card expired">
        <h3>Verlopen</h3>
        <p><?= $status['verlopen'] ?></p>
    </div>
    <div class="stat-card revoked">
        <h3>Ingetrokken</h3>
        <p><?= $status['ingetrokken'] ?></p>
    </div>
</div>

<h3>Recente Diploma's</h3>

<?php if (count($recenteDiplomas) > 0): ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Diploma Nr</th>
                <th>Naam</th>
                <th>Behaald op</th>
                <th>Geldig tot</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recenteDiplomas as $diploma): 
                $today = new DateTime();
                $expiry = new DateTime($diploma['geldig_tot']);
            ?>
            <tr>
                <td><?= htmlspecialchars($diploma['diploma_nummer']) ?></td>
                <td><?= htmlspecialchars($diploma['voornaam'] . ' ' . $diploma['achternaam']) ?></td>
                <td><?= date('d-m-Y', strtotime($diploma['behaald_datum'])) ?></td>
                <td><?= date('d-m-Y', strtotime($diploma['geldig_tot'])) ?></td>
                <td>
                    <?php 
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
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="alert info">Er zijn nog geen diploma's van dit type uitgereikt</div>
<?php endif; ?>

<div class="action-buttons">
    <a href="index.php" class="btn">Terug naar overzicht</a>
    <a href="edit.php?id=<?= $diplomaType['id'] ?>" class="btn">Bewerken</a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>