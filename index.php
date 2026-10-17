<?php
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
} elseif ($_SESSION['role'] == 'admin') {
    header('Location: students.php');
} else {
    header('Location: profile.php');
}
exit;
