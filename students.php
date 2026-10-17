<?php
require 'config.php';
need_admin();

$course = (int) ($_GET['course'] ?? 0);
$courses = $db->query('SELECT * FROM courses ORDER BY code')->fetchAll(PDO::FETCH_ASSOC);

$sql = 'SELECT students.*, courses.code
        FROM students JOIN courses ON students.course_id = courses.id';
$params = [];

if ($course > 0) {
    $sql .= ' WHERE students.course_id = ?';
    $params[] = $course;
}
$sql .= ' ORDER BY students.student_no';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Students (<?= count($students) ?>)</h3>
    <a href="student_form.php" class="btn btn-primary">+ Add Student</a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><?= e($_GET['msg']) ?></div>
<?php endif; ?>

<form method="get" class="row g-2 mb-3">
    <div class="col-12 col-md-6">
        <input type="text" id="searchBox" class="form-control" placeholder="Search by name, number or email...">
    </div>
    <div class="col-8 col-md-4">
        <select name="course" class="form-select">
            <option value="0">All courses</option>
            <?php foreach ($courses as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $course == $c['id'] ? 'selected' : '' ?>><?= e($c['code']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-4 col-md-2">
        <button type="submit" class="btn btn-secondary w-100">Filter</button>
    </div>
</form>

<div class="table-responsive">
<table class="table table-bordered bg-white" id="studentTable">
    <thead>
        <tr>
            <th>Student No.</th>
            <th>Name</th>
            <th>Email</th>
            <th>Course</th>
            <th>Year</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php if (count($students) == 0): ?>
        <tr><td colspan="6" class="text-center">No students found.</td></tr>
    <?php endif; ?>

    <?php foreach ($students as $s): ?>
        <tr>
            <td><?= e($s['student_no']) ?></td>
            <td><?= e($s['full_name']) ?></td>
            <td><?= e($s['email']) ?></td>
            <td><?= e($s['code']) ?></td>
            <td><?= e($s['year_level']) ?></td>
            <td class="text-nowrap">
                <a href="student_form.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                <form method="post" action="student_delete.php" class="d-inline" onsubmit="return confirm('Delete this student and their account?');">
                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php include 'footer.php'; ?>
