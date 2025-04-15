<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/header.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$diplomaId = $_GET['id'];

// Haal diploma details op
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
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Haal resultaten op
$stmt = $pdo->prepare("SELECT * FROM resultaten WHERE diploma_id = ?");
$stmt->execute([$diplomaId]);
$resultaten = $stmt->fetchAll();
?>

<h2>Diploma Details</h2>

<div class="detail-card">
    <h3><?= htmlspecialchars($diploma['diploma_type_naam']) ?></h3>
    
    <div class="detail-row">
        <div class="detail-group">
            <label>Diploma Nummer:</label>
            <p><?= htmlspecialchars($diploma['diploma_nummer']) ?></p>
        </div>
        <div class="detail-group">
            <label>Status:</label>
            <?php 
            $today = new DateTime();
            $expiry = new DateTime($diploma['geldig_tot']);
            
            if ($diploma['is_ingetrokken']) {
                echo '<p class="status-badge revoked">Ingetrokken</p>';
            } elseif ($diploma['is_verlengd']) {
                echo '<p class="status-badge extended">Verlengd</p>';
            } elseif ($expiry < $today) {
                echo '<p class="status-badge expired">Verlopen</p>';
            } else {
                echo '<p class="status-badge valid">Geldig</p>';
            }
            ?>
        </div>
    </div>

    <div class="detail-row">
        <div class="detail-group">
            <label>Behaald door:</label>
            <p><?= htmlspecialchars($diploma['voornaam'] . ' ' . $diploma['achternaam']) ?></p>
        </div>
        <div class="detail-group">
            <label>Geboortedatum:</label>
            <p><?= date('d-m-Y', strtotime($diploma['geboortedatum'])) ?></p>
        </div>
    </div>

    <div class="detail-row">
        <div class="detail-group">
            <label>Behaald op:</label>
            <p><?= date('d-m-Y', strtotime($diploma['behaald_datum'])) ?></p>
        </div>
        <div class="detail-group">
            <label>Geldig tot:</label>
            <p><?= date('d-m-Y', strtotime($diploma['geldig_tot'])) ?></p>
        </div>
    </div>

    <div class="detail-group">
        <label>Examinateur:</label>
        <p><?= htmlspecialchars($diploma['examinator_naam']) ?> (<?= htmlspecialchars($diploma['examinator_organisatie']) ?>)</p>
    </div>

    <?php if ($diploma['is_verlengd']): ?>
    <div class="detail-group">
        <label>Origineel behaald op:</label>
        <?php 
        $stmt = $pdo->prepare("SELECT behaald_datum FROM diplomas WHERE id = ?");
        $stmt->execute([$diploma['origineel_diploma_id']]);
        $origineel = $stmt->fetch();
        ?>
        <p><?= date('d-m-Y', strtotime($origineel['behaald_datum'])) ?></p>
    </div>
    <?php endif; ?>

    <?php if ($diploma['is_ingetrokken']): ?>
    <div class="detail-group">
        <label>Reden intrekking:</label>
        <p><?= htmlspecialchars($diploma['intrekking_reden']) ?></p>
    </div>
    <?php endif; ?>
</div>

<?php if (!empty($resultaten)): ?>
<div class="detail-card">
    <h3>Resultaten</h3>
    <table class="result-table">
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

<div class="action-buttons">
    <a href="index.php" class="btn">Terug naar overzicht</a>
    <a href="edit.php?id=<?= $diploma['id'] ?>" class="btn">Bewerken</a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>