<?php
$host = 'localhost';
$dbname = 'studentinfo';
$dbuser = 'root';
$dbpass = '';

session_start();

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection failed. Check config.php');
}

function e($text) {
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

function need_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function need_admin() {
    need_login();
    if ($_SESSION['role'] != 'admin') {
        die('Access denied. Admins only.');
    }
}

function check_student_fields($d) {
    if (strlen($d['full_name']) < 3 || strlen($d['full_name']) > 100) {
        return 'Full name must be 3 to 100 characters.';
    }
    if (!preg_match('/^[0-9]{4}-[0-9]{4}$/', $d['student_no'])) {
        return 'Student number must look like 2026-0001.';
    }
    if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL) || strlen($d['email']) > 100) {
        return 'Please enter a valid email.';
    }
    if ($d['course_id'] < 1) {
        return 'Please choose a course.';
    }
    if ($d['year_level'] < 1 || $d['year_level'] > 5) {
        return 'Year level must be from 1 to 5.';
    }
    if (strlen($d['address']) > 200) {
        return 'Address is too long (max 200 characters).';
    }
    return '';
}

function create_student($db, $d) {
    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $d['username'])) {
        return 'Username must be 3 to 30 characters (letters, numbers, underscore).';
    }
    if (strlen($d['password']) < 6) {
        return 'Password must be at least 6 characters.';
    }

    $error = check_student_fields($d);
    if ($error != '') {
        return $error;
    }

    $stmt = $db->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute([$d['username']]);
    if ($stmt->fetch()) {
        return 'Username is already taken.';
    }
    $stmt = $db->prepare('SELECT id FROM students WHERE student_no = ? OR email = ?');
    $stmt->execute([$d['student_no'], $d['email']]);
    if ($stmt->fetch()) {
        return 'Student number or email is already registered.';
    }

    $db->beginTransaction();
    $hash = password_hash($d['password'], PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'student')");
    $stmt->execute([$d['username'], $hash]);
    $user_id = $db->lastInsertId();

    $stmt = $db->prepare('INSERT INTO students (user_id, student_no, full_name, email, course_id, year_level, address) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$user_id, $d['student_no'], $d['full_name'], $d['email'], $d['course_id'], $d['year_level'], $d['address']]);
    $db->commit();

    return '';
}
