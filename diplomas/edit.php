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

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$diplomaId = $_GET['id'];

// Haal diploma gegevens op
$stmt = $pdo->prepare("
    SELECT d.*, 
           p.voornaam, p.achternaam,
           dt.naam AS diploma_type_naam,
           e.naam AS examinator_naam
    FROM diplomas d
    JOIN personen p ON d.persoon_id = p.id
    JOIN diploma_types dt ON d.diploma_type_id = dt.id
    JOIN examinateurs e ON d.examinator_id = e.id
    WHERE d.id = ?
");
$stmt->execute([$diplomaId]);
$diploma = $stmt->fetch();

if (!$diploma) {
    echo '<div class="alert error">Diploma niet gevonden</div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Verwerk formulier verzending
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $behaald_datum = $_POST['behaald_datum'];
    $geldig_tot = $_POST['geldig_tot'];
    $is_ingetrokken = isset($_POST['is_ingetrokken']) ? 1 : 0;
    $intrekking_reden = $_POST['intrekking_reden'] ?? null;

    $stmt = $pdo->prepare("
        UPDATE diplomas 
        SET behaald_datum = ?, 
            geldig_tot = ?,
            is_ingetrokken = ?,
            intrekking_reden = ?
        WHERE id = ?
    ");
    $stmt->execute([$behaald_datum, $geldig_tot, $is_ingetrokken, $intrekking_reden, $diplomaId]);
    
    header('Location: view.php?id=' . $diplomaId);
    exit;
}
?>

<h2>Diploma Bewerken: <?= htmlspecialchars($diploma['diploma_type_naam']) ?></h2>

<form method="POST">
    <div class="form-row">
        <div class="form-group">
            <label>Diploma Nummer</label>
            <input type="text" value="<?= htmlspecialchars($diploma['diploma_nummer']) ?>" readonly>
        </div>
        <div class="form-group">
            <label>Persoon</label>
            <input type="text" value="<?= htmlspecialchars($diploma['voornaam'] . ' ' . $diploma['achternaam']) ?>" readonly>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Behaald op</label>
            <input type="date" name="behaald_datum" value="<?= htmlspecialchars($diploma['behaald_datum']) ?>" required>
        </div>
        <div class="form-group">
            <label>Geldig tot</label>
            <input type="date" name="geldig_tot" value="<?= htmlspecialchars($diploma['geldig_tot']) ?>" required>
        </div>
    </div>

    <div class="form-group">
        <label>Examinateur</label>
        <input type="text" value="<?= htmlspecialchars($diploma['examinator_naam']) ?>" readonly>
    </div>

    <div class="form-group checkbox">
        <input type="checkbox" name="is_ingetrokken" id="is_ingetrokken" <?= $diploma['is_ingetrokken'] ? 'checked' : '' ?>>
        <label for="is_ingetrokken">Ingetrokken</label>
    </div>

    <div class="form-group" id="revocationReasonGroup" style="<?= $diploma['is_ingetrokken'] ? '' : 'display:none;' ?>">
        <label>Reden intrekking</label>
        <textarea name="intrekking_reden"><?= htmlspecialchars($diploma['intrekking_reden']) ?></textarea>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">Opslaan</button>
        <a href="view.php?id=<?= $diploma['id'] ?>" class="btn">Annuleren</a>
    </div>
</form>

<script>
// Toon/verberg reden van intrekking bij checkbox change
document.getElementById('is_ingetrokken').addEventListener('change', function() {
    document.getElementById('revocationReasonGroup').style.display = this.checked ? 'block' : 'none';
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>