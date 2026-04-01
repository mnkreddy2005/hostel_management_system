<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $room_num = trim($_POST['room_number']);
    $cap = (int)$_POST['capacity'];
    
    if ($action == 'add') {
        $chk = $conn->prepare("SELECT id FROM rooms WHERE room_number = ?");
        $chk->bind_param("s", $room_num);
        $chk->execute();
        
        if ($chk->get_result()->num_rows > 0) {
            $error = "Room number already exists!";
        } else {
            $stmt = $conn->prepare("INSERT INTO rooms (room_number, capacity) VALUES (?, ?)");
            $stmt->bind_param("si", $room_num, $cap);
            $stmt->execute();
            $success = "Room successfully injected.";
        }
    } 
    elseif ($action == 'edit') {
        $room_id = (int)$_POST['room_id'];
        $manual_occ = (int)$_POST['occupied'];
        
        $chk = $conn->prepare("SELECT id FROM rooms WHERE room_number = ? AND id != ?");
        $chk->bind_param("si", $room_num, $room_id);
        $chk->execute();
        
        if ($chk->get_result()->num_rows > 0) {
            $error = "Another room already has this number!";
        } else {
            if ($manual_occ > $cap) $manual_occ = $cap;
            if ($manual_occ < 0) $manual_occ = 0;
            
            $stmt = $conn->prepare("UPDATE rooms SET room_number=?, capacity=?, occupied=? WHERE id=?");
            $stmt->bind_param("siii", $room_num, $cap, $manual_occ, $room_id);
            $stmt->execute();
            $success = "Room framework effectively updated.";
        }
    }
}

// Handle Room Deletion safely
if (isset($_GET['delete'])) {
    $del = (int)$_GET['delete'];
    $chk = $conn->query("SELECT occupied, room_number FROM rooms WHERE id = $del")->fetch_assoc();
    if ($chk) {
        if ($chk['occupied'] > 0) {
            $error = "Refused! Room " . $chk['room_number'] . " is currently occupied by $chk[occupied] student(s). Reassign them first.";
        } else {
            $conn->query("DELETE FROM rooms WHERE id = $del");
            $success = "Room " . $chk['room_number'] . " permanently deleted.";
        }
    }
}

$edit_mode = false;
$e_room = ['room_number'=>'', 'capacity'=>'', 'occupied'=>'0'];
$eid = null;
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $eid = (int)$_GET['edit'];
    $r = $conn->query("SELECT * FROM rooms WHERE id = $eid");
    if($r->num_rows > 0) {
        $e_room = $r->fetch_assoc();
    } else {
        $edit_mode = false;
    }
}

$page_title = "Manage Rooms";
include 'includes/header.php';
include 'includes/sidebar_admin.php';

$rooms = $conn->query("SELECT * FROM rooms ORDER BY room_number ASC");
?>

<main class="main-content">
    <header><h1>Facility Management</h1></header>

    <?php if($error): ?><p style="color: red; padding: 1rem;"> <?= $error ?> </p><?php endif; ?>
    <?php if($success): ?><p style="color: #4CAF50; padding: 1rem;"> <?= $success ?> </p><?php endif; ?>

    <div class="card" style="margin-top: 2rem;">
        <h2><?= $edit_mode ? "Modify Room Specifications" : "Provision New Room" ?></h2>
        
        <?php if($edit_mode): ?>
            <a href="admin_rooms.php" style="color:#e74c3c; font-size: 0.9rem; margin-bottom: 1rem; display:block;">&larr; Cancel Edits</a>
        <?php endif; ?>

        <form method="POST" action="admin_rooms.php" style="display:flex; gap: 1rem; margin-top: 1rem; align-items: flex-end;">
            <input type="hidden" name="action" value="<?= $edit_mode ? 'edit' : 'add' ?>">
            <?php if($edit_mode): ?><input type="hidden" name="room_id" value="<?= $eid ?>"><?php endif; ?>

            <div class="form-group" style="margin:0;">
                <label>Room Number</label>
                <input type="text" name="room_number" class="form-control" required value="<?= htmlspecialchars($e_room['room_number']) ?>">
            </div>
            <div class="form-group" style="margin:0;">
                <label>Total Capacity (Beds)</label>
                <input type="number" name="capacity" class="form-control" required min="1" max="15" value="<?= $e_room['capacity'] ?>">
            </div>
            
            <?php if($edit_mode): ?>
            <div class="form-group" style="margin:0;">
                <label>Manual Occupancy Tracker</label>
                <input type="number" name="occupied" class="form-control" required min="0" value="<?= $e_room['occupied'] ?>">
            </div>
            <?php endif; ?>
            
            <button type="submit" class="btn"><?= $edit_mode ? "Save Revisions" : "Integrate Room" ?></button>
        </form>
    </div>

    <div class="card">
        <h2>Hostel Availability Overview</h2>
        <div class="table-responsive">
            <table>
                <thead><tr><th>Room Number</th><th>Max Beds</th><th>Currently Filled</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php while($r = $rooms->fetch_assoc()): ?>
                    <tr>
                        <td style="font-weight:bold; color:#2C3E50;"><?= htmlspecialchars($r['room_number']) ?></td>
                        <td><?= $r['capacity'] ?> Beds</td>
                        <td><?= $r['occupied'] ?> Beds</td>
                        <td>
                            <?php if($r['occupied'] >= $r['capacity']): ?>
                                <span class="badge" style="background:#e74c3c;">Full</span>
                            <?php else: ?>
                                <span class="badge paid"><?= $r['capacity'] - $r['occupied'] ?> Available</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="admin_rooms.php?edit=<?= $r['id'] ?>" class="btn" style="background:#f39c12; padding: 0.25rem 0.5rem; font-size:0.8rem; margin-right: 0.5rem;">Edit Setup</a>
                            <a href="admin_rooms.php?delete=<?= $r['id'] ?>" class="btn" style="background:#e74c3c; padding: 0.25rem 0.5rem; font-size:0.8rem;" onclick="return confirm('Ensure the room is empty before permanently deleting it! Proceed?');">Decommission</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include 'includes/footer.php'; ?>
