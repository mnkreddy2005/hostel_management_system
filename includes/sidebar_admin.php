<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<!-- Sidebar Navigation for Admin -->
<aside class="sidebar">
    <div class="brand">HMS Admin</div>
    <nav>
        <a href="admin_dashboard.php" class="<?= $current_page == 'admin_dashboard.php' ? 'active' : '' ?>">Dashboard Summary</a>
        <a href="admin_students.php" class="<?= $current_page == 'admin_students.php' ? 'active' : '' ?>">Manage Students</a>
        <a href="admin_rooms.php" class="<?= $current_page == 'admin_rooms.php' ? 'active' : '' ?>">Manage Rooms</a>
        <a href="admin_complaints.php" class="<?= $current_page == 'admin_complaints.php' ? 'active' : '' ?>">Student Complaints</a>
        <a href="admin_fees.php" class="<?= $current_page == 'admin_fees.php' ? 'active' : '' ?>">Fee Management</a>
        <a href="logout.php" class="logout" style="margin-top:auto">Logout</a>
    </nav>
</aside>
