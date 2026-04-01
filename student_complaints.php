<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['student_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['student_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $issue = trim($_POST['issue']);
    if (!empty($issue)) {
        $stmt = $conn->prepare("INSERT INTO complaints (student_id, issue) VALUES (?, ?)");
        $stmt->bind_param("is", $student_id, $issue);
        $stmt->execute();
        $msg = "Complaint successfully submitted!";
    }
}

$page_title = "My Complaints";
include 'includes/header.php';
include 'includes/sidebar_student.php';

$complaints = $conn->query("SELECT * FROM complaints WHERE student_id = $student_id ORDER BY created_at DESC");
?>
<main class="main-content">
    <header><h1>Hostel Support Portal</h1></header>

    <?php if(isset($msg)): ?>
        <p style="color: green; padding: 1rem; font-weight:bold;"><?= $msg ?></p>
    <?php endif; ?>

    <div class="card" style="margin-top:2rem;">
        <h2>Lodge a New Complaint</h2>
        <form method="POST" action="student_complaints.php" style="margin-top: 1rem;">
            <div class="form-group">
                <label>Please describe the issue in detail</label>
                <textarea name="issue" class="form-control" placeholder="E.g., leaky faucet, broken fan, loud noise..." required rows="4"></textarea>
            </div>
            <button type="submit" class="btn">Submit Evidence to Admin</button>
        </form>
    </div>

    <div class="card">
        <h2>My Complaint History</h2>
        <div class="table-responsive">
            <table>
                <thead><tr><th>Date Submitted</th><th>Issue Tracker</th><th>Administrative Status</th></tr></thead>
                <tbody>
                    <?php if($complaints->num_rows > 0): ?>
                        <?php while($c = $complaints->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('M d, Y g:i A', strtotime($c['created_at'])) ?></td>
                            <td style="max-width:300px;"><?= htmlspecialchars($c['issue']) ?></td>
                            <td>
                                <?php if($c['status'] == 'Resolved'): ?>
                                    <span class="badge paid">Resolved</span>
                                <?php else: ?>
                                    <span class="badge pending">Awaiting Review</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="text-align: center;">You have an empty tracking history.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include 'includes/footer.php'; ?>
