<?php
// patient/appointments.php
// View and manage patient's bookings.

$page_title = "CareSync Patient - My Bookings";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

check_role(['patient']);

$patientId = $_SESSION['patient_id'];

$success = '';
$error = '';

// Session alerts
if (isset($_SESSION['success_msg'])) {
    $success = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}
if (isset($_SESSION['error_msg'])) {
    $error = $_SESSION['error_msg'];
    unset($_SESSION['error_msg']);
}

// Handle booking cancellation request
if (isset($_GET['action']) && $_GET['action'] === 'cancel' && isset($_GET['id'])) {
    $appointmentId = (int)$_GET['id'];
    
    try {
        // Confirm patient owns this appointment before canceling
        $chk = $pdo->prepare("SELECT status FROM appointments WHERE id = ? AND patient_id = ? LIMIT 1");
        $chk->execute([$appointmentId, $patientId]);
        $app = $chk->fetch();
        
        if ($app) {
            if ($app['status'] === 'Completed' || $app['status'] === 'Rejected') {
                $_SESSION['error_msg'] = "Completed or Rejected bookings cannot be cancelled.";
            } else {
                $stmt = $pdo->prepare("UPDATE appointments SET status = 'Cancelled' WHERE id = ?");
                $stmt->execute([$appointmentId]);
                $_SESSION['success_msg'] = "Your appointment booking has been cancelled successfully.";
            }
        } else {
            $_SESSION['error_msg'] = "Record access error. Booking not found.";
        }
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Failed to cancel appointment: " . $e->getMessage();
    }
    
    header("Location: appointments.php");
    exit();
}

// Fetch bookings
$appointments = [];
try {
    $query = "
        SELECT a.*, u.name AS doctor_name, d.specialization, d.consultation_fee, dep.name AS department_name
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.id
        JOIN users u ON d.user_id = u.id
        JOIN departments dep ON d.department_id = dep.id
        WHERE a.patient_id = ?
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$patientId]);
    $appointments = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Failed to load booking history: " . $e->getMessage();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 font-outfit fw-bold text-dark-blue">My Appointment Bookings</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="doctors.php" class="btn btn-medical-blue text-white rounded-pill px-4 py-2 fs-8 fw-semibold shadow-xs">
            <i class="fa-solid fa-plus me-1"></i> Book Appointment
        </a>
    </div>
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

<!-- Bookings List Table -->
<div class="card border-0 shadow-sm rounded-3 bg-white p-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0 text-nowrap">
                <thead class="table-light fs-9 text-uppercase">
                    <tr>
                        <th>Booking ID</th>
                        <th>Doctor Name</th>
                        <th>Specialty / Dept</th>
                        <th>Date & Time</th>
                        <th>Fee</th>
                        <th>Status</th>
                        <th>Notes / Directions</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="fs-8">
                    <?php if (empty($appointments)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No appointment bookings recorded.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($appointments as $app): ?>
                            <tr>
                                <td class="fw-bold">#APP-<?php echo $app['id']; ?></td>
                                <td class="fw-semibold text-dark fs-7"><?php echo sanitize($app['doctor_name']); ?></td>
                                <td>
                                    <div><?php echo sanitize($app['specialization']); ?></div>
                                    <div class="text-muted fs-9"><?php echo sanitize($app['department_name']); ?></div>
                                </td>
                                <td>
                                    <div class="text-dark fw-medium"><?php echo format_date($app['appointment_date']); ?></div>
                                    <div class="text-muted fs-9"><?php echo format_time($app['appointment_time']); ?></div>
                                </td>
                                <td class="fw-semibold text-success">$<?php echo number_format($app['consultation_fee'], 2); ?></td>
                                <td>
                                    <span class="badge <?php echo get_badge_class($app['status']); ?> px-3 py-1.5 rounded-pill fs-9">
                                        <?php echo $app['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="text-wrap fs-9 text-muted" style="max-width: 180px;">
                                        <?php echo !empty($app['notes']) ? sanitize($app['notes']) : 'No clinical notes.'; ?>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <?php if ($app['status'] === 'Pending' || $app['status'] === 'Approved'): ?>
                                        <a href="appointments.php?action=cancel&id=<?php echo $app['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger rounded-pill px-3 fs-9"
                                           onclick="return confirm('Are you sure you want to cancel this appointment request?');">
                                            <i class="fa-solid fa-trash-can"></i> Cancel
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted fs-9">N/A</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
require_once __DIR__ . '/../includes/footer.php';
?>
