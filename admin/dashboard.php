<?php
// admin/dashboard.php
// Administrator management dashboard.

$page_title = "CareSync Admin - Dashboard";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

// Access Control
check_role(['admin']);

// Metrics Initialisation
$total_doctors = 0;
$total_patients = 0;
$total_appointments = 0;
$pending_app = 0;
$approved_app = 0;
$completed_app = 0;

$recent_appointments = [];
$recent_patients = [];

try {
    // 1. Fetch KPI metrics counts
    $total_doctors = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
    $total_patients = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
    
    $total_appointments = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
    $pending_app = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'Pending'")->fetchColumn();
    $approved_app = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'Approved'")->fetchColumn();
    $completed_app = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'Completed'")->fetchColumn();

    // 2. Fetch 5 Recent Appointments
    $appQuery = "
        SELECT a.*, p.phone AS patient_phone, u_p.name AS patient_name, u_d.name AS doctor_name
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        JOIN users u_p ON p.user_id = u_p.id
        JOIN doctors d ON a.doctor_id = d.id
        JOIN users u_d ON d.user_id = u_d.id
        ORDER BY a.created_at DESC
        LIMIT 5
    ";
    $recent_appointments = $pdo->query($appQuery)->fetchAll();

    // 3. Fetch 5 Recent Patients
    $patQuery = "
        SELECT p.*, u.name, u.email, u.status
        FROM patients p
        JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC
        LIMIT 5
    ";
    $recent_patients = $pdo->query($patQuery)->fetchAll();

} catch (PDOException $e) {
    $error_msg = "Error loading dashboard metrics: " . $e->getMessage();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 font-outfit fw-bold text-dark-blue">Administrator Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <span class="badge bg-dark-blue text-white rounded-pill px-3 py-2 fs-8"><i class="fa-solid fa-clock-rotate-left me-1"></i> Live System View</span>
    </div>
</div>

<?php if (isset($error_msg)): ?>
    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
<?php endif; ?>

<!-- Metrics Row -->
<div class="row g-4 mb-4">
    <!-- Total Doctors Card -->
    <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
        <div class="card card-metric bg-white border border-light shadow-xs h-100 p-2">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary me-3">
                    <i class="fa-solid fa-user-doctor fs-3"></i>
                </div>
                <div>
                    <h6 class="card-subtitle text-muted fs-9 text-uppercase fw-semibold mb-1">Total Doctors</h6>
                    <h3 class="card-title font-outfit fw-bold mb-0 text-dark-blue"><?php echo $total_doctors; ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Patients Card -->
    <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
        <div class="card card-metric bg-white border border-light shadow-xs h-100 p-2">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 p-3 bg-success bg-opacity-10 text-success me-3">
                    <i class="fa-solid fa-hospital-user fs-3"></i>
                </div>
                <div>
                    <h6 class="card-subtitle text-muted fs-9 text-uppercase fw-semibold mb-1">Total Patients</h6>
                    <h3 class="card-title font-outfit fw-bold mb-0 text-dark-blue"><?php echo $total_patients; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Appointments Card -->
    <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
        <div class="card card-metric bg-white border border-light shadow-xs h-100 p-2">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 p-3 bg-info bg-opacity-10 text-info me-3">
                    <i class="fa-solid fa-calendar-check fs-3"></i>
                </div>
                <div>
                    <h6 class="card-subtitle text-muted fs-9 text-uppercase fw-semibold mb-1">Bookings</h6>
                    <h3 class="card-title font-outfit fw-bold mb-0 text-dark-blue"><?php echo $total_appointments; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Card -->
    <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
        <div class="card card-metric bg-white border border-light shadow-xs h-100 p-2">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning me-3">
                    <i class="fa-solid fa-hourglass-half fs-3"></i>
                </div>
                <div>
                    <h6 class="card-subtitle text-muted fs-9 text-uppercase fw-semibold mb-1">Pending</h6>
                    <h3 class="card-title font-outfit fw-bold mb-0 text-dark-blue"><?php echo $pending_app; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Approved Card -->
    <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
        <div class="card card-metric bg-white border border-light shadow-xs h-100 p-2">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 p-3 bg-success bg-opacity-10 text-success me-3">
                    <i class="fa-solid fa-circle-check fs-3"></i>
                </div>
                <div>
                    <h6 class="card-subtitle text-muted fs-9 text-uppercase fw-semibold mb-1">Approved</h6>
                    <h3 class="card-title font-outfit fw-bold mb-0 text-dark-blue"><?php echo $approved_app; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Completed Card -->
    <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
        <div class="card card-metric bg-white border border-light shadow-xs h-100 p-2">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 p-3 bg-secondary bg-opacity-10 text-secondary me-3">
                    <i class="fa-solid fa-circle-check fs-3"></i>
                </div>
                <div>
                    <h6 class="card-subtitle text-muted fs-9 text-uppercase fw-semibold mb-1">Completed</h6>
                    <h3 class="card-title font-outfit fw-bold mb-0 text-dark-blue"><?php echo $completed_app; ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Chart Column -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100 p-3">
            <div class="card-body">
                <h5 class="card-title font-outfit fw-bold mb-3 text-dark-blue">Appointment Statuses</h5>
                <div style="position: relative; height:280px; width:100%">
                    <canvas id="appointmentsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Appointments Column -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100 p-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title font-outfit fw-bold m-0 text-dark-blue">Recent Appointment Activity</h5>
                    <a href="appointments.php" class="btn btn-link text-medical-blue text-decoration-none fs-8 fw-semibold p-0">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 text-nowrap table-hover">
                        <thead class="table-light fs-9 text-uppercase">
                            <tr>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Schedule</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody class="fs-8">
                            <?php if (empty($recent_appointments)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No appointments booked yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_appointments as $app): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark"><?php echo sanitize($app['patient_name']); ?></div>
                                            <div class="text-muted fs-9"><?php echo sanitize($app['patient_phone']); ?></div>
                                        </td>
                                        <td class="text-dark"><?php echo sanitize($app['doctor_name']); ?></td>
                                        <td>
                                            <div><?php echo format_date($app['appointment_date']); ?></div>
                                            <div class="text-muted fs-9"><?php echo format_time($app['appointment_time']); ?></div>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo get_badge_class($app['status']); ?> px-2 py-1 fs-9 rounded-pill">
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
</div>

<div class="row g-4">
    <!-- Recent Patients -->
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title font-outfit fw-bold m-0 text-dark-blue">Recently Registered Patients</h5>
                    <a href="patients.php" class="btn btn-link text-medical-blue text-decoration-none fs-8 fw-semibold p-0">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 text-nowrap table-hover">
                        <thead class="table-light fs-9 text-uppercase">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Gender / Age</th>
                                <th>Date Registered</th>
                            </tr>
                        </thead>
                        <tbody class="fs-8">
                            <?php if (empty($recent_patients)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">No patients registered.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_patients as $pat): 
                                    $age = date_diff(date_create($pat['date_of_birth']), date_create('today'))->y;
                                ?>
                                    <tr>
                                        <td class="fw-semibold text-dark"><?php echo sanitize($pat['name']); ?></td>
                                        <td class="text-dark"><?php echo sanitize($pat['email']); ?></td>
                                        <td class="text-dark"><?php echo sanitize($pat['phone']); ?></td>
                                        <td><?php echo $pat['gender']; ?> (<?php echo $age; ?> yrs)</td>
                                        <td class="text-muted"><?php echo format_date($pat['created_at']); ?></td>
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

<!-- Chart Script Injection -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('appointmentsChart').getContext('2d');
        const appointmentsChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Approved', 'Completed', 'Rejected', 'Cancelled'],
                datasets: [{
                    label: 'Bookings Distribution',
                    data: [
                        <?php echo $pending_app; ?>,
                        <?php echo $approved_app; ?>,
                        <?php echo $completed_app; ?>,
                        <?php echo $total_appointments - ($pending_app + $approved_app + $completed_app + $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'Cancelled'")->fetchColumn()); // placeholder math for rejected
                        ?>,
                        <?php echo $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'Cancelled'")->fetchColumn(); ?>
                    ],
                    backgroundColor: [
                        '#eab308', // Pending - Yellow
                        '#22c55e', // Approved - Green
                        '#06b6d4', // Completed - Cyan
                        '#ef4444', // Rejected - Red
                        '#64748b'  // Cancelled - Grey
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                family: 'Inter',
                                size: 11
                            },
                            color: '#1e293b'
                        }
                    }
                },
                cutout: '65%'
            }
        });
    });
</script>

<?php 
require_once __DIR__ . '/../includes/footer.php';
?>
