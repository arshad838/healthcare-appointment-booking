<?php
// auth/login.php
// Secure user login form.

$page_title = "CareSync - Secure Login";
$hide_nav = true;
require_once __DIR__ . '/../includes/header.php';

$error = '';
$success = '';

if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}
if (isset($_SESSION['register_success'])) {
    $success = $_SESSION['register_success'];
    unset($_SESSION['register_success']);
}
?>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5 py-5">
            
            <div class="text-center mb-4">
                <a href="<?php echo BASE_URL; ?>" class="text-decoration-none d-inline-flex align-items-center gap-2">
                    <i class="fa-solid fa-heart-pulse text-medical-blue fs-1"></i>
                    <span class="font-outfit fw-extrabold tracking-tight fs-2 text-dark-blue">CareSync</span>
                </a>
                <p class="text-muted mt-2">Enter credentials to access your portal</p>
            </div>

            <div class="card border-0 shadow-lg p-4 rounded-4 bg-white">
                <div class="card-body">
                    <h3 class="font-outfit fw-bold mb-4 text-center">Sign In</h3>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show fs-8" role="alert">
                            <i class="fa-solid fa-circle-exclamation me-1"></i> <?php echo sanitize($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show fs-8" role="alert">
                            <i class="fa-solid fa-circle-check me-1"></i> <?php echo sanitize($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="process-login.php" method="POST" class="needs-validation" novalidate>
                        <!-- Form CSRF protection token -->
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                            <label for="email"><i class="fa-solid fa-envelope text-muted me-1"></i> Email Address</label>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                            <label for="password"><i class="fa-solid fa-lock text-muted me-1"></i> Password</label>
                            <div class="invalid-feedback">Please enter your password.</div>
                        </div>

                        <button class="btn btn-medical-blue text-white w-100 py-3 rounded-pill fw-semibold shadow-sm mb-3" type="submit">
                            <i class="fa-solid fa-right-to-bracket me-1"></i> Login
                        </button>
                    </form>

                    <div class="text-center mt-3 fs-8">
                        <span class="text-muted">New Patient?</span>
                        <a href="register.php" class="text-medical-blue fw-semibold text-decoration-none ms-1">Create an Account</a>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="<?php echo BASE_URL; ?>" class="text-muted fs-8 text-decoration-none">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Homepage
                </a>
            </div>

            <!-- Demo Login Assistant Accordion for University DevOps Presentation -->
            <div class="accordion mt-5 shadow-xs border-0 rounded-3" id="demoAccountsHelper">
                <div class="accordion-item border-0">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-dark-blue text-white font-outfit fs-8 rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#demoAccountsCollapse" aria-expanded="false">
                            <i class="fa-solid fa-circle-info me-2 text-medical-blue"></i> DevOps Demo Credentials (Click to Expand)
                        </button>
                    </h2>
                    <div id="demoAccountsCollapse" class="accordion-collapse collapse" data-bs-parent="#demoAccountsHelper">
                        <div class="accordion-body bg-white border border-top-0 rounded-bottom-3 fs-9">
                            <p class="mb-2">For grading and demo presentation, utilize the following seeded credentials (password is <strong>password123</strong>):</p>
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Role</th>
                                        <th>Username / Email</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="badge bg-danger">Admin</span></td>
                                        <td><code>admin@healthcare.com</code></td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-primary">Doctor (Cardiology)</span></td>
                                        <td><code>john.smith@healthcare.com</code></td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-success">Patient</span></td>
                                        <td><code>alice.brown@example.com</code></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php 
require_once __DIR__ . '/../includes/footer.php';
?>
