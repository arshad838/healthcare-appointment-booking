<?php
// admin/schedules.php
// Manage doctor weekly shift schedules.

$page_title = "CareSync Admin - Doctor Schedules";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

check_role(['admin']);

$error = '';
$success = '';

// Session alerts
if (isset($_SESSION['success_msg'])) {
    $success = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}
if (isset($_SESSION['error_msg'])) {
    $error = $_SESSION['error_msg'];
    unset($_SESSION['error_msg']);
}

// 1. Process Shift Addition / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = "CSRF verification failed.";
    } else {
        $doctor_id = (int)$_POST['doctor_id'];
        $day_of_week = sanitize($_POST['day_of_week']);
        $start_time = sanitize($_POST['start_time']);
        $end_time = sanitize($_POST['end_time']);
        $slot_duration = (int)($_POST['slot_duration'] ?? 30);
        $status = sanitize($_POST['status'] ?? 'active');

        if (empty($doctor_id) || empty($day_of_week) || empty($start_time) || empty($end_time)) {
            $error = "All fields are required to save the schedule.";
        } elseif (strtotime($start_time) >= strtotime($end_time)) {
            $error = "The shift start time must be earlier than the end time.";
        } else {
            try {
                // Insert or Update ON DUPLICATE KEY using the unique index (doctor_id, day_of_week)
                $query = "
                    INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time, slot_duration, status)
                    VALUES (?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        start_time = VALUES(start_time),
                        end_time = VALUES(end_time),
                        slot_duration = VALUES(slot_duration),
                        status = VALUES(status)
                ";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$doctor_id, $day_of_week, $start_time, $end_time, $slot_duration, $status]);
                
                $success = "Doctor schedule shift saved successfully.";
            } catch (PDOException $e) {
                $error = "Failed to save doctor schedule: " . $e->getMessage();
            }
        }
    }
}

// Handle Delete/Deactivate Action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $scheduleId = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM doctor_schedules WHERE id = ?");
        $stmt->execute([$scheduleId]);
        $_SESSION['success_msg'] = "Schedule shift deleted successfully.";
        header("Location: schedules.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Failed to delete schedule shift: " . $e->getMessage();
        header("Location: schedules.php");
        exit();
    }
}

// Fetch doctors list for selection
$doctors_list = [];
try {
    $doctors_list = $pdo->query("
        SELECT d.id, u.name, d.specialization 
        FROM doctors d 
        JOIN users u ON d.user_id = u.id 
        WHERE d.status = 'active'
        ORDER BY u.name ASC
    ")->fetchAll();
} catch (PDOException $e) {
    $error = "Failed to load doctors list: " . $e->getMessage();
}

// Fetch all schedule listings
$schedules = [];
try {
    $schedules = $pdo->query("
        SELECT ds.*, u.name AS doctor_name, d.specialization, dep.name AS department_name
        FROM doctor_schedules ds
        JOIN doctors d ON ds.doctor_id = d.id
        JOIN users u ON d.user_id = u.id
        JOIN departments dep ON d.department_id = dep.id
        ORDER BY FIELD(ds.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), ds.start_time ASC
    ")->fetchAll();
} catch (PDOException $e) {
    $error = "Failed to load schedules: " . $e->getMessage();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 font-outfit fw-bold text-dark-blue">Doctor Shifts & Schedules</h1>
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

<div class="row g-4">
    <!-- Schedule Creation Form -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <div class="card-body">
                <h5 class="card-title font-outfit fw-bold text-dark-blue mb-4 border-bottom pb-2">
                    <i class="fa-solid fa-clock-rotate-left text-medical-blue me-2"></i>Configure Shift
                </h5>
                
                <form action="schedules.php" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                    <div class="mb-3">
                        <label for="doctor_id" class="form-label fs-8 text-muted fw-semibold">Select Doctor</label>
                        <select class="form-select" id="doctor_id" name="doctor_id" required>
                            <option value="" disabled selected>Select Doctor...</option>
                            <?php foreach ($doctors_list as $doc): ?>
                                <option value="<?php echo $doc['id']; ?>"><?php echo sanitize($doc['name']); ?> (<?php echo sanitize($doc['specialization']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Please select a doctor.</div>
                    </div>

                    <div class="mb-3">
                        <label for="day_of_week" class="form-label fs-8 text-muted fw-semibold">Day of the Week</label>
                        <select class="form-select" id="day_of_week" name="day_of_week" required>
                            <option value="" disabled selected>Select Day...</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                        </select>
                        <div class="invalid-feedback">Please select a day.</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_time" class="form-label fs-8 text-muted fw-semibold">Start Time</label>
                            <input type="time" class="form-control" id="start_time" name="start_time" required>
                            <div class="invalid-feedback">Provide start time.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_time" class="form-label fs-8 text-muted fw-semibold">End Time</label>
                            <input type="time" class="form-control" id="end_time" name="end_time" required>
                            <div class="invalid-feedback">Provide end time.</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="slot_duration" class="form-label fs-8 text-muted fw-semibold">Slot Duration</label>
                            <select class="form-select" id="slot_duration" name="slot_duration" required>
                                <option value="15">15 minutes</option>
                                <option value="30" selected>30 minutes</option>
                                <option value="45">45 minutes</option>
                                <option value="60">60 minutes</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="status" class="form-label fs-8 text-muted fw-semibold">Shift Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-grid pt-2">
                        <button type="submit" class="btn btn-medical-blue text-white fw-semibold py-2">
                            Save Shift Schedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Schedules Table Column -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <div class="card-body">
                <h5 class="card-title font-outfit fw-bold text-dark-blue mb-4 border-bottom pb-2">Active Shifts Directory</h5>
                <div class="table-responsive">
                    <table class="table align-middle table-hover mb-0 text-nowrap">
                        <thead class="table-light fs-9 text-uppercase">
                            <tr>
                                <th>Doctor</th>
                                <th>Department</th>
                                <th>Work Day</th>
                                <th>Hours (Shift)</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="fs-8">
                            <?php if (empty($schedules)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">No shifts scheduled yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($schedules as $sch): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark"><?php echo sanitize($sch['doctor_name']); ?></div>
                                            <div class="text-muted fs-9"><?php echo sanitize($sch['specialization']); ?></div>
                                        </td>
                                        <td><?php echo sanitize($sch['department_name']); ?></td>
                                        <td class="fw-medium text-dark-blue"><?php echo $sch['day_of_week']; ?></td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?php echo format_time($sch['start_time']); ?> - <?php echo format_time($sch['end_time']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $sch['slot_duration']; ?> mins</td>
                                        <td>
                                            <span class="badge bg-opacity-15 px-3 py-1.5 rounded-pill fs-9 <?php echo $sch['status'] === 'active' ? 'bg-success text-success' : 'bg-danger text-danger'; ?>">
                                                <?php echo ucfirst($sch['status']); ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="schedules.php?action=delete&id=<?php echo $sch['id']; ?>" 
                                               class="btn btn-outline-danger btn-sm rounded-circle d-flex align-items-center justify-content-center ms-auto" 
                                               style="width:32px; height:32px;" 
                                               onclick="return confirm('Are you sure you want to delete this shift? This will not affect existing bookings.');" 
                                               title="Delete Shift">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
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
