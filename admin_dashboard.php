<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$page_title = "Admin Dashboard";
include 'includes/header.php';
include 'includes/sidebar_admin.php';

// Fetch summaries
$student_count = $conn->query("SELECT COUNT(*) as cx FROM students")->fetch_assoc()['cx'];
$room_count = $conn->query("SELECT COUNT(*) as cx FROM rooms")->fetch_assoc()['cx'];
$complaints_count = $conn->query("SELECT COUNT(*) as cx FROM complaints WHERE status='Pending'")->fetch_assoc()['cx'];

// Fetch latest students
$latest_students = $conn->query("SELECT s.*, r.room_number FROM students s LEFT JOIN rooms r ON s.room_id = r.id ORDER BY s.id DESC LIMIT 5");
?>

<main class="main-content">
    <header>
        <h1>Admin Dashboard Summary</h1>
        <p>A high-level view of your Hostel Management System.</p>
    </header>

    <!-- Summary Cards -->
    <div class="info-grid" style="grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-top: 2rem;">
        <div class="card" style="margin-bottom:0; text-align: center; border-bottom: 4px solid var(--primary-color);">
            <h3 style="margin: 0; font-size: 2.5rem;"><?= $student_count ?></h3>
            <p style="color: #666;">Total Students</p>
        </div>
        <div class="card" style="margin-bottom:0; text-align: center; border-bottom: 4px solid #3498db;">
            <h3 style="margin: 0; font-size: 2.5rem;"><?= $room_count ?></h3>
            <p style="color: #666;">Total Rooms</p>
        </div>
        <div class="card" style="margin-bottom:0; text-align: center; border-bottom: 4px solid #e74c3c;">
            <h3 style="margin: 0; font-size: 2.5rem;"><?= $complaints_count ?></h3>
            <p style="color: #666;">Pending Complaints</p>
        </div>
    </div>

    <!-- Recent Students Preview -->
    <div class="card" style="margin-top: 2rem;">
        <h2>Recently Added Students</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Assigned Room</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $latest_students->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                        <td><?= $row['room_number'] ? htmlspecialchars($row['room_number']) : '<span style="color:#e74c3c">Unassigned</span>' ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div style="margin-top: 1rem;">
            <a href="admin_students.php" class="btn btn-secondary">Manage All Students &rarr;</a>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
