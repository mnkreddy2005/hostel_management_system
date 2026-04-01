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
    $student_id = (int)$_POST['student_id'];
    $amount = (float)$_POST['amount'];
    $status = $_POST['status'];
    $payment_date = empty($_POST['payment_date']) ? NULL : $_POST['payment_date'];

    if ($action == 'add') {
        $stmt = $conn->prepare("INSERT INTO fees (student_id, amount, status, payment_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idss", $student_id, $amount, $status, $payment_date);
        $stmt->execute();
        $success = "Fee statement flawlessly authored!";
    } 
    elseif ($action == 'edit') {
        $fee_id = (int)$_POST['fee_id'];
        $stmt = $conn->prepare("UPDATE fees SET student_id=?, amount=?, status=?, payment_date=? WHERE id=?");
        $stmt->bind_param("idssi", $student_id, $amount, $status, $payment_date, $fee_id);
        $stmt->execute();
        $success = "Fee ledger gracefully optimized!";
    }
}

// Mark as Paid gracefully via URL link
if (isset($_GET['mark_paid'])) {
    $fid = (int)$_GET['mark_paid'];
    $dt = date('Y-m-d');
    $conn->query("UPDATE fees SET status = 'Paid', payment_date = '$dt' WHERE id = $fid");
    header("Location: admin_fees.php");
    exit();
}

// Delete permanently
if (isset($_GET['delete'])) {
    $del = (int)$_GET['delete'];
    $conn->query("DELETE FROM fees WHERE id = $del");
    $success = "Fee log cleanly scrapped.";
}

// Edit Mode Setup
$edit_mode = false;
$e_fee = ['student_id'=>'', 'amount'=>'', 'status'=>'Pending', 'payment_date'=>''];
$eid = null;

if (isset($_GET['edit'])) {
    $edit_mode = true;
    $eid = (int)$_GET['edit'];
    $fid = $conn->query("SELECT * FROM fees WHERE id = $eid");
    if($fid->num_rows > 0) {
        $e_fee = $fid->fetch_assoc();
    } else {
        $edit_mode = false;
    }
}

$page_title = "Manage Fees";
include 'includes/header.php';
include 'includes/sidebar_admin.php';

$students = $conn->query("SELECT id, name FROM students ORDER BY name ASC");
$fees = $conn->query("SELECT f.*, s.name FROM fees f JOIN students s ON f.student_id = s.id ORDER BY f.id DESC");
?>
<main class="main-content">
    <header><h1>Financial Accounting</h1></header>

    <?php if($error): ?><p style="color: red; padding: 1rem;"> <?= $error ?> </p><?php endif; ?>
    <?php if($success): ?><p style="color: #4CAF50; padding: 1rem;"> <?= $success ?> </p><?php endif; ?>

    <div class="card" style="margin-top: 2rem;">
        <h2><?= $edit_mode ? "Rewrite Fee Protocol" : "Generate Fee Statement" ?></h2>
        
        <?php if($edit_mode): ?>
            <a href="admin_fees.php" style="color:#e74c3c; font-size: 0.9rem; margin-bottom: 1rem; display:block;">&larr; Abort Edits</a>
        <?php endif; ?>

        <form method="POST" action="admin_fees.php" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
            <input type="hidden" name="action" value="<?= $edit_mode ? 'edit' : 'add' ?>">
            <?php if($edit_mode): ?><input type="hidden" name="fee_id" value="<?= $eid ?>"><?php endif; ?>

            <div class="form-group">
                <label>Target Student</label>
                <select name="student_id" class="form-control" required>
                    <option value="">-- Choose Student --</option>
                    <?php while($st = $students->fetch_assoc()): 
                        $sel = ($e_fee['student_id'] == $st['id']) ? 'selected' : '';
                    ?>
                        <option value="<?= $st['id'] ?>" <?= $sel ?>><?= htmlspecialchars($st['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Principal Amount ($)</label>
                <input type="number" step="0.01" name="amount" class="form-control" required value="<?= $e_fee['amount'] ?>">
            </div>
            <div class="form-group">
                <label>Current Status</label>
                <select name="status" class="form-control" required>
                    <option value="Pending" <?= ($e_fee['status'] == 'Pending') ? 'selected' : '' ?>>Overdue / Pending</option>
                    <option value="Paid" <?= ($e_fee['status'] == 'Paid') ? 'selected' : '' ?>>Settled (Paid Now)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Recorded Payment Date (If Paid)</label>
                <input type="date" name="payment_date" class="form-control" value="<?= $e_fee['payment_date'] ?>">
            </div>
            <div style="grid-column: 1 / -1;">
                <button type="submit" class="btn"><?= $edit_mode ? "Finalize Restructuring" : "Authorize Fee Application" ?></button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Fee Records Database</h2>
        <div class="table-responsive">
            <table>
                <thead><tr><th>Student</th><th>Amount</th><th>Status</th><th>Date Logged</th><th>Operations</th></tr></thead>
                <tbody>
                    <?php while($f = $fees->fetch_assoc()): ?>
                    <tr>
                        <td style="font-weight:bold;"><?= htmlspecialchars($f['name']) ?></td>
                        <td>$<?= number_format($f['amount'], 2) ?></td>
                        <td>
                            <?php if($f['status'] == 'Paid'): ?>
                                <span class="badge paid">Paid</span>
                            <?php else: ?>
                                <span class="badge pending" style="background:#e74c3c; color:#fff;">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $f['payment_date'] ? date('M d, Y', strtotime($f['payment_date'])) : '<span style="color:#aaa">-</span>' ?></td>
                        <td>
                            <?php if($f['status'] == 'Pending'): ?>
                            <a href="admin_fees.php?mark_paid=<?= $f['id'] ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; background:#4CAF50; margin-right:0.25rem;">Resolve</a>
                            <?php endif; ?>
                            
                            <a href="admin_fees.php?edit=<?= $f['id'] ?>" class="btn" style="background:#f39c12; padding: 0.25rem 0.5rem; font-size:0.8rem; margin-right: 0.25rem;">Edit</a>
                            <a href="admin_fees.php?delete=<?= $f['id'] ?>" class="btn" style="background:#e74c3c; padding: 0.25rem 0.5rem; font-size:0.8rem;" onclick="return confirm('Wipe this financial log unconditionally?');">Erase</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include 'includes/footer.php'; ?>
