<?php
// patient/doctor-details.php
// Specialist details and booking scheduler.

$page_title = "CareSync Patient - Book Specialist";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

check_role(['patient']);

$doctorId = (int)($_GET['id'] ?? 0);
if (!$doctorId) {
    header("Location: doctors.php");
    exit();
}

$error = '';
$doctor = null;
$schedules = [];

try {
    // 1. Fetch doctor details
    $stmt = $pdo->prepare("
        SELECT d.*, u.name AS doctor_name, u.email, dep.name AS department_name, dep.description AS department_description
        FROM doctors d
        JOIN users u ON d.user_id = u.id
        JOIN departments dep ON d.department_id = dep.id
        WHERE d.id = ? AND d.status = 'active' LIMIT 1
    ");
    $stmt->execute([$doctorId]);
    $doctor = $stmt->fetch();
    
    if (!$doctor) {
        $_SESSION['error_msg'] = "Doctor profile not found.";
        header("Location: doctors.php");
        exit();
    }

    // 2. Fetch doctor active schedules/shifts
    $stmt = $pdo->prepare("SELECT * FROM doctor_schedules WHERE doctor_id = ? AND status = 'active' ORDER BY day_of_week ASC");
    $stmt->execute([$doctorId]);
    $schedules = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = "Failed to load doctor profile: " . $e->getMessage();
}

// Tomorrow date configuration for date field limits
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$maxDate = date('Y-m-d', strtotime('+30 days')); // limit bookings to 30 days in advance
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 font-outfit fw-bold text-dark-blue">Doctor Consultation Scheduler</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="doctors.php" class="btn btn-outline-secondary rounded-pill px-4 py-2 fs-8 fw-semibold">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Directory
        </a>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="row g-4">
    <!-- Doctor Profile Column -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <div class="card-body text-center">
                <div class="mb-3 position-relative d-inline-block">
                    <div class="rounded-circle bg-medical-light d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px;">
                        <i class="fa-solid fa-user-md display-3 text-medical-blue"></i>
                    </div>
                </div>
                <h4 class="font-outfit fw-bold mb-1 text-dark-blue"><?php echo sanitize($doctor['doctor_name']); ?></h4>
                <p class="text-medical-blue fs-8 fw-semibold mb-2"><?php echo sanitize($doctor['specialization']); ?></p>
                <span class="badge bg-medical-light text-medical-blue rounded-pill px-3 py-1 fs-9 mb-4 fw-semibold text-uppercase"><?php echo sanitize($doctor['department_name']); ?></span>
                
                <div class="text-start border-top pt-3 fs-8">
                    <h6 class="font-outfit fw-bold text-dark-blue mb-2">Qualifications & Experience</h6>
                    <p class="text-muted mb-2"><i class="fa-solid fa-graduation-cap text-medical-blue me-2"></i> <?php echo sanitize($doctor['qualification']); ?></p>
                    <p class="text-muted mb-3"><i class="fa-solid fa-briefcase text-medical-blue me-2"></i> <?php echo $doctor['experience']; ?> Years clinical experience</p>
                    
                    <h6 class="font-outfit fw-bold text-dark-blue mb-2">Consultation Rate</h6>
                    <p class="text-success fw-bold fs-6 mb-3"><i class="fa-solid fa-receipt me-2 text-muted"></i> $<?php echo number_format($doctor['consultation_fee'], 2); ?></p>
                    
                    <h6 class="font-outfit fw-bold text-dark-blue mb-2">Clinical Location Contact</h6>
                    <p class="text-muted mb-0"><i class="fa-solid fa-phone text-medical-blue me-2"></i> <?php echo sanitize($doctor['phone']); ?></p>
                </div>
            </div>
        </div>

        <!-- Weekly Availability Card -->
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <div class="card-body">
                <h5 class="card-title font-outfit fw-bold text-dark-blue mb-3 border-bottom pb-2">
                    <i class="fa-solid fa-business-time text-medical-blue me-2"></i>Weekly Working Days
                </h5>
                <ul class="list-group list-group-flush fs-8">
                    <?php if (empty($schedules)): ?>
                        <li class="list-group-item text-center text-muted px-0">No active work shifts listed.</li>
                    <?php else: ?>
                        <?php foreach ($schedules as $sch): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span class="fw-semibold text-dark-blue"><?php echo $sch['day_of_week']; ?></span>
                                <span class="badge bg-light text-dark border">
                                    <?php echo format_time($sch['start_time']); ?> - <?php echo format_time($sch['end_time']); ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Booking Form Column -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="card-body">
                <h4 class="font-outfit fw-bold text-dark-blue mb-2"><i class="fa-solid fa-calendar-check text-medical-blue me-2"></i>Schedule Appointment</h4>
                <p class="text-muted fs-8 mb-4 border-bottom pb-2">Provide a reason and select an available time-slot to register your appointment request.</p>

                <form action="book-appointment.php" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="doctor_id" id="doctor_id" value="<?php echo $doctor['id']; ?>">

                    <!-- 1. Select Date -->
                    <div class="mb-3">
                        <label for="appointment_date" class="form-label fs-8 text-muted fw-semibold">Choose Appointment Date</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-calendar text-muted"></i></span>
                            <input type="date" class="form-control" id="appointment_date" name="appointment_date" min="<?php echo $tomorrow; ?>" max="<?php echo $maxDate; ?>" required>
                        </div>
                        <div class="invalid-feedback">Please choose a valid booking date.</div>
                        <small class="form-text text-muted fs-9">Appointments can be scheduled up to 30 days in advance.</small>
                    </div>

                    <!-- 2. Select Time-Slot Grid -->
                    <div class="mb-4">
                        <label class="form-label fs-8 text-muted fw-semibold d-block">Available Time Slots</label>
                        
                        <!-- Spinner loader -->
                        <div id="slots-loading" class="d-none text-center py-3">
                            <div class="spinner-border text-medical-blue spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading slots...</span>
                            </div>
                            <span class="ms-2 fs-8 text-muted">Searching doctor schedule...</span>
                        </div>

                        <!-- Slots injection container -->
                        <div id="slots-container" class="bg-light p-3 rounded-3 border">
                            <p class="text-muted fs-8 text-center my-3"><i class="fa-solid fa-calendar me-1"></i> Please choose a date to display available slots.</p>
                        </div>
                    </div>

                    <!-- 3. Reason for Visit -->
                    <div class="mb-4">
                        <label for="reason" class="form-label fs-8 text-muted fw-semibold">Reason for Consultation</label>
                        <textarea class="form-control" id="reason" name="reason" rows="4" placeholder="Briefly describe your symptoms or reason for visit (e.g. checkup, prescription renewal, joint pain)..." required></textarea>
                        <div class="invalid-feedback">Please provide a reason for scheduling this visit.</div>
                    </div>

                    <!-- 4. Submit Button -->
                    <div class="d-grid">
                        <button type="submit" id="submit-booking-btn" class="btn btn-medical-blue text-white btn-lg fw-semibold py-3 rounded-pill shadow-sm" disabled>
                            <i class="fa-solid fa-calendar-plus me-1"></i> Confirm Appointment Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
require_once __DIR__ . '/../includes/footer.php';
?>
