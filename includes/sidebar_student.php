<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<!-- Sidebar Navigation for Student -->
<aside class="sidebar">
    <div class="brand">HMS Student</div>
    <nav>
        <a href="student_dashboard.php" class="<?= $current_page == 'student_dashboard.php' ? 'active' : '' ?>">My Profile & Fees</a>
        <a href="student_complaints.php" class="<?= $current_page == 'student_complaints.php' ? 'active' : '' ?>">My Complaints</a>
        <a href="logout.php" class="logout" style="margin-top:auto">Logout</a>
    </nav>
</aside>
