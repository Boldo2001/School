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
session_start();
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/header.php';
?>

<section class="search-box">
    <h2>Diploma Verificatie</h2>

    <?php if (isset($_SESSION['ingelogd'])): ?>
        <p style="color: green;">Welkom terug, <?= htmlspecialchars($_SESSION['gebruikersnaam']) ?>!</p>
    <?php else: ?>
        <p style="color: gray;">Je bent niet ingelogd. Alleen diploma verificatie is beschikbaar.</p>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="identifier" placeholder="Diploma nummer of naam" required>
        <input type="text" name="securityCode" placeholder="Security code (optioneel)">
        <button type="submit">Verifiëren</button>
    </form>
</section>

<?php
// Verificatie formulier verwerking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = $_POST['identifier'] ?? '';
    $securityCode = $_POST['securityCode'] ?? '';
    
    // Zoek diploma
    $stmt = $pdo->prepare("
        SELECT d.*, p.voornaam, p.achternaam, dt.naam AS diploma_type_naam, e.naam AS examinator_naam
        FROM diplomas d
        JOIN personen p ON d.persoon_id = p.id
        JOIN diploma_types dt ON d.diploma_type_id = dt.id
        JOIN examinateurs e ON d.examinator_id = e.id
        WHERE d.diploma_nummer = :identifier OR p.achternaam LIKE :achternaam
    ");
    $stmt->execute([
        ':identifier' => $identifier,
        ':achternaam' => "%$identifier%"
    ]);
    $diploma = $stmt->fetch();
    
    if ($diploma) {
        // Haal resultaten op als security code klopt
        $resultaten = [];
        if (!empty($securityCode) && $securityCode === $diploma['security_code']) {
            $stmt = $pdo->prepare("SELECT * FROM resultaten WHERE diploma_id = ?");
            $stmt->execute([$diploma['id']]);
            $resultaten = $stmt->fetchAll();
        }
        
        // Toon resultaat
        echo '<div class="verification-result">';
        echo '<h2>Verificatie Resultaat</h2>';
        echo '<div class="result-card">';
        echo '<h3>' . htmlspecialchars($diploma['diploma_type_naam']) . '</h3>';
        echo '<p>Behaald door: ' . htmlspecialchars($diploma['voornaam'] . ' ' . $diploma['achternaam']) . '</p>';
        echo '<p>Behaald op: ' . date('d-m-Y', strtotime($diploma['behaald_datum'])) . '</p>';
        echo '<p>Geldig tot: ' . date('d-m-Y', strtotime($diploma['geldig_tot'])) . '</p>';
        
        if ($diploma['is_verlengd']) {
            $stmt = $pdo->prepare("SELECT behaald_datum FROM diplomas WHERE id = ?");
            $stmt->execute([$diploma['origineel_diploma_id']]);
            $origineel = $stmt->fetch();
            echo '<p>Verlengd van origineel behaald op ' . date('d-m-Y', strtotime($origineel['behaald_datum'])) . '</p>';
        }
        
        if ($diploma['is_ingetrokken']) {
            echo '<p class="revoked">Dit diploma is ingetrokken. Reden: ' . htmlspecialchars($diploma['intrekking_reden']) . '</p>';
        } elseif (date('Y-m-d') > $diploma['geldig_tot']) {
            echo '<p class="expired">Dit diploma is verlopen</p>';
        }
        
        echo '</div>';
        
        // Toon details als security code correct is
        if (!empty($resultaten)) {
            echo '<div class="result-card">';
            echo '<h3>Details</h3>';
            echo '<p>Examinateur: ' . htmlspecialchars($diploma['examinator_naam']) . '</p>';
            echo '<h4>Resultaten</h4>';
            echo '<table>';
            echo '<thead><tr><th>Onderdeel</th><th>Score</th></tr></thead>';
            echo '<tbody>';
            
            foreach ($resultaten as $resultaat) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($resultaat['onderdeel']) . '</td>';
                echo '<td>' . htmlspecialchars($resultaat['score']) . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody></table></div>';
        }
        
        echo '</div>';
    } else {
        echo '<div class="alert error">Geen diploma gevonden met deze zoekopdracht</div>';
    }
}
?>

<?php require_once 'includes/footer.php'; ?>
</body>
</html>
