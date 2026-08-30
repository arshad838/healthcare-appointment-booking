<?php
// admin/appointments.php
// System-wide Appointments Management.

$page_title = "CareSync Admin - Appointment Booking Management";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

check_role(['admin']);

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

// 1. Process Status Modification Request
if (isset($_GET['action']) && $_GET['action'] === 'update_status' && isset($_GET['id']) && isset($_GET['status'])) {
    $appointmentId = (int)$_GET['id'];
    $newStatus = sanitize($_GET['status']);
    
    $validStatuses = ['Pending', 'Approved', 'Rejected', 'Completed', 'Cancelled'];
    
    if (!in_array($newStatus, $validStatuses)) {
        $_SESSION['error_msg'] = "Invalid appointment status selected.";
    } else {
        try {
            // Update appointment status
            $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $appointmentId]);
            
            $_SESSION['success_msg'] = "Appointment status set to '{$newStatus}' successfully.";
        } catch (PDOException $e) {
            $_SESSION['error_msg'] = "Failed to update status: " . $e->getMessage();
        }
    }
    header("Location: appointments.php");
    exit();
}

// 2. Fetch filters
$status_filter = sanitize($_GET['status'] ?? '');
$doctor_filter = (int)($_GET['doctor_id'] ?? 0);

// Fetch doctors for filter list
$doctors_list = [];
try {
    $doctors_list = $pdo->query("
        SELECT d.id, u.name 
        FROM doctors d 
        JOIN users u ON d.user_id = u.id 
        WHERE d.status = 'active'
        ORDER BY u.name ASC
    ")->fetchAll();
} catch (PDOException $e) {
    // Fail silently for filters
}

// 3. Build query for appointments listing
$query = "
    SELECT a.*, u_p.name AS patient_name, p.phone AS patient_phone, u_d.name AS doctor_name, dep.name AS department_name
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    JOIN users u_p ON p.user_id = u_p.id
    JOIN doctors d ON a.doctor_id = d.id
    JOIN users u_d ON d.user_id = u_d.id
    JOIN departments dep ON d.department_id = dep.id
    WHERE 1=1
";
$params = [];

if (!empty($status_filter)) {
    $query .= " AND a.status = ?";
    $params[] = $status_filter;
}

if ($doctor_filter > 0) {
    $query .= " AND a.doctor_id = ?";
    $params[] = $doctor_filter;
}

$query .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $appointments = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Failed to load clinic bookings: " . $e->getMessage();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 font-outfit fw-bold text-dark-blue">Clinic Appointments</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <span class="badge bg-dark-blue text-white rounded-pill px-3 py-2 fs-8"><i class="fa-solid fa-calendar-check me-1"></i> Patient Booking Records</span>
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

<!-- Filters Form -->
<div class="card border-0 shadow-sm rounded-3 bg-white p-3 mb-4">
    <div class="card-body">
        <form action="appointments.php" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="doctor_id" class="form-label fs-8 text-muted fw-semibold">Filter by Doctor</label>
                <select class="form-select bg-light" id="doctor_id" name="doctor_id">
                    <option value="">All Doctors</option>
                    <?php foreach ($doctors_list as $doc): ?>
                        <option value="<?php echo $doc['id']; ?>" <?php echo $doctor_filter === $doc['id'] ? 'selected' : ''; ?>><?php echo sanitize($doc['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-4">
                <label for="status" class="form-label fs-8 text-muted fw-semibold">Filter by Status</label>
                <select class="form-select bg-light" id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Approved" <?php echo $status_filter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="Completed" <?php echo $status_filter === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="Rejected" <?php echo $status_filter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                    <option value="Cancelled" <?php echo $status_filter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            
            <div class="col-md-4 d-grid gap-2 d-md-flex">
                <button type="submit" class="btn btn-dark-blue text-white fw-semibold w-100 py-2">Apply Filters</button>
                <a href="appointments.php" class="btn btn-outline-secondary fw-semibold w-100 py-2">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Appointments Table -->
<div class="card border-0 shadow-sm rounded-3 bg-white p-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0 text-nowrap">
                <thead class="table-light fs-9 text-uppercase">
                    <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Doctor / Dept</th>
                        <th>Schedule Date</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th class="text-end">Update Status</th>
                    </tr>
                </thead>
                <tbody class="fs-8">
                    <?php if (empty($appointments)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No appointment bookings found matching filters.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($appointments as $app): ?>
                            <tr>
                                <td class="fw-bold">#APP-<?php echo $app['id']; ?></td>
                                <td>
                                    <div class="fw-semibold text-dark fs-7"><?php echo sanitize($app['patient_name']); ?></div>
                                    <div class="text-muted fs-9"><?php echo sanitize($app['patient_phone']); ?></div>
                                </td>
                                <td>
                                    <div class="text-dark"><?php echo sanitize($app['doctor_name']); ?></div>
                                    <div class="text-medical-blue fs-9"><?php echo sanitize($app['department_name']); ?></div>
                                </td>
                                <td>
                                    <div class="text-dark fw-medium"><?php echo format_date($app['appointment_date']); ?></div>
                                    <div class="text-muted fs-9"><?php echo format_time($app['appointment_time']); ?></div>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 180px;" title="<?php echo sanitize($app['reason']); ?>">
                                        <?php echo sanitize($app['reason']); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?php echo get_badge_class($app['status']); ?> px-3 py-1.5 rounded-pill fs-9">
                                        <?php echo $app['status']; ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <?php if ($app['status'] === 'Pending'): ?>
                                        <div class="d-inline-flex gap-2">
                                            <a href="appointments.php?action=update_status&id=<?php echo $app['id']; ?>&status=Approved" 
                                               class="btn btn-sm btn-success rounded-pill px-3 fs-9"
                                               onclick="return confirm('Approve this appointment slot?');">
                                                <i class="fa-solid fa-check me-1"></i> Approve
                                            </a>
                                            <a href="appointments.php?action=update_status&id=<?php echo $app['id']; ?>&status=Rejected" 
                                               class="btn btn-sm btn-danger rounded-pill px-3 fs-9"
                                               onclick="return confirm('Reject this appointment slot?');">
                                                <i class="fa-solid fa-ban me-1"></i> Reject
                                            </a>
                                        </div>
                                    <?php elseif ($app['status'] === 'Approved'): ?>
                                        <div class="d-inline-flex gap-2">
                                            <a href="appointments.php?action=update_status&id=<?php echo $app['id']; ?>&status=Completed" 
                                               class="btn btn-sm btn-info text-dark rounded-pill px-3 fs-9"
                                               onclick="return confirm('Mark this appointment as Completed?');">
                                                <i class="fa-solid fa-circle-check me-1"></i> Complete
                                            </a>
                                            <a href="appointments.php?action=update_status&id=<?php echo $app['id']; ?>&status=Cancelled" 
                                               class="btn btn-sm btn-outline-danger rounded-pill px-3 fs-9"
                                               onclick="return confirm('Cancel this appointment?');">
                                                <i class="fa-solid fa-xmark me-1"></i> Cancel
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted fs-9">No actions available</span>
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
