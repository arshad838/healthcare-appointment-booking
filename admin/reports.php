<?php
// admin/reports.php
// Systems operational reports.

$page_title = "CareSync Admin - Reports & Analytics";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

check_role(['admin']);

$error = '';
$dept_reports = [];
$status_reports = [];
$total_revenue = 0;

try {
    // 1. Fetch reports by department: appointment count and consult fee revenue from Completed bookings
    $deptQuery = "
        SELECT dep.name AS department_name, 
               COUNT(a.id) AS total_appointments, 
               SUM(CASE WHEN a.status = 'Completed' THEN d.consultation_fee ELSE 0 END) AS total_revenue
        FROM departments dep
        LEFT JOIN doctors d ON dep.id = d.department_id
        LEFT JOIN appointments a ON d.id = a.doctor_id
        GROUP BY dep.id, dep.name
        ORDER BY total_appointments DESC
    ";
    $dept_reports = $pdo->query($deptQuery)->fetchAll();

    // Sum overall revenue
    foreach ($dept_reports as $rep) {
        $total_revenue += $rep['total_revenue'];
    }

    // 2. Fetch appointments ratios by status
    $statusQuery = "
        SELECT status, COUNT(*) AS status_count
        FROM appointments
        GROUP BY status
    ";
    $status_reports = $pdo->query($statusQuery)->fetchAll();

} catch (PDOException $e) {
    $error = "Failed to compile system analytics: " . $e->getMessage();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom d-print-none">
    <h1 class="h2 font-outfit fw-bold text-dark-blue">Reports & Analytics</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button onclick="window.print()" class="btn btn-outline-dark rounded-pill px-4 py-2 fs-8 fw-semibold shadow-xs">
            <i class="fa-solid fa-print me-1"></i> Print / Save PDF
        </button>
    </div>
</div>

<!-- Print Header (Visible only when printing) -->
<div class="d-none d-print-block mb-4 text-center">
    <h2 class="font-outfit fw-bold text-dark-blue">CareSync Health Systems</h2>
    <h4 class="text-muted">Clinical Systems Operations Report</h4>
    <p class="fs-8 text-muted">Generated on: <?php echo date('F d, Y h:i A'); ?> | Role: System Administrator</p>
    <hr>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger d-print-none"><?php echo $error; ?></div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <!-- Revenue Card -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2 text-muted fs-8 fw-semibold text-uppercase">
                    <i class="fa-solid fa-file-invoice-dollar text-success me-2 fs-5"></i> Total Clinic Revenue
                </div>
                <h2 class="font-outfit fw-bold text-success mb-0">$<?php echo number_format($total_revenue, 2); ?></h2>
                <p class="fs-9 text-muted mt-2 mb-0">Compiled from all completed patient consultations.</p>
            </div>
        </div>
    </div>
    
    <!-- Total Bookings Card -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2 text-muted fs-8 fw-semibold text-uppercase">
                    <i class="fa-solid fa-calendar-check text-medical-blue me-2 fs-5"></i> Total Appt Records
                </div>
                <h2 class="font-outfit fw-bold text-dark-blue mb-0">
                    <?php 
                    $tot = 0;
                    foreach($status_reports as $sr) $tot += $sr['status_count'];
                    echo $tot;
                    ?>
                </h2>
                <p class="fs-9 text-muted mt-2 mb-0">Total appointments processed through the system.</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Specialty Performance Table -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <div class="card-body">
                <h5 class="card-title font-outfit fw-bold text-dark-blue mb-4 border-bottom pb-2">
                    <i class="fa-solid fa-hospital text-medical-blue me-2"></i>Specialty Department Diagnostics
                </h5>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light fs-9 text-uppercase">
                            <tr>
                                <th>Department</th>
                                <th class="text-center">Total Bookings</th>
                                <th class="text-end">Revenue Generated</th>
                            </tr>
                        </thead>
                        <tbody class="fs-8">
                            <?php if (empty($dept_reports)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">No specialty details compiled.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($dept_reports as $rep): ?>
                                    <tr>
                                        <td class="fw-semibold text-dark"><?php echo sanitize($rep['department_name']); ?></td>
                                        <td class="text-center text-dark fw-medium"><?php echo $rep['total_appointments']; ?></td>
                                        <td class="text-end text-success fw-bold">$<?php echo number_format($rep['total_revenue'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Status Ratios -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <div class="card-body">
                <h5 class="card-title font-outfit fw-bold text-dark-blue mb-4 border-bottom pb-2">
                    <i class="fa-solid fa-chart-pie text-medical-blue me-2"></i>Status Summaries
                </h5>
                <ul class="list-group list-group-flush fs-8">
                    <?php if (empty($status_reports)): ?>
                        <li class="list-group-item text-center text-muted">No appointments found.</li>
                    <?php else: ?>
                        <?php foreach ($status_reports as $sr): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>
                                    <span class="badge <?php echo get_badge_class($sr['status']); ?> rounded-circle p-1.5 me-2"><span class="visually-hidden">Bullet</span></span>
                                    <?php echo $sr['status']; ?>
                                </span>
                                <span class="badge bg-light text-dark border font-outfit fw-bold fs-8"><?php echo $sr['status_count']; ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php 
require_once __DIR__ . '/../includes/footer.php';
?>
