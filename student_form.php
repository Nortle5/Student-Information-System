<?php
require 'config.php';
need_admin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$courses = $db->query('SELECT * FROM courses ORDER BY code')->fetchAll(PDO::FETCH_ASSOC);
$d = ['username' => '', 'student_no' => '', 'full_name' => '', 'email' => '', 'course_id' => 0, 'year_level' => 1, 'address' => ''];

if ($id > 0) {
    $stmt = $db->prepare('SELECT * FROM students WHERE id = ?');
    $stmt->execute([$id]);
    $d = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$d) {
        header('Location: students.php');
        exit;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $d = [
        'username'   => trim($_POST['username'] ?? ''),
        'password'   => $_POST['password'] ?? '',
        'student_no' => trim($_POST['student_no']),
        'full_name'  => trim($_POST['full_name']),
        'email'      => trim($_POST['email']),
        'course_id'  => (int) $_POST['course_id'],
        'year_level' => (int) $_POST['year_level'],
        'address'    => trim($_POST['address']),
    ];

    if ($id == 0) {
        $error = create_student($db, $d);
        if ($error == '') {
            header('Location: students.php?msg=' . urlencode('Student added.'));
            exit;
        }
    } else {
        $error = check_student_fields($d);

        if ($error == '') {
            $stmt = $db->prepare('SELECT id FROM students WHERE (student_no = ? OR email = ?) AND id != ?');
            $stmt->execute([$d['student_no'], $d['email'], $id]);
            if ($stmt->fetch()) {
                $error = 'Student number or email is already used by another student.';
            }
        }

        if ($error == '') {
            $stmt = $db->prepare('UPDATE students SET student_no = ?, full_name = ?, email = ?, course_id = ?, year_level = ?, address = ? WHERE id = ?');
            $stmt->execute([$d['student_no'], $d['full_name'], $d['email'], $d['course_id'], $d['year_level'], $d['address'], $id]);
            header('Location: students.php?msg=' . urlencode('Student updated.'));
            exit;
        }
    }
}

include 'header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-7">
        <h3><?= $id > 0 ? 'Edit Student' : 'Add Student' ?></h3>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <?php if ($id == 0): ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" value="<?= e($d['username']) ?>" required minlength="3" maxlength="30">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label">Student Number</label>
                <input type="text" name="student_no" class="form-control" value="<?= e($d['student_no']) ?>" placeholder="2026-0001" pattern="[0-9]{4}-[0-9]{4}" required>
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
                <div class="col-md-8 mb-3">
                    <label class="form-label">Course</label>
                    <select name="course_id" class="form-select" required>
                        <option value="">-- choose course --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $d['course_id'] == $c['id'] ? 'selected' : '' ?>>
                                <?= e($c['code']) ?> - <?= e($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
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
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="students.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
