<?php
// patient/book-appointment.php
// Back-end processor for slot validation, AJAX queries, and booking submissions.

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

check_role(['patient']);

$patientId = $_SESSION['patient_id'];

// =========================================================================
// OPERATION 1: AJAX Time-Slot Checker (GET request with action = get_slots)
// =========================================================================
if (isset($_GET['action']) && $_GET['action'] === 'get_slots') {
    header('Content-Type: application/json');
    
    $doctorId = (int)($_GET['doctor_id'] ?? 0);
    $date = sanitize($_GET['date'] ?? '');
    
    if (!$doctorId || empty($date)) {
        echo json_encode(['error' => 'Missing doctor id or date.']);
        exit();
    }
    
    try {
        // Find day of the week (e.g. 'Monday', 'Tuesday', ...)
        $dayOfWeek = date('l', strtotime($date));
        
        // Fetch doctor's work shift for that specific day
        $schStmt = $pdo->prepare("
            SELECT * FROM doctor_schedules 
            WHERE doctor_id = ? AND day_of_week = ? AND status = 'active' 
            LIMIT 1
        ");
        $schStmt->execute([$doctorId, $dayOfWeek]);
        $schedule = $schStmt->fetch();
        
        if (!$schedule) {
            echo json_encode(['slots' => []]); // No slots scheduled for this weekday
            exit();
        }
        
        // Fetch existing bookings on that date (excluding cancelled or rejected slots)
        $appStmt = $pdo->prepare("
            SELECT appointment_time 
            FROM appointments 
            WHERE doctor_id = ? AND appointment_date = ? AND status NOT IN ('Cancelled', 'Rejected')
        ");
        $appStmt->execute([$doctorId, $date]);
        $booked_rows = $appStmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Generate slot array overlaying booked lists
        $slots = generate_time_slots(
            $schedule['start_time'], 
            $schedule['end_time'], 
            $schedule['slot_duration'], 
            $booked_rows
        );
        
        echo json_encode(['slots' => $slots]);
        exit();
        
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error compiling slots: ' . $e->getMessage()]);
        exit();
    }
}

// =========================================================================
// OPERATION 2: Process Booking POST Submissions
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF verification
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error_msg'] = "CSRF verification failed. Request blocked.";
        header("Location: doctors.php");
        exit();
    }
    
    $doctorId = (int)($_POST['doctor_id'] ?? 0);
    $date = sanitize($_POST['appointment_date'] ?? '');
    $time = sanitize($_POST['appointment_time'] ?? '');
    $reason = sanitize($_POST['reason'] ?? '');
    
    if (!$doctorId || empty($date) || empty($time) || empty($reason)) {
        $_SESSION['error_msg'] = "Please complete all scheduler fields.";
        header("Location: doctor-details.php?id=" . $doctorId);
        exit();
    }
    
    try {
        // Double-check: verify slot is not already booked by another active booking
        $chkStmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM appointments 
            WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status NOT IN ('Cancelled', 'Rejected')
        ");
        $chkStmt->execute([$doctorId, $date, $time]);
        
        if ($chkStmt->fetchColumn() > 0) {
            $_SESSION['error_msg'] = "The selected appointment slot has already been booked. Please choose a different slot.";
            header("Location: doctor-details.php?id=" . $doctorId);
            exit();
        }
        
        // Double-check: verify patient has not booked this exact slot twice
        $patChk = $pdo->prepare("
            SELECT COUNT(*) 
            FROM appointments 
            WHERE patient_id = ? AND appointment_date = ? AND appointment_time = ? AND status NOT IN ('Cancelled', 'Rejected')
        ");
        $patChk->execute([$patientId, $date, $time]);
        if ($patChk->fetchColumn() > 0) {
            $_SESSION['error_msg'] = "You already have another appointment scheduled at this exact time.";
            header("Location: doctor-details.php?id=" . $doctorId);
            exit();
        }
        
        // Save the booking
        $stmt = $pdo->prepare("
            INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, status)
            VALUES (?, ?, ?, ?, ?, 'Pending')
        ");
        $stmt->execute([$patientId, $doctorId, $date, $time, $reason]);
        
        $_SESSION['success_msg'] = "Your consultation slot has been booked successfully and is currently pending administrator review.";
        header("Location: appointments.php");
        exit();
        
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Failed to save booking request: " . $e->getMessage();
        header("Location: doctor-details.php?id=" . $doctorId);
        exit();
    }
}

// Fallback redirect
header("Location: doctors.php");
exit();
?>
