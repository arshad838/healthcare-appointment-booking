<?php
// doctor/dashboard.php
// Doctor workspace dashboard.

$page_title = "CareSync Doctor - Dashboard";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

check_role(['doctor']);

$doctorId = $_SESSION['doctor_id'];
$error = '';

$today_app = 0;
$upcoming_app = 0;
$pending_app = 0;
$completed_app = 0;

$todays_schedule = [];
$pending_requests = [];

try {
    // 1. Fetch metrics counts
    // Today's appointments count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND appointment_date = CURDATE()");
    $stmt->execute([$doctorId]);
    $today_app = $stmt->fetchColumn();

    // Upcoming appointments count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND appointment_date > CURDATE() AND status = 'Approved'");
    $stmt->execute([$doctorId]);
    $upcoming_app = $stmt->fetchColumn();

    // Pending appointments count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND status = 'Pending'");
    $stmt->execute([$doctorId]);
    $pending_app = $stmt->fetchColumn();

    // Completed appointments count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND status = 'Completed'");
    $stmt->execute([$doctorId]);
    $completed_app = $stmt->fetchColumn();

    // 2. Fetch Today's Appointments List
    $todayQuery = "
        SELECT a.*, u.name AS patient_name, p.phone AS patient_phone, p.gender, p.date_of_birth
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        JOIN users u ON p.user_id = u.id
        WHERE a.doctor_id = ? AND a.appointment_date = CURDATE()
        ORDER BY a.appointment_time ASC
    ";
    $stmt = $pdo->prepare($todayQuery);
    $stmt->execute([$doctorId]);
    $todays_schedule = $stmt->fetchAll();

    // 3. Fetch Pending Requests
    $pendingQuery = "
        SELECT a.*, u.name AS patient_name, p.phone AS patient_phone
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        JOIN users u ON p.user_id = u.id
        WHERE a.doctor_id = ? AND a.status = 'Pending'
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
        LIMIT 5
    ";
    $stmt = $pdo->prepare($pendingQuery);
    $stmt->execute([$doctorId]);
    $pending_requests = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = "Failed to load dashboard statistics: " . $e->getMessage();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 font-outfit fw-bold text-dark-blue">Welcome back, <?php echo sanitize($_SESSION['name']); ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <span class="badge bg-medical-blue text-white rounded-pill px-3 py-2 fs-8"><i class="fa-solid fa-user-md me-1"></i> Doctor Workspace</span>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<!-- Metrics Row -->
<div class="row g-4 mb-4">
    <!-- Today's Appointments Card -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card card-metric bg-white border border-light shadow-xs h-100 p-2">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 p-3 bg-danger bg-opacity-10 text-danger me-3">
                    <i class="fa-solid fa-hospital-user fs-3"></i>
                </div>
                <div>
                    <h6 class="card-subtitle text-muted fs-9 text-uppercase fw-semibold mb-1">Today's List</h6>
                    <h3 class="card-title font-outfit fw-bold mb-0 text-dark-blue"><?php echo $today_app; ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Upcoming Appointments Card -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card card-metric bg-white border border-light shadow-xs h-100 p-2">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 p-3 bg-success bg-opacity-10 text-success me-3">
                    <i class="fa-solid fa-calendar-days fs-3"></i>
                </div>
                <div>
                    <h6 class="card-subtitle text-muted fs-9 text-uppercase fw-semibold mb-1">Upcoming</h6>
                    <h3 class="card-title font-outfit fw-bold mb-0 text-dark-blue"><?php echo $upcoming_app; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Requests Card -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card card-metric bg-white border border-light shadow-xs h-100 p-2">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning me-3">
                    <i class="fa-solid fa-hourglass-half fs-3"></i>
                </div>
                <div>
                    <h6 class="card-subtitle text-muted fs-9 text-uppercase fw-semibold mb-1">Pending Requests</h6>
                    <h3 class="card-title font-outfit fw-bold mb-0 text-dark-blue"><?php echo $pending_app; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Completed Consultations Card -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card card-metric bg-white border border-light shadow-xs h-100 p-2">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 p-3 bg-info bg-opacity-10 text-info me-3">
                    <i class="fa-solid fa-circle-check fs-3"></i>
                </div>
                <div>
                    <h6 class="card-subtitle text-muted fs-9 text-uppercase fw-semibold mb-1">Completed Cases</h6>
                    <h3 class="card-title font-outfit fw-bold mb-0 text-dark-blue"><?php echo $completed_app; ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Today's Schedule -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100 p-3">
            <div class="card-body">
                <h5 class="card-title font-outfit fw-bold mb-3 text-dark-blue">
                    <i class="fa-solid fa-clock text-medical-blue me-2"></i>Today's Clinic Schedule
                </h5>
                
                <div class="table-responsive">
                    <table class="table align-middle table-hover mb-0 text-nowrap">
                        <thead class="table-light fs-9 text-uppercase">
                            <tr>
                                <th>Time Slot</th>
                                <th>Patient Name</th>
                                <th>Reason</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody class="fs-8">
                            <?php if (empty($todays_schedule)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No appointments scheduled for today.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($todays_schedule as $app): 
                                    $age = date_diff(date_create($app['date_of_birth']), date_create('today'))->y;
                                ?>
                                    <tr>
                                        <td class="fw-semibold text-dark-blue"><?php echo format_time($app['appointment_time']); ?></td>
                                        <td>
                                            <div class="fw-semibold text-dark"><?php echo sanitize($app['patient_name']); ?></div>
                                            <div class="text-muted fs-9"><?php echo $app['gender']; ?> (<?php echo $age; ?> yrs)</div>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 150px;" title="<?php echo sanitize($app['reason']); ?>">
                                                <?php echo sanitize($app['reason']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo get_badge_class($app['status']); ?> px-2.5 py-1 rounded-pill fs-9">
                                                <?php echo $app['status']; ?>
                                            </span>
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

    <!-- Recent Pending Requests -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100 p-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title font-outfit fw-bold m-0 text-dark-blue">
                        <i class="fa-solid fa-hourglass text-medical-blue me-2"></i>Pending Booking Requests
                    </h5>
                    <a href="appointments.php" class="btn btn-link text-medical-blue text-decoration-none fs-8 fw-semibold p-0">View All</a>
                </div>
                
                <div class="table-responsive">
                    <table class="table align-middle mb-0 text-nowrap">
                        <thead class="table-light fs-9 text-uppercase">
                            <tr>
                                <th>Patient</th>
                                <th>Schedule Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="fs-8">
                            <?php if (empty($pending_requests)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No pending appointment requests.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pending_requests as $app): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark"><?php echo sanitize($app['patient_name']); ?></div>
                                            <div class="text-muted fs-9"><?php echo sanitize($app['patient_phone']); ?></div>
                                        </td>
                                        <td>
                                            <div><?php echo format_date($app['appointment_date']); ?></div>
                                            <div class="text-muted fs-9"><?php echo format_time($app['appointment_time']); ?></div>
                                        </td>
                                        <td class="text-end">
                                            <a href="appointments.php?action=update_status&id=<?php echo $app['id']; ?>&status=Approved" class="btn btn-success btn-sm rounded-circle" title="Approve"><i class="fa-solid fa-check"></i></a>
                                            <a href="appointments.php?action=update_status&id=<?php echo $app['id']; ?>&status=Rejected" class="btn btn-danger btn-sm rounded-circle" title="Reject"><i class="fa-solid fa-xmark"></i></a>
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
</div>

<?php 
require_once __DIR__ . '/../includes/footer.php';
?>
