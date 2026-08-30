<?php
// auth/process-login.php
// Backend process for verifying login details and setting up user session.

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

// 1. Verify CSRF Token
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    $_SESSION['login_error'] = "Invalid CSRF token. Access denied.";
    header("Location: login.php");
    exit();
}

$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['login_error'] = "Both email and password are required fields.";
    header("Location: login.php");
    exit();
}

try {
    // 2. Fetch User Record
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Check if user account is active
        if ($user['status'] !== 'active') {
            $_SESSION['login_error'] = "Your account is currently inactive. Contact your system admin.";
            header("Location: login.php");
            exit();
        }

        // Initialize Session Variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        // 3. Resolve Role IDs for Patient/Doctor portals
        if ($user['role'] === 'patient') {
            $pStmt = $pdo->prepare("SELECT id FROM patients WHERE user_id = ? LIMIT 1");
            $pStmt->execute([$user['id']]);
            $patient = $pStmt->fetch();
            if ($patient) {
                $_SESSION['patient_id'] = $patient['id'];
            } else {
                // If patient record is missing, redirect with error
                $_SESSION['login_error'] = "Patient profile record not found.";
                session_destroy();
                header("Location: login.php");
                exit();
            }
            $redirect_url = BASE_URL . "patient/dashboard.php";
            
        } elseif ($user['role'] === 'doctor') {
            $dStmt = $pdo->prepare("SELECT id FROM doctors WHERE user_id = ? LIMIT 1");
            $dStmt->execute([$user['id']]);
            $doctor = $dStmt->fetch();
            if ($doctor) {
                $_SESSION['doctor_id'] = $doctor['id'];
            } else {
                $_SESSION['login_error'] = "Doctor profile record not found.";
                session_destroy();
                header("Location: login.php");
                exit();
            }
            $redirect_url = BASE_URL . "doctor/dashboard.php";
            
        } else {
            // Admin portal
            $redirect_url = BASE_URL . "admin/dashboard.php";
        }

        // Redirect to intended URL if available, else standard portal
        if (isset($_SESSION['redirect_url'])) {
            $redirect_url = $_SESSION['redirect_url'];
            unset($_SESSION['redirect_url']);
        }

        header("Location: " . $redirect_url);
        exit();

    } else {
        $_SESSION['login_error'] = "Invalid credentials. Please check your username and password.";
        header("Location: login.php");
        exit();
    }

} catch (PDOException $e) {
    $_SESSION['login_error'] = "Authentication error occurred: " . $e->getMessage();
    header("Location: login.php");
    exit();
}
?>
