<?php
// register.php - a new student creates an account and their student record
require 'config.php';

$error = '';
$courses = $db->query('SELECT * FROM courses ORDER BY code')->fetchAll(PDO::FETCH_ASSOC);
$d = ['username' => '', 'student_no' => '', 'full_name' => '', 'email' => '', 'course_id' => 0, 'year_level' => 1, 'address' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $d = [
        'username'   => trim($_POST['username']),
        'password'   => $_POST['password'],
        'student_no' => trim($_POST['student_no']),
        'full_name'  => trim($_POST['full_name']),
        'email'      => trim($_POST['email']),
        'course_id'  => (int) $_POST['course_id'],
        'year_level' => (int) $_POST['year_level'],
        'address'    => trim($_POST['address']),
    ];

    if ($_POST['password'] != $_POST['confirm']) {
        $error = 'Passwords do not match.';
    } else {
        $error = create_student($db, $d);
        if ($error == '') {
            header('Location: login.php?registered=1');
            exit;
        }
    }
}

include 'header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-7">
        <h3>Student Registration</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" id="registerForm">
            <div class="row">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" value="<?= e($d['username']) ?>" required minlength="3" maxlength="30">
                </div>
                <div class="mb-3">
                    <label class="form-label">ID Number</label>
                    <input type="text" name="student_no" id="student_no" class="form-control" value="<?= e($d['student_no']) ?>" placeholder="2026-0001" pattern="[0-9]{4}-[0-9]{4}" required>
                    <div class="form-text" id="studentNoMsg"></div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control" value="<?= e($d['full_name']) ?>" required maxlength="100">
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= e($d['email']) ?>" required>
            </div>
            <div class="row">
                <div class="mb-3">
                    <label class="form-label">Course</label>
                    <select name="course_id" class="form-select" required>
                        <option value="">-- choose course --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $d['course_id'] == $c['id'] ? 'selected': '' ?>>
                                <?= e($c['code']) ?> - <?= e($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Year Level</label>
                    <select name="year_level" class="form-select">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?= $i ?>" <?= $d['year_level'] == $i ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control" value="<?= e($d['address']) ?>" maxlength="200">
            </div>
            <div class="row">
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required minlength="6">
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm" id="confirm" class="form-control" required>
                </div>
            </div>
            <div class="text-danger mb-2" id="errorMsg"></div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
