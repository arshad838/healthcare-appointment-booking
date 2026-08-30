<?php
// includes/sidebar.php
// Left sidebar navigation for authenticated portal panels.

$current_file = basename($_SERVER['PHP_SELF']);
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

// Helper to determine if menu item should be active
function is_active($file_name, $current_file, $custom_active = null) {
    if ($custom_active === $file_name) return 'active';
    return ($current_file === $file_name) ? 'active' : '';
}
?>

<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-white sidebar collapse border-end shadow-xs">
    <div class="position-sticky pt-3 sidebar-sticky">
        
        <?php if ($role === 'admin'): ?>
            <div class="px-3 mb-3 text-uppercase text-muted fw-bold fs-9 tracking-wider">Admin Administration</div>
            <ul class="nav flex-column gap-1">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-2 <?php echo is_active('dashboard.php', $current_file); ?>" href="<?php echo BASE_URL; ?>admin/dashboard.php">
                        <i class="fa-solid fa-gauge-high fs-5 text-muted"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-2 <?php echo is_active('doctors.php', $current_file) || is_active('doctor-add.php', $current_file) || is_active('doctor-edit.php', $current_file); ?>" href="<?php echo BASE_URL; ?>admin/doctors.php">
                        <i class="fa-solid fa-user-doctor fs-5 text-muted"></i>
                        <span>Manage Doctors</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-2 <?php echo is_active('departments.php', $current_file); ?>" href="<?php echo BASE_URL; ?>admin/departments.php">
                        <i class="fa-solid fa-hospital fs-5 text-muted"></i>
                        <span>Departments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-2 <?php echo is_active('patients.php', $current_file); ?>" href="<?php echo BASE_URL; ?>admin/patients.php">
                        <i class="fa-solid fa-hospital-user fs-5 text-muted"></i>
                        <span>Patients</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-2 <?php echo is_active('appointments.php', $current_file); ?>" href="<?php echo BASE_URL; ?>admin/appointments.php">
                        <i class="fa-solid fa-calendar-check fs-5 text-muted"></i>
                        <span>Appointments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-2 <?php echo is_active('schedules.php', $current_file); ?>" href="<?php echo BASE_URL; ?>admin/schedules.php">
                        <i class="fa-solid fa-clock fs-5 text-muted"></i>
                        <span>Doctor Schedules</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-2 <?php echo is_active('reports.php', $current_file); ?>" href="<?php echo BASE_URL; ?>admin/reports.php">
                        <i class="fa-solid fa-chart-line fs-5 text-muted"></i>
                        <span>Reports</span>
                    </a>
                </li>
            </ul>
            
        <?php elseif ($role === 'doctor'): ?>
            <div class="px-3 mb-3 text-uppercase text-muted fw-bold fs-9 tracking-wider">Doctor Portal</div>
            <ul class="nav flex-column gap-1">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-2 <?php echo is_active('dashboard.php', $current_file); ?>" href="<?php echo BASE_URL; ?>doctor/dashboard.php">
                        <i class="fa-solid fa-gauge-high fs-5 text-muted"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-2 <?php echo is_active('appointments.php', $current_file); ?>" href="<?php echo BASE_URL; ?>doctor/appointments.php">
                        <i class="fa-solid fa-calendar-days fs-5 text-muted"></i>
                        <span>Appointments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-2 <?php echo is_active('patients.php', $current_file); ?>" href="<?php echo BASE_URL; ?>doctor/patients.php">
                        <i class="fa-solid fa-hospital-user fs-5 text-muted"></i>
                        <span>My Patients</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-2 <?php echo is_active('profile.php', $current_file); ?>" href="<?php echo BASE_URL; ?>doctor/profile.php">
                        <i class="fa-solid fa-user-gear fs-5 text-muted"></i>
                        <span>Doctor Profile</span>
                    </a>
                </li>
            </ul>
            
        <?php elseif ($role === 'patient'): ?>
            <div class="px-3 mb-3 text-uppercase text-muted fw-bold fs-9 tracking-wider">Patient Portal</div>
            <ul class="nav flex-column gap-1">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-2 <?php echo is_active('dashboard.php', $current_file); ?>" href="<?php echo BASE_URL; ?>patient/dashboard.php">
                        <i class="fa-solid fa-gauge-high fs-5 text-muted"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-2 <?php echo is_active('doctors.php', $current_file) || is_active('doctor-details.php', $current_file); ?>" href="<?php echo BASE_URL; ?>patient/doctors.php">
                        <i class="fa-solid fa-user-doctor fs-5 text-muted"></i>
                        <span>Find Doctor</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-2 <?php echo is_active('appointments.php', $current_file); ?>" href="<?php echo BASE_URL; ?>patient/appointments.php">
                        <i class="fa-solid fa-calendar-check fs-5 text-muted"></i>
                        <span>My Bookings</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-2 <?php echo is_active('profile.php', $current_file); ?>" href="<?php echo BASE_URL; ?>patient/profile.php">
                        <i class="fa-solid fa-id-card fs-5 text-muted"></i>
                        <span>My Profile</span>
                    </a>
                </li>
            </ul>
        <?php endif; ?>
        
        <div class="px-3 mt-4 pt-4 border-top">
            <a href="<?php echo BASE_URL; ?>auth/logout.php" class="btn btn-outline-danger btn-sm w-100 py-2 d-flex align-items-center justify-content-center gap-2 rounded-pill">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Sign Out</span>
            </a>
        </div>
    </div>
</nav>
