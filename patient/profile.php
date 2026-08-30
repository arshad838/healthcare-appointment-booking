<?php
// patient/profile.php
// Patient profile editing.

$page_title = "CareSync Patient - Profile Settings";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

check_role(['patient']);

$patientId = $_SESSION['patient_id'];
$userId = $_SESSION['user_id'];

$error = '';
$success = '';

// Handle Profile Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = "CSRF verification failed.";
    } else {
        // Collect and sanitize fields
        $name = sanitize($_POST['name']);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $phone = sanitize($_POST['phone']);
        $gender = sanitize($_POST['gender']);
        $dob = sanitize($_POST['date_of_birth']);
        $address = sanitize($_POST['address']);

        // Check required fields
        if (empty($name) || empty($email) || empty($phone) || empty($gender) || empty($dob) || empty($address)) {
            $error = "All profile fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please provide a valid email address.";
        } else {
            try {
                // Ensure email address is unique
                $chk = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
                $chk->execute([$email, $userId]);
                
                if ($chk->fetch()) {
                    $error = "This email address is already in use by another account.";
                } else {
                    $pdo->beginTransaction();

                    // 1. Update user credentials
                    if (!empty($password)) {
                        if (strlen($password) < 6) {
                            throw new Exception("Password must be at least 6 characters long.");
                        }
                        $hashed_pwd = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
                        $stmt->execute([$name, $email, $hashed_pwd, $userId]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                        $stmt->execute([$name, $email, $userId]);
                    }

                    // 2. Update patient profile
                    $stmtPat = $pdo->prepare("
                        UPDATE patients 
                        SET phone = ?, gender = ?, date_of_birth = ?, address = ?
                        WHERE id = ?
                    ");
                    $stmtPat->execute([$phone, $gender, $dob, $address, $patientId]);

                    $pdo->commit();

                    $_SESSION['name'] = $name; // Sync session name
                    $success = "Your profile was updated successfully.";
                }
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = $e->getMessage();
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = "Database failure: " . $e->getMessage();
            }
        }
    }
}

// Fetch current details
try {
    $stmt = $pdo->prepare("
        SELECT p.*, u.name, u.email 
        FROM patients p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.id = ? LIMIT 1
    ");
    $stmt->execute([$patientId]);
    $patient = $stmt->fetch();
} catch (PDOException $e) {
    $error = "Failed to load patient profile data: " . $e->getMessage();
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
                    <label for="name" class="form-label fs-8 text-muted fw-semibold">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo sanitize($patient['name']); ?>" required>
                    <div class="invalid-feedback">Please enter your name.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label fs-8 text-muted fw-semibold">Account Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo sanitize($patient['email']); ?>" required>
                    <div class="invalid-feedback">Please provide a valid email.</div>
                </div>
                <div class="col-md-6 mb-4">
                    <label for="password" class="form-label fs-8 text-muted fw-semibold">New Password (Leave blank to keep current)</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Change password (min 6 characters)">
                </div>
            </div>

            <h5 class="font-outfit fw-bold text-dark-blue mb-4 border-bottom pb-2 mt-2"><i class="fa-solid fa-address-card text-medical-blue me-2"></i>Personal Contact Information</h5>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="phone" class="form-label fs-8 text-muted fw-semibold">Contact Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo sanitize($patient['phone']); ?>" required>
                    <div class="invalid-feedback">Please enter your contact number.</div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="gender" class="form-label fs-8 text-muted fw-semibold">Gender</label>
                    <select class="form-select" id="gender" name="gender" required>
                        <option value="Male" <?php echo $patient['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $patient['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo $patient['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                    <div class="invalid-feedback">Please select your gender.</div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="date_of_birth" class="form-label fs-8 text-muted fw-semibold">Date of Birth</label>
                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo sanitize($patient['date_of_birth']); ?>" required>
                    <div class="invalid-feedback">Please enter your date of birth.</div>
                </div>
                
                <div class="col-12 mb-4">
                    <label for="address" class="form-label fs-8 text-muted fw-semibold">Residential Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3" required><?php echo sanitize($patient['address']); ?></textarea>
                    <div class="invalid-feedback">Please enter your address.</div>
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
