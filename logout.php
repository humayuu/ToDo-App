<?php
session_start();
if (isset($_SESSION['loggedIn']) == false) {
    header('Location: ../index.php?loginFirst=1');
    exit;
}


session_unset();

if (session_destroy()) {
    header('Location: index.php?logoutSuccess=1');
    exit;
}