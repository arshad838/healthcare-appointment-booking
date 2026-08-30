<?php
// doctor/profile.php
// Doctor profile details editing.

$page_title = "CareSync Doctor - Profile Settings";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

check_role(['doctor']);

$doctorId = $_SESSION['doctor_id'];
$userId = $_SESSION['user_id'];

$error = '';
$success = '';

// Process Profile Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = "CSRF verification failed.";
    } else {
        $name = sanitize($_POST['name']);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $specialization = sanitize($_POST['specialization']);
        $qualification = sanitize($_POST['qualification']);
        $experience = (int)($_POST['experience'] ?? 0);
        $fee = (float)($_POST['consultation_fee'] ?? 0);
        $phone = sanitize($_POST['phone']);
        $bio = sanitize($_POST['bio']);

        // Basic validation
        if (empty($name) || empty($email) || empty($specialization) || empty($qualification) || empty($phone) || empty($bio)) {
            $error = "All profile details except password are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } elseif ($experience < 0 || $fee < 0) {
            $error = "Experience and consultation fee must be positive values.";
        } else {
            try {
                // Check if email is already taken by another user
                $chk = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
                $chk->execute([$email, $userId]);
                if ($chk->fetch()) {
                    $error = "This email is already in use by another user account.";
                } else {
                    $pdo->beginTransaction();

                    // 1. Update user
                    if (!empty($password)) {
                        if (strlen($password) < 6) {
                            throw new Exception("Password must be at least 6 characters long.");
                        }
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
                        $stmt->execute([$name, $email, $hashed_password, $userId]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                        $stmt->execute([$name, $email, $userId]);
                    }

                    // 2. Update doctor details
                    $stmtDoc = $pdo->prepare("
                        UPDATE doctors 
                        SET specialization = ?, qualification = ?, experience = ?, consultation_fee = ?, phone = ?, bio = ?
                        WHERE id = ?
                    ");
                    $stmtDoc->execute([$specialization, $qualification, $experience, $fee, $phone, $bio, $doctorId]);

                    $pdo->commit();

                    $_SESSION['name'] = $name; // Update active session name
                    $success = "Your profile updates were saved successfully.";
                }
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = $e->getMessage();
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Fetch current details
try {
    $stmt = $pdo->prepare("
        SELECT d.*, u.name, u.email, dep.name AS department_name
        FROM doctors d
        JOIN users u ON d.user_id = u.id
        JOIN departments dep ON d.department_id = dep.id
        WHERE d.id = ? LIMIT 1
    ");
    $stmt->execute([$doctorId]);
    $doctor = $stmt->fetch();
} catch (PDOException $e) {
    $error = "Failed to load profile details: " . $e->getMessage();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 font-outfit fw-bold text-dark-blue">My Profile Settings</h1>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-check me-1"></i> <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-exclamation me-1"></i> <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-3 bg-white p-4">
    <div class="card-body">
        <form action="profile.php" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

            <h5 class="font-outfit fw-bold text-dark-blue mb-4 border-bottom pb-2"><i class="fa-solid fa-lock text-medical-blue me-2"></i>Security & Identity</h5>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label fs-8 text-muted fw-semibold">Profile Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo sanitize($doctor['name']); ?>" required>
                    <div class="invalid-feedback">Name is required.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label fs-8 text-muted fw-semibold">Login Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo sanitize($doctor['email']); ?>" required>
                    <div class="invalid-feedback">Please provide a valid email.</div>
                </div>
                <div class="col-md-6 mb-4">
                    <label for="password" class="form-label fs-8 text-muted fw-semibold">New Password (Leave blank to keep current)</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Min 6 characters">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label fs-8 text-muted fw-semibold">Department (Assigned by Administrator)</label>
                    <input type="text" class="form-control bg-light" value="<?php echo sanitize($doctor['department_name']); ?>" readonly>
                </div>
            </div>

            <h5 class="font-outfit fw-bold text-dark-blue mb-4 border-bottom pb-2 mt-2"><i class="fa-solid fa-user-md text-medical-blue me-2"></i>Medical Profile Details</h5>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="specialization" class="form-label fs-8 text-muted fw-semibold">Clinical Specialty</label>
                    <input type="text" class="form-control" id="specialization" name="specialization" value="<?php echo sanitize($doctor['specialization']); ?>" required>
                    <div class="invalid-feedback">Specialty is required.</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="phone" class="form-label fs-8 text-muted fw-semibold">Contact Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo sanitize($doctor['phone']); ?>" required>
                    <div class="invalid-feedback">Contact number is required.</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="qualification" class="form-label fs-8 text-muted fw-semibold">Degrees & Qualifications</label>
                    <input type="text" class="form-control" id="qualification" name="qualification" value="<?php echo sanitize($doctor['qualification']); ?>" required>
                    <div class="invalid-feedback">Please list your qualifications.</div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="experience" class="form-label fs-8 text-muted fw-semibold">Consulting Experience (Years)</label>
                    <input type="number" class="form-control" id="experience" name="experience" value="<?php echo sanitize($doctor['experience']); ?>" min="0" required>
                    <div class="invalid-feedback">Experience is required.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="consultation_fee" class="form-label fs-8 text-muted fw-semibold">Consultation Rate Fee ($)</label>
                    <input type="number" step="0.01" class="form-control" id="consultation_fee" name="consultation_fee" value="<?php echo sanitize($doctor['consultation_fee']); ?>" min="0" required>
                    <div class="invalid-feedback">Consultation fee rate is required.</div>
                </div>
                
                <div class="col-12 mb-4">
                    <label for="bio" class="form-label fs-8 text-muted fw-semibold">Professional Bio</label>
                    <textarea class="form-control" id="bio" name="bio" rows="4" required><?php echo sanitize($doctor['bio']); ?></textarea>
                    <div class="invalid-feedback">A biography description is required.</div>
                </div>
            </div>

            <div class="d-flex justify-content-end pt-3 border-top">
                <button type="submit" class="btn btn-medical-blue text-white px-5 py-2 fw-semibold">Save Profile Updates</button>
            </div>
        </form>
    </div>
</div>

<?php 
require_once __DIR__ . '/../includes/footer.php';
?>
