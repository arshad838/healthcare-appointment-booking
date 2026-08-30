<?php
// patient/dashboard.php
// Patient portal dashboard.

$page_title = "CareSync Patient - Dashboard";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

check_role(['patient']);

$patientId = $_SESSION['patient_id'];
$error = '';

$upcoming_count = 0;
$pending_count = 0;
$completed_count = 0;
$upcoming_appointments = [];

try {
    // 1. Fetch counts
    // Upcoming approved bookings
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id = ? AND appointment_date >= CURDATE() AND status = 'Approved'");
    $stmt->execute([$patientId]);
    $upcoming_count = $stmt->fetchColumn();

    // Pending bookings
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id = ? AND status = 'Pending'");
    $stmt->execute([$patientId]);
    $pending_count = $stmt->fetchColumn();

    // Completed bookings
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id = ? AND status = 'Completed'");
    $stmt->execute([$patientId]);
    $completed_count = $stmt->fetchColumn();

    // 2. Fetch Patient's upcoming bookings
    $appQuery = "
        SELECT a.*, u.name AS doctor_name, d.specialization, dep.name AS department_name
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.id
        JOIN users u ON d.user_id = u.id
        JOIN departments dep ON d.department_id = dep.id
        WHERE a.patient_id = ? AND a.appointment_date >= CURDATE() AND a.status IN ('Pending', 'Approved')
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
        LIMIT 5
    ";
    $stmt = $pdo->prepare($appQuery);
    $stmt->execute([$patientId]);
    $upcoming_appointments = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = "Failed to load dashboard metrics: " . $e->getMessage();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 font-outfit fw-bold text-dark-blue">Welcome, <?php echo sanitize($_SESSION['name']); ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="doctors.php" class="btn btn-medical-blue text-white rounded-pill px-4 py-2 fs-8 fw-semibold shadow-xs">
            <i class="fa-solid fa-search me-1"></i> Book New Consultation
        </a>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<!-- Quick Metrics -->
<div class="row g-4 mb-4">
    <!-- Approved Upcoming Card -->
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="card card-metric bg-white border border-light shadow-xs h-100 p-2">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 p-3 bg-success bg-opacity-10 text-success me-3">
                    <i class="fa-solid fa-calendar-check fs-3"></i>
                </div>
                <div>
                    <h6 class="card-subtitle text-muted fs-9 text-uppercase fw-semibold mb-1">Confirmed Scheduled</h6>
                    <h3 class="card-title font-outfit fw-bold mb-0 text-dark-blue"><?php echo $upcoming_count; ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pending Requests Card -->
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="card card-metric bg-white border border-light shadow-xs h-100 p-2">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning me-3">
                    <i class="fa-solid fa-hourglass-half fs-3"></i>
                </div>
                <div>
                    <h6 class="card-subtitle text-muted fs-9 text-uppercase fw-semibold mb-1">Pending Requests</h6>
                    <h3 class="card-title font-outfit fw-bold mb-0 text-dark-blue"><?php echo $pending_count; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Completed Consultations Card -->
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="card card-metric bg-white border border-light shadow-xs h-100 p-2">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 p-3 bg-info bg-opacity-10 text-info me-3">
                    <i class="fa-solid fa-notes-medical fs-3"></i>
                </div>
                <div>
                    <h6 class="card-subtitle text-muted fs-9 text-uppercase fw-semibold mb-1">Past Consultations</h6>
                    <h3 class="card-title font-outfit fw-bold mb-0 text-dark-blue"><?php echo $completed_count; ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Scheduled Bookings List -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100 p-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title font-outfit fw-bold m-0 text-dark-blue">
                        <i class="fa-solid fa-calendar-days text-medical-blue me-2"></i>My Scheduled Consultations
                    </h5>
                    <a href="appointments.php" class="btn btn-link text-medical-blue text-decoration-none fs-8 fw-semibold p-0">View History</a>
                </div>
                
                <div class="table-responsive">
                    <table class="table align-middle table-hover mb-0 text-nowrap">
                        <thead class="table-light fs-9 text-uppercase">
                            <tr>
                                <th>Doctor / Specialty</th>
                                <th>Schedule Date</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody class="fs-8">
                            <?php if (empty($upcoming_appointments)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No scheduled or pending appointments.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($upcoming_appointments as $app): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark fs-7"><?php echo sanitize($app['doctor_name']); ?></div>
                                            <div class="text-muted fs-9"><?php echo sanitize($app['specialization']); ?></div>
                                        </td>
                                        <td>
                                            <div class="text-dark fw-medium"><?php echo format_date($app['appointment_date']); ?></div>
                                            <div class="text-muted fs-9"><?php echo format_time($app['appointment_time']); ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border"><?php echo sanitize($app['department_name']); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo get_badge_class($app['status']); ?> px-2.5 py-1 rounded-pill fs-9">
                                                <?php echo $app['status']; ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="appointments.php" class="btn btn-outline-secondary btn-sm rounded-pill fs-9 px-3">Manage</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Support/Info Panel -->
    <div class="col-lg-4">
        <div class="card border-0 bg-dark-blue text-white shadow-sm rounded-3 h-100 p-4">
            <div class="card-body d-flex flex-column justify-content-between">
                <div>
                    <h5 class="font-outfit fw-bold mb-3"><i class="fa-solid fa-shield-halved text-medical-blue me-2"></i>Need Assistance?</h5>
                    <p class="fs-8 text-slate-300 text-justify">
                        Consultations must be scheduled at least 24 hours in advance. Once booked, clinic staff will review the doctor availability and approve the request.
                    </p>
                    <p class="fs-8 text-slate-300 text-justify">
                        Need to cancel? You can cancel bookings up to 12 hours before the appointment session via the bookings screen.
                    </p>
                </div>
                <div class="pt-4 border-top border-secondary mt-3">
                    <a href="doctors.php" class="btn btn-medical-blue text-white w-100 rounded-pill fw-semibold py-2">
                        <i class="fa-solid fa-plus me-1"></i> Book Appointment
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
require_once __DIR__ . '/../includes/footer.php';
?>
