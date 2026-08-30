<?php
// includes/navbar.php
// Top navigation bar for the Healthcare Appointment Booking System.
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark-blue sticky-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="<?php echo BASE_URL; ?>">
            <i class="fa-solid fa-heart-pulse text-medical-blue me-2 fs-4"></i>
            <span class="font-outfit fw-bold tracking-tight">Care<span class="text-medical-blue">Sync</span></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>#how-it-works">How It Works</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>#departments">Specialties</a>
                </li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'patient'): ?>
                    <li class="nav-item">
                        <a class="nav-link text-medical-blue fw-medium" href="<?php echo BASE_URL; ?>patient/doctors.php">
                            <i class="fa-solid fa-user-doctor me-1"></i> Find a Doctor
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <div class="d-flex align-items-center gap-3">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <a class="btn btn-outline-light btn-sm dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" id="userMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-user-circle fs-5 text-medical-blue"></i>
                            <span><?php echo sanitize($_SESSION['name']); ?></span>
                            <span class="badge bg-medical-blue text-white rounded-pill px-2 fs-9 text-uppercase"><?php echo $_SESSION['role']; ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="userMenuLink">
                            <li>
                                <a class="dropdown-item py-2" href="<?php echo BASE_URL . $_SESSION['role']; ?>/dashboard.php">
                                    <i class="fa-solid fa-gauge me-2 text-muted"></i> Dashboard
                                </a>
                            </li>
                            <?php if ($_SESSION['role'] === 'patient' || $_SESSION['role'] === 'doctor'): ?>
                                <li>
                                    <a class="dropdown-item py-2" href="<?php echo BASE_URL . $_SESSION['role']; ?>/profile.php">
                                        <i class="fa-solid fa-user-gear me-2 text-muted"></i> Edit Profile
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item py-2 text-danger" href="<?php echo BASE_URL; ?>auth/logout.php">
                                    <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>auth/login.php" class="btn btn-link text-white text-decoration-none fw-medium py-1 px-3">Login</a>
                    <a href="<?php echo BASE_URL; ?>auth/register.php" class="btn btn-medical-blue text-white fw-medium px-4 py-2 shadow-sm rounded-pill">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
