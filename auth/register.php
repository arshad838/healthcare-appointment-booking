<?php
// auth/register.php
// Patient registration page.

$page_title = "CareSync - Patient Registration";
$hide_nav = true;
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

$error = '';
$name = $email = $phone = $gender = $dob = $address = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validate CSRF
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = "Invalid CSRF token validation. Request blocked.";
    } else {
        // Retrieve and sanitize fields
        $name = sanitize($_POST['name']);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $phone = sanitize($_POST['phone']);
        $gender = sanitize($_POST['gender']);
        $dob = sanitize($_POST['date_of_birth']);
        $address = sanitize($_POST['address']);

        // Basic validations
        if (empty($name) || empty($email) || empty($password) || empty($phone) || empty($gender) || empty($dob) || empty($address)) {
            $error = "All fields are required to register.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please provide a valid email address.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } else {
            try {
                // 2. Check if email exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = "This email address is already registered. Please login instead.";
                } else {
                    // 3. Insert user and patient under transaction
                    $pdo->beginTransaction();

                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert to users table
                    $stmtUser = $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'patient', 'active')");
                    $stmtUser->execute([$name, $email, $hashed_password]);
                    $userId = $pdo->lastInsertId();

                    // Insert to patients table
                    $stmtPatient = $pdo->prepare("INSERT INTO patients (user_id, phone, gender, date_of_birth, address) VALUES (?, ?, ?, ?, ?)");
                    $stmtPatient->execute([$userId, $phone, $gender, $dob, $address]);

                    $pdo->commit();

                    $_SESSION['register_success'] = "Account created successfully! Please login with your credentials.";
                    header("Location: login.php");
                    exit();
                }
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $error = "Registration failed: " . $e->getMessage();
            }
        }
    }
}
?>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100 py-5">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
            
            <div class="text-center mb-4">
                <a href="<?php echo BASE_URL; ?>" class="text-decoration-none d-inline-flex align-items-center gap-2">
                    <i class="fa-solid fa-heart-pulse text-medical-blue fs-1"></i>
                    <span class="font-outfit fw-extrabold tracking-tight fs-2 text-dark-blue">CareSync</span>
                </a>
                <p class="text-muted mt-2">Create a secure patient account to book consultations</p>
            </div>

            <div class="card border-0 shadow-lg p-4 rounded-4 bg-white">
                <div class="card-body">
                    <h3 class="font-outfit fw-bold mb-4 text-center">Patient Register</h3>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show fs-8" role="alert">
                            <i class="fa-solid fa-circle-exclamation me-1"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="register.php" method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo sanitize($name); ?>" placeholder="Full Name" required>
                            <label for="name"><i class="fa-solid fa-user text-muted me-1"></i> Full Name</label>
                            <div class="invalid-feedback">Please enter your full name.</div>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo sanitize($email); ?>" placeholder="Email Address" required>
                            <label for="email"><i class="fa-solid fa-envelope text-muted me-1"></i> Email Address</label>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required minlength="6">
                            <label for="password"><i class="fa-solid fa-lock text-muted me-1"></i> Password (min 6 chars)</label>
                            <div class="invalid-feedback">Password must be at least 6 characters.</div>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo sanitize($phone); ?>" placeholder="Phone Number" required>
                            <label for="phone"><i class="fa-solid fa-phone text-muted me-1"></i> Phone Number</label>
                            <div class="invalid-feedback">Please enter your contact number.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <select class="form-select" id="gender" name="gender" required>
                                        <option value="" disabled <?php echo empty($gender) ? 'selected' : ''; ?>>Select...</option>
                                        <option value="Male" <?php echo $gender === 'Male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo $gender === 'Female' ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo $gender === 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                    <label for="gender"><i class="fa-solid fa-venus-mars text-muted me-1"></i> Gender</label>
                                    <div class="invalid-feedback">Please select your gender.</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo sanitize($dob); ?>" required>
                                    <label for="date_of_birth"><i class="fa-solid fa-calendar-days text-muted me-1"></i> Date of Birth</label>
                                    <div class="invalid-feedback">Please enter your date of birth.</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-4">
                            <textarea class="form-control" id="address" name="address" placeholder="Residential Address" style="height: 100px" required><?php echo sanitize($address); ?></textarea>
                            <label for="address"><i class="fa-solid fa-house-chimney text-muted me-1"></i> Residential Address</label>
                            <div class="invalid-feedback">Please enter your address.</div>
                        </div>

                        <button class="btn btn-medical-blue text-white w-100 py-3 rounded-pill fw-semibold shadow-sm mb-3" type="submit">
                            <i class="fa-solid fa-user-plus me-1"></i> Register Account
                        </button>
                    </form>

                    <div class="text-center mt-3 fs-8">
                        <span class="text-muted">Already registered?</span>
                        <a href="login.php" class="text-medical-blue fw-semibold text-decoration-none ms-1">Login here</a>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="<?php echo BASE_URL; ?>" class="text-muted fs-8 text-decoration-none">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Homepage
                </a>
            </div>

        </div>
    </div>
</div>

<?php 
require_once __DIR__ . '/../includes/footer.php';
?>
