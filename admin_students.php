<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$error = null;
$success = null;

// Handle Add / Edit Student POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $room_id = empty($_POST['room_id']) ? NULL : (int)$_POST['room_id'];
    $username = trim($_POST['username']);
    $password_input = trim($_POST['password']);

    if ($action == 'add') {
        // Room capacity check
        if ($room_id) {
            $chk = $conn->query("SELECT capacity, occupied FROM rooms WHERE id = $room_id")->fetch_assoc();
            if ($chk['occupied'] >= $chk['capacity']) { $error = "Cannot assign room; it is completely full!"; $room_id = NULL; }
        }
        // Unique checks
        $chk_u = $conn->query("SELECT id FROM users WHERE username = '$username'");
        if ($chk_u->num_rows > 0) $error = "Username already exists! Please choose a different one.";
        $chk_e = $conn->query("SELECT id FROM students WHERE email = '$email'");
        if ($chk_e->num_rows > 0) $error = "Email address is already registered!";

        if (!$error) {
            try {
                $password = password_hash($password_input, PASSWORD_DEFAULT);
                $stmt_u = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'student')");
                $stmt_u->bind_param("ss", $username, $password);
                $stmt_u->execute();
                $user_id = $conn->insert_id;
                
                $stmt_s = $conn->prepare("INSERT INTO students (user_id, name, email, phone, room_id) VALUES (?, ?, ?, ?, ?)");
                $stmt_s->bind_param("isssi", $user_id, $name, $email, $phone, $room_id);
                $stmt_s->execute();
                
                if ($room_id) $conn->query("UPDATE rooms SET occupied = occupied + 1 WHERE id = $room_id");
                $success = "Student successfully registered and room accurately allocated!";
            } catch (mysqli_sql_exception $e) { $error = "Database Error: " . $e->getMessage(); }
        }
    } 
    elseif ($action == 'edit') {
        $student_id = (int)$_POST['student_id'];
        $curr = $conn->query("SELECT s.user_id, s.room_id, u.username FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = $student_id")->fetch_assoc();

        // Unique checks (exclude self)
        if ($username !== $curr['username']) {
            $chk_u = $conn->query("SELECT id FROM users WHERE username = '$username'");
            if ($chk_u->num_rows > 0) $error = "Username already taken!";
        }
        $chk_e = $conn->query("SELECT id FROM students WHERE email = '$email' AND id != $student_id");
        if ($chk_e->num_rows > 0) $error = "Email address already registered to another student!";

        // Handle Room Shift Logic gracefully
        if ($room_id !== $curr['room_id'] && $room_id !== NULL) {
            $chk = $conn->query("SELECT capacity, occupied FROM rooms WHERE id = $room_id")->fetch_assoc();
            if ($chk['occupied'] >= $chk['capacity']) { $error = "Cannot swap to that room; it is completely full!"; $room_id = $curr['room_id']; }
        }

        if (!$error) {
            try {
                // Update User Details
                if (!empty($password_input)) {
                    $hash = password_hash($password_input, PASSWORD_DEFAULT);
                    $stmt_u = $conn->prepare("UPDATE users SET username=?, password=? WHERE id=?");
                    $stmt_u->bind_param("ssi", $username, $hash, $curr['user_id']);
                } else {
                    $stmt_u = $conn->prepare("UPDATE users SET username=? WHERE id=?");
                    $stmt_u->bind_param("si", $username, $curr['user_id']);
                }
                $stmt_u->execute();

                // Update Student Details
                $stmt_s = $conn->prepare("UPDATE students SET name=?, email=?, phone=?, room_id=? WHERE id=?");
                $stmt_s->bind_param("sssii", $name, $email, $phone, $room_id, $student_id);
                $stmt_s->execute();

                // Adjust Room Occupancies if shifting
                if ($room_id != $curr['room_id']) {
                    if ($curr['room_id']) $conn->query("UPDATE rooms SET occupied = occupied - 1 WHERE id = " . $curr['room_id']);
                    if ($room_id) $conn->query("UPDATE rooms SET occupied = occupied + 1 WHERE id = $room_id");
                }
                $success = "Student records elegantly updated!";
            } catch (Exception $e) { $error = "Error updating: " . $e->getMessage(); }
        }
    }
}

// Handle Delete Student
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $s = $conn->query("SELECT room_id, user_id FROM students WHERE id = $del_id")->fetch_assoc();
    if ($s) {
        if ($s['room_id']) $conn->query("UPDATE rooms SET occupied = occupied - 1 WHERE id = " . $s['room_id']);
        $conn->query("DELETE FROM users WHERE id = " . $s['user_id']); 
    }
    header("Location: admin_students.php");
    exit();
}

// Edit Mode Setup
$edit_mode = false;
$e_stu = ['name'=>'', 'email'=>'', 'phone'=>'', 'room_id'=>'', 'username'=>''];
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $eid = (int)$_GET['edit'];
    $res = $conn->query("SELECT s.*, u.username FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = $eid");
    if($res->num_rows > 0) {
        $e_stu = $res->fetch_assoc();
    } else {
        $edit_mode = false;
    }
}

$page_title = "Manage Students";
include 'includes/header.php';
include 'includes/sidebar_admin.php';

$students = $conn->query("SELECT s.*, r.room_number FROM students s LEFT JOIN rooms r ON s.room_id = r.id ORDER BY s.id DESC");
$rooms = $conn->query("SELECT id, room_number, capacity, occupied FROM rooms");
?>

<main class="main-content">
    <header><h1>Central Student Directory</h1></header>

    <?php if($error): ?><p style="color: red; padding: 1rem;"> <?= $error ?> </p><?php endif; ?>
    <?php if($success): ?><p style="color: #4CAF50; padding: 1rem;"> <?= $success ?> </p><?php endif; ?>

    <div class="card" style="margin-top: 1rem;">
        <h2><?= $edit_mode ? "Modify Student Profile" : "Add New Student" ?></h2>
        
        <?php if($edit_mode): ?>
            <a href="admin_students.php" style="color:#e74c3c; font-size: 0.9rem; margin-bottom: 1rem; display:block;">&larr; Cancel Edits</a>
        <?php endif; ?>

        <form method="POST" action="admin_students.php" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
            <input type="hidden" name="action" value="<?= $edit_mode ? 'edit' : 'add' ?>">
            <?php if($edit_mode): ?><input type="hidden" name="student_id" value="<?= $eid ?>"><?php endif; ?>
            
            <div class="form-group">
                <label>Student Full Name</label>
                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($e_stu['name']) ?>">
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($e_stu['email']) ?>">
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" class="form-control" required value="<?= htmlspecialchars($e_stu['phone']) ?>">
            </div>
            <div class="form-group">
                <label>Assign/Change Room</label>
                <select name="room_id" class="form-control">
                    <option value="">-- No Room Assigned --</option>
                    <?php while($room = $rooms->fetch_assoc()): 
                        $avail = $room['capacity'] - $room['occupied'];
                        $sel = ($e_stu['room_id'] == $room['id']) ? 'selected' : '';
                        // Only show if available, OR if it's their current room
                        if ($avail > 0 || $sel == 'selected'):
                    ?>
                        <option value="<?= $room['id'] ?>" <?= $sel ?>>Room <?= $room['room_number'] ?> <?= $sel?'(Current)':'(Avail: '.$avail.')' ?></option>
                    <?php endif; endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Portal Username</label>
                <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($e_stu['username']) ?>">
            </div>
            <div class="form-group">
                <label><?= $edit_mode ? "Overwrite Password (Leave blank to keep)" : "Portal Password" ?></label>
                <input type="password" name="password" class="form-control" <?= $edit_mode ? '' : 'required' ?>>
            </div>
            <div style="grid-column: 1 / -1;">
                <button type="submit" class="btn"><?= $edit_mode ? "Save Revisions" : "Register Student" ?></button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Student Full Directory</h2>
        <div class="table-responsive">
            <table>
                <thead><tr><th>Name</th><th>Email</th><th>Room</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php while($s = $students->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['name']) ?></td>
                        <td><?= htmlspecialchars($s['email']) ?></td>
                        <td><?= $s['room_number'] ? $s['room_number'] : '<span style="color:#e74c3c">Unassigned</span>' ?></td>
                        <td>
                            <a href="admin_students.php?edit=<?= $s['id'] ?>" class="btn" style="background:#f39c12; padding: 0.25rem 0.5rem; font-size:0.8rem; margin-right: 0.5rem;">Edit Profile</a>
                            <a href="admin_students.php?delete=<?= $s['id'] ?>" class="btn" style="background:#e74c3c; padding: 0.25rem 0.5rem; font-size:0.8rem;" onclick="return confirm('WARNING: Are you sure? This PERMANENTLY deletes their user account, financial fees, and complaints!');">Remove</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
