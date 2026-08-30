<?php
// doctor/patients.php
// List of patients seen by this doctor.

$page_title = "CareSync Doctor - My Patients";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

check_role(['doctor']);

$doctorId = $_SESSION['doctor_id'];
$error = '';
$patients = [];

try {
    // Fetch unique patients who have scheduled at least one appointment with this doctor
    $query = "
        SELECT p.*, u.name, u.email, 
               COUNT(a.id) AS total_appointments,
               MAX(a.appointment_date) AS last_visit
        FROM patients p
        JOIN users u ON p.user_id = u.id
        JOIN appointments a ON p.id = a.patient_id
        WHERE a.doctor_id = ?
        GROUP BY p.id, u.name, u.email
        ORDER BY u.name ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$doctorId]);
    $patients = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Failed to load patient records: " . $e->getMessage();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 font-outfit fw-bold text-dark-blue">My Patients</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <span class="badge bg-dark-blue text-white rounded-pill px-3 py-2 fs-8"><i class="fa-solid fa-users me-1"></i> Patient History Catalog</span>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<!-- Patients Registry Table -->
<div class="card border-0 shadow-sm rounded-3 bg-white p-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0 text-nowrap">
                <thead class="table-light fs-9 text-uppercase">
                    <tr>
                        <th>Patient Name</th>
                        <th>Email Address</th>
                        <th>Phone</th>
                        <th>Gender / DOB (Age)</th>
                        <th class="text-center">Total Bookings</th>
                        <th>Last Consultation</th>
                    </tr>
                </thead>
                <tbody class="fs-8">
                    <?php if (empty($patients)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No patients have scheduled consultations with you yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($patients as $pat): 
                            $age = date_diff(date_create($pat['date_of_birth']), date_create('today'))->y;
                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; flex-shrink: 0;">
                                            <i class="fa-solid fa-hospital-user fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark fs-7"><?php echo sanitize($pat['name']); ?></div>
                                            <div class="text-muted fs-9">Patient ID: #PAT-0<?php echo $pat['id']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-dark"><?php echo sanitize($pat['email']); ?></td>
                                <td class="text-dark"><?php echo sanitize($pat['phone']); ?></td>
                                <td>
                                    <div><?php echo $pat['gender']; ?></div>
                                    <div class="text-muted fs-9"><?php echo format_date($pat['date_of_birth']); ?> (<?php echo $age; ?> yrs)</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border font-outfit fw-bold px-3 py-1.5 fs-8">
                                        <?php echo $pat['total_appointments']; ?>
                                    </span>
                                </td>
                                <td class="text-dark-blue fw-medium">
                                    <?php echo format_date($pat['last_visit']); ?>
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
