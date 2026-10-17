// script.js - client side code

// 1. Register form: check the passwords before submitting
var registerForm = document.getElementById('registerForm');
if (registerForm) {
    registerForm.addEventListener('submit', function (e) {
        var pass = document.getElementById('password').value;
        var confirm = document.getElementById('confirm').value;
        var msg = document.getElementById('errorMsg');

        if (pass.length < 6) {
            msg.textContent = 'Password must be at least 6 characters.';
            e.preventDefault();
        } else if (pass !== confirm) {
            msg.textContent = 'Passwords do not match.';
            e.preventDefault();
        }
    });
}

// 2. Register form: check if the student number is available (AJAX)
var studentNo = document.getElementById('student_no');
if (studentNo) {
    studentNo.addEventListener('blur', function () {
        var msg = document.getElementById('studentNoMsg');
        var value = studentNo.value.trim();

        if (value == '') {
            msg.textContent = '';
            return;
        }

        fetch('check_student.php?student_no=' + encodeURIComponent(value))
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.valid) {
                    msg.textContent = 'Format must be like 2026-0001.';
                    msg.className = 'form-text text-danger';
                } else if (!data.available) {
                    msg.textContent = 'This student number is already registered.';
                    msg.className = 'form-text text-danger';
                } else {
                    msg.textContent = 'Student number is available.';
                    msg.className = 'form-text text-success';
                }
            });
    });
}

// 3. Students page: live search (hides rows that do not match)
var searchBox = document.getElementById('searchBox');
if (searchBox) {
    searchBox.addEventListener('input', function () {
        var text = searchBox.value.toLowerCase();
        var rows = document.querySelectorAll('#studentTable tbody tr');

        rows.forEach(function (row) {
            if (row.textContent.toLowerCase().indexOf(text) > -1) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
}
