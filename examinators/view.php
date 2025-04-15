<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/header.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$examinatorId = $_GET['id'];

// Haal examinator details op
$stmt = $pdo->prepare("SELECT * FROM examinateurs WHERE id = ?");
$stmt->execute([$examinatorId]);
$examinator = $stmt->fetch();

if (!$examinator) {
    echo '<div class="alert error">Examinator niet gevonden</div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Haal recente examens op voor deze examinator
$stmt = $pdo->prepare("
    SELECT d.*, p.voornaam, p.achternaam, dt.naam AS diploma_type_naam
    FROM diplomas d
    JOIN personen p ON d.persoon_id = p.id
    JOIN diploma_types dt ON d.diploma_type_id = dt.id
    WHERE d.examinator_id = ?
    ORDER BY d.behaald_datum DESC
    LIMIT 10
");
$stmt->execute([$examinatorId]);
$recenteExamens = $stmt->fetchAll();

// Tel diploma's per status
$statusCount = $pdo->prepare("
    SELECT 
        dt.naam AS type_naam,
        COUNT(d.id) AS aantal
    FROM diplomas d
    JOIN diploma_types dt ON d.diploma_type_id = dt.id
    WHERE d.examinator_id = ?
    GROUP BY dt.id
    ORDER BY aantal DESC
    LIMIT 5
");
$statusCount->execute([$examinatorId]);
$topTypes = $statusCount->fetchAll();
?>

<h2>Examinator: <?= htmlspecialchars($examinator['naam']) ?></h2>

<div class="detail-card">
    <div class="detail-row">
        <div class="detail-group">
            <label>Naam:</label>
            <p><?= htmlspecialchars($examinator['naam']) ?></p>
        </div>
        <div class="detail-group">
            <label>Organisatie:</label>
            <p><?= htmlspecialchars($examinator['organisatie'] ?? 'Onbekend') ?></p>
        </div>
    </div>

    <?php if (!empty($examinator['contact_info'])): ?>
    <div class="detail-group">
        <label>Contactinformatie:</label>
        <p><?= nl2br(htmlspecialchars($examinator['contact_info'])) ?></p>
    </div>
    <?php endif; ?>
</div>

<div class="stats-row">
    <div class="stat-card">
        <h3>Totaal Examens</h3>
        <p><?= count($recenteExamens) > 0 ? $recenteExamens[0]['aantal_examens'] : 0 ?></p>
    </div>
    <div class="stat-card">
        <h3>Eerste Examen</h3>
        <p><?= $recenteExamens ? date('d-m-Y', strtotime(end($recenteExamens)['behaald_datum'])) : 'N.v.t.' ?></p>
    </div>
    <div class="stat-card">
        <h3>Laatste Examen</h3>
        <p><?= $recenteExamens ? date('d-m-Y', strtotime($recenteExamens[0]['behaald_datum'])) : 'N.v.t.' ?></p>
    </div>
</div>

<?php if (!empty($topTypes)): ?>
<div class="detail-card">
    <h3>Meest Afgenomen Examens</h3>
    <ul class="chart-list">
        <?php foreach ($topTypes as $type): ?>
        <li>
            <span class="chart-label"><?= htmlspecialchars($type['type_naam']) ?></span>
            <span class="chart-bar" style="width: <?= ($type['aantal'] / max(array_column($topTypes, 'aantal')) * 100) ?>%">
                <span class="chart-value"><?= $type['aantal'] ?></span>
            </span>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<h3>Recente Examens</h3>

<?php if (count($recenteExamens) > 0): ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Diploma Nr</th>
                <th>Naam</th>
                <th>Diploma Type</th>
                <th>Datum</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recenteExamens as $examen): 
                $today = new DateTime();
                $expiry = new DateTime($examen['geldig_tot']);
            ?>
            <tr>
                <td><?= htmlspecialchars($examen['diploma_nummer']) ?></td>
                <td><?= htmlspecialchars($examen['voornaam'] . ' ' . $examen['achternaam']) ?></td>
                <td><?= htmlspecialchars($examen['diploma_type_naam']) ?></td>
                <td><?= date('d-m-Y', strtotime($examen['behaald_datum'])) ?></td>
                <td>
                    <?php 
                    if ($examen['is_ingetrokken']) {
                        echo '<span class="status-badge revoked">Ingetrokken</span>';
                    } elseif ($examen['is_verlengd']) {
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
    <div class="alert info">Geen examens gevonden voor deze examinator</div>
<?php endif; ?>

<div class="action-buttons">
    <a href="index.php" class="btn">Terug naar overzicht</a>
    <a href="edit.php?id=<?= $examinator['id'] ?>" class="btn">Bewerken</a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>