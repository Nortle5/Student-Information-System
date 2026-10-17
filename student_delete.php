<?php
require 'config.php';
need_admin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int) $_POST['id'];

    $stmt = $db->prepare('DELETE FROM users WHERE id = (SELECT user_id FROM students WHERE id = ?)');
    $stmt->execute([$id]);
}

header('Location: students.php?msg=' . urlencode('Student deleted.'));
exit;
