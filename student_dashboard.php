<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['student_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit();
}

$page_title = "My Dashboard";
include 'includes/header.php';
include 'includes/sidebar_student.php';

$student_id = $_SESSION['student_id'];
$s_query = "SELECT s.*, r.room_number, r.capacity 
            FROM students s 
            LEFT JOIN rooms r ON s.room_id = r.id 
            WHERE s.id = ?";
$stmt = $conn->prepare($s_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

$f_query = "SELECT SUM(amount) as total_owed FROM fees WHERE student_id = ? AND status = 'Pending'";
$f_stmt = $conn->prepare($f_query);
$f_stmt->bind_param("i", $student_id);
$f_stmt->execute();
$owed = (float)($f_stmt->get_result()->fetch_assoc()['total_owed'] ?? 0);

$fees_history = $conn->query("SELECT * FROM fees WHERE student_id = $student_id ORDER BY id DESC");
?>

<main class="main-content">
    <header><h1>Student Portal Dashboard</h1></header>

    <div class="info-grid" style="grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-top: 1rem;">
        <div class="card" style="margin-bottom:0;">
            <h3>Profile Specifications</h3>
            <p><strong>Name:</strong> <?= htmlspecialchars($student['name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($student['email']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($student['phone']) ?></p>
            <p><strong>Student ID:</strong> STU<?= str_pad($student_id, 3, '0', STR_PAD_LEFT) ?></p>
        </div>

        <div class="card" style="margin-bottom:0; text-align: center; border-bottom: 4px solid var(--primary-color);">
            <h3>Outstanding Fee Balance</h3>
            <h1 style="color: #e74c3c; margin: 1rem 0; font-size: 3rem;">$<?= number_format($owed, 2) ?></h1>
            <p style="color:#666;">Payable to Hostel Administration</p>
        </div>
    </div>

    <div class="card" style="margin-top: 2rem;">
        <h2>Allocated Room Details</h2>
        <div class="info-grid" style="margin-top: 1rem;">
            <div class="info-item" style="border-left-color: #3498db;">
                <h4>Assigned Room Number</h4>
                <p style="font-size: 1.5rem; font-weight: bold; color: #3498db;"><?= $student['room_number'] ? $student['room_number'] : "Not Assigned" ?></p>
            </div>
            <div class="info-item" style="border-left-color: #3498db;">
                <h4>Room Specifications</h4>
                <p><?= $student['capacity'] ? $student['capacity'] . " Bed Capacity" : "N/A" ?></p>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Fee Payment History</h2>
        <div class="table-responsive">
            <table>
                <thead><tr><th>Amount</th><th>Status</th><th>Date Paid</th></tr></thead>
                <tbody>
                    <?php if($fees_history->num_rows > 0): ?>
                        <?php while($f = $fees_history->fetch_assoc()): ?>
                        <tr>
                            <td>$<?= number_format($f['amount'], 2) ?></td>
                            <td>
                                <?php if($f['status'] == 'Paid'): ?>
                                    <span class="badge paid">Paid</span>
                                <?php else: ?>
                                    <span class="badge" style="background:#e74c3c; color:white;">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $f['payment_date'] ? date('M d, Y', strtotime($f['payment_date'])) : '-' ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="text-align: center;">No fee records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include 'includes/footer.php'; ?>
