<?php
// admin/doctor-add.php
// Add a new doctor account and profile.

$page_title = "CareSync Admin - Add Doctor";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

check_role(['admin']);

$error = '';
$name = $email = $dept_id = $specialization = $qualification = $experience = $fee = $phone = $bio = '';

// Fetch departments for select dropdown
$departments = [];
try {
    $depStmt = $pdo->query("SELECT * FROM departments WHERE status = 'active' ORDER BY name ASC");
    $departments = $depStmt->fetchAll();
} catch (PDOException $e) {
    $error = "Failed to load departments: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = "Invalid CSRF token. Request blocked.";
    } else {
        // Collect and sanitize fields
        $name = sanitize($_POST['name']);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $dept_id = (int)($_POST['department_id'] ?? 0);
        $specialization = sanitize($_POST['specialization']);
        $qualification = sanitize($_POST['qualification']);
        $experience = (int)($_POST['experience'] ?? 0);
        $fee = (float)($_POST['consultation_fee'] ?? 0);
        $phone = sanitize($_POST['phone']);
        $bio = sanitize($_POST['bio']);

        // Validations
        if (empty($name) || empty($email) || empty($password) || empty($dept_id) || empty($specialization) || empty($qualification) || empty($phone) || empty($bio)) {
            $error = "All fields except file uploads are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } elseif ($experience < 0 || $fee < 0) {
            $error = "Experience and consultation fee must be positive values.";
        } else {
            try {
                // Check if email already exists
                $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
                $checkStmt->execute([$email]);
                if ($checkStmt->fetch()) {
                    $error = "Email address is already in use by another user account.";
                } else {
                    // Start transaction
                    $pdo->beginTransaction();

                    // 1. Insert user
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmtUser = $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'doctor', 'active')");
                    $stmtUser->execute([$name, $email, $hashed_password]);
                    $userId = $pdo->lastInsertId();

                    // 2. Insert doctor profile (default image is null or placeholder)
                    $stmtDoc = $pdo->prepare("
                        INSERT INTO doctors (user_id, department_id, specialization, qualification, experience, consultation_fee, phone, bio, image, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL, 'active')
                    ");
                    $stmtDoc->execute([$userId, $dept_id, $specialization, $qualification, $experience, $fee, $phone, $bio]);

                    $pdo->commit();

                    $_SESSION['success_msg'] = "Doctor account for Dr. {$name} added successfully.";
                    header("Location: doctors.php");
                    exit();
                }
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = "Failed to add doctor record: " . $e->getMessage();
            }
        }
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 font-outfit fw-bold text-dark-blue">Add Doctor Account</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="doctors.php" class="btn btn-outline-secondary rounded-pill px-4 py-2 fs-8 fw-semibold">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to List
        </a>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-exclamation me-1"></i> <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-3 bg-white p-4">
    <div class="card-body">
        <form action="doctor-add.php" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

            <h5 class="font-outfit fw-bold text-dark-blue mb-4 border-bottom pb-2"><i class="fa-solid fa-user-lock text-medical-blue me-2"></i>Account & Authentication</h5>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label fs-8 text-muted fw-semibold">Doctor Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo sanitize($name); ?>" placeholder="e.g. Dr. John Doe" required>
                    <div class="invalid-feedback">Please enter the doctor name.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label fs-8 text-muted fw-semibold">Email Address (Login Username)</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo sanitize($email); ?>" placeholder="e.g. doctor@care.com" required>
                    <div class="invalid-feedback">Please enter a valid email address.</div>
                </div>
                <div class="col-md-6 mb-4">
                    <label for="password" class="form-label fs-8 text-muted fw-semibold">Account Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Min 6 characters" required minlength="6">
                    <div class="invalid-feedback">Please provide a password of at least 6 characters.</div>
                </div>
            </div>

            <h5 class="font-outfit fw-bold text-dark-blue mb-4 border-bottom pb-2 mt-2"><i class="fa-solid fa-user-md text-medical-blue me-2"></i>Medical Profile Details</h5>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="department_id" class="form-label fs-8 text-muted fw-semibold">Department Specialty</label>
                    <select class="form-select" id="department_id" name="department_id" required>
                        <option value="" disabled selected>Select Department...</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>" <?php echo $dept_id == $dept['id'] ? 'selected' : ''; ?>><?php echo sanitize($dept['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Please select a clinic department.</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="specialization" class="form-label fs-8 text-muted fw-semibold">Sub-Specialization</label>
                    <input type="text" class="form-control" id="specialization" name="specialization" value="<?php echo sanitize($specialization); ?>" placeholder="e.g. Pediatric Cardiology" required>
                    <div class="invalid-feedback">Please specify sub-specialization.</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="phone" class="form-label fs-8 text-muted fw-semibold">Contact Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo sanitize($phone); ?>" placeholder="e.g. +1 555-1234" required>
                    <div class="invalid-feedback">Please enter the contact phone number.</div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="qualification" class="form-label fs-8 text-muted fw-semibold">Qualifications & Degrees</label>
                    <input type="text" class="form-control" id="qualification" name="qualification" value="<?php echo sanitize($qualification); ?>" placeholder="e.g. MD in Cardiology (Harvard)" required>
                    <div class="invalid-feedback">Please specify academic qualifications.</div>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="experience" class="form-label fs-8 text-muted fw-semibold">Experience (Years)</label>
                    <input type="number" class="form-control" id="experience" name="experience" value="<?php echo sanitize($experience); ?>" min="0" placeholder="e.g. 10" required>
                    <div class="invalid-feedback">Provide experience years.</div>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="consultation_fee" class="form-label fs-8 text-muted fw-semibold">Consultation Fee ($)</label>
                    <input type="number" step="0.01" class="form-control" id="consultation_fee" name="consultation_fee" value="<?php echo sanitize($fee); ?>" min="0" placeholder="e.g. 150.00" required>
                    <div class="invalid-feedback">Provide consulting rate.</div>
                </div>
                
                <div class="col-12 mb-4">
                    <label for="bio" class="form-label fs-8 text-muted fw-semibold">Professional Biography</label>
                    <textarea class="form-control" id="bio" name="bio" rows="4" placeholder="Brief details regarding doctor experience, clinic specializations, and patient care philosophies..." required><?php echo sanitize($bio); ?></textarea>
                    <div class="invalid-feedback">Please write a short biography for the profile.</div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 pt-3 border-top">
                <a href="doctors.php" class="btn btn-outline-secondary px-4 py-2">Cancel</a>
                <button type="submit" class="btn btn-medical-blue text-white px-5 py-2 fw-semibold">Create Doctor Account</button>
            </div>
        </form>
    </div>
</div>

<?php 
require_once __DIR__ . '/../includes/footer.php';
?>
