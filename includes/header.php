<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diploma Verificatie Systeem</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
</head>
<body>
    <header>
        <h1>Diploma Verificatie Systeem</h1>
        <nav>
            <a href="<?php echo BASE_URL; ?>" <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : ''; ?>>Dashboard</a>

            <?php if (isset($_SESSION['ingelogd'])): ?>
                <a href="<?php echo BASE_URL; ?>diplomas/" <?php echo strpos($_SERVER['PHP_SELF'], 'diplomas/') !== false ? 'class="active"' : ''; ?>>Diploma's</a>
                <a href="<?php echo BASE_URL; ?>personen/" <?php echo strpos($_SERVER['PHP_SELF'], 'personen/') !== false ? 'class="active"' : ''; ?>>Personen</a>
                <a href="<?php echo BASE_URL; ?>diploma_types/" <?php echo strpos($_SERVER['PHP_SELF'], 'diploma_types/') !== false ? 'class="active"' : ''; ?>>Diploma Types</a>
                <a href="<?php echo BASE_URL; ?>examinators/" <?php echo strpos($_SERVER['PHP_SELF'], 'examinators/') !== false ? 'class="active"' : ''; ?>>Examinateurs</a>
                <a href="<?php echo BASE_URL; ?>logout.php">Uitloggen</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>login.php">Inloggen</a>
            <?php endif; ?>
        </nav>
    </header>
