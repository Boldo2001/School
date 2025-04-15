<?php
session_start();
session_unset();
session_destroy();
header("Location: index.php"); // terug naar dashboard voor niet-ingelogde gebruiker
exit;
?>
