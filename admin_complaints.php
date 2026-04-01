<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_GET['mark_resolved'])) {
    $cid = (int)$_GET['mark_resolved'];
    $conn->query("UPDATE complaints SET status = 'Resolved' WHERE id = $cid");
    header("Location: admin_complaints.php");
    exit();
}

$page_title = "Manage Complaints";
include 'includes/header.php';
include 'includes/sidebar_admin.php';

$complaints = $conn->query("
    SELECT c.*, s.name, r.room_number 
    FROM complaints c 
    JOIN students s ON c.student_id = s.id 
    LEFT JOIN rooms r ON s.room_id = r.id 
    ORDER BY c.created_at DESC
");
?>
<main class="main-content">
    <header><h1>Student Complaints</h1></header>
    <div class="card" style="margin-top:2rem;">
        <div class="table-responsive">
            <table>
                <thead><tr><th>Date</th><th>Student</th><th>Room</th><th>Issue</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    <?php while($c = $complaints->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                        <td><?= htmlspecialchars($c['name']) ?></td>
                        <td><?= $c['room_number'] ? $c['room_number'] : 'N/A' ?></td>
                        <td style="max-width:300px;"><?= htmlspecialchars($c['issue']) ?></td>
                        <td>
                            <?php if($c['status'] == 'Resolved'): ?>
                                <span class="badge paid">Resolved</span>
                            <?php else: ?>
                                <span class="badge pending">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($c['status'] == 'Pending'): ?>
                            <a href="admin_complaints.php?mark_resolved=<?= $c['id'] ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Mark Resolved</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include 'includes/footer.php'; ?>
