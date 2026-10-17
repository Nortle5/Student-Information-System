<?php
require 'config.php';
need_login();

if ($_SESSION['role'] == 'admin') {
    header('Location: students.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $error = 'Please enter a valid email.';
    } elseif (strlen($address) > 200) {
        $error = 'Address is too long (max 200 characters).';
    } else {
        // make sure nobody else is using the email
        $stmt = $db->prepare('SELECT id FROM students WHERE email = ? AND user_id != ?');
        $stmt->execute([$email, $_SESSION['user_id']]);

        if ($stmt->fetch()) {
            $error = 'That email is used by another student.';
        } else {
            $stmt = $db->prepare('UPDATE students SET email = ?, address = ? WHERE user_id = ?');
            $stmt->execute([$email, $address, $_SESSION['user_id']]);
            $success = 'Profile updated.';
        }
    }
}

$stmt = $db->prepare(
    'SELECT students.*, courses.code, courses.name AS course_name
     FROM students JOIN courses ON students.course_id = courses.id
     WHERE students.user_id = ?'
);
$stmt->execute([$_SESSION['user_id']]);
$s = $stmt->fetch(PDO::FETCH_ASSOC);

include 'header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-7">
        <h3>Profile</h3>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="card mb-3">
            <div class="card-body">
                <p class="mb-1"><b>Name:</b> <?= e($s['full_name']) ?></p>
                <p class="mb-1"><b>Student Number:</b> <?= e($s['student_no']) ?></p>
                <p class="mb-1"><b>Course:</b> <?= e($s['code']) ?> - <?= e($s['course_name']) ?></p>
                <p class="mb-0"><b>Year Level:</b> <?= e($s['year_level']) ?></p>
            </div>
        </div>

        <h5>Update Information</h5>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= e($s['email']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control" value="<?= e($s['address']) ?>" maxlength="200">
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
