<?php
// index.php
// Healthcare Appointment Booking System landing page.

$page_title = "CareSync - Clinical Appointment Scheduling Platform";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/database.php';

// Fetch departments for department section and search filter
$departments = [];
$doctors = [];

try {
    // Get active departments
    $depStmt = $pdo->query("SELECT * FROM departments WHERE status = 'active' ORDER BY name ASC");
    $departments = $depStmt->fetchAll();

    // Get active featured doctors
    $docQuery = "
        SELECT d.*, u.name AS doctor_name, dep.name AS department_name 
        FROM doctors d
        JOIN users u ON d.user_id = u.id
        JOIN departments dep ON d.department_id = dep.id
        WHERE d.status = 'active' AND u.status = 'active'
        LIMIT 3
    ";
    $docStmt = $pdo->query($docQuery);
    $doctors = $docStmt->fetchAll();
} catch (PDOException $e) {
    // Database connection errors handled silently, fallback to empty arrays
}
?>

<!-- 1. Hero Section -->
<section class="hero-section text-white py-5 d-flex align-items-center">
    <div class="container py-5 z-2">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 text-center text-lg-start">
                <span class="badge bg-medical-blue text-white rounded-pill px-3 py-2 mb-3 fs-8 fw-semibold tracking-wider text-uppercase">
                    <i class="fa-solid fa-star me-1 text-warning"></i> Leading Digital Clinic Platform
                </span>
                <h1 class="display-4 font-outfit fw-extrabold text-white lh-sm tracking-tight mb-3">
                    Your Health, Synchronized. <br><span class="text-medical-blue">Book Certified Care.</span>
                </h1>
                <p class="lead text-slate-300 fs-6 fw-normal mb-4">
                    Connect instantly with board-certified physicians, view real-time slot availability, and coordinate your clinic appointments in seconds. Secure, HIPAA-aligned, and efficient.
                </p>
                <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-3">
                    <?php if (isset($_SESSION['role'])): ?>
                        <a href="<?php echo BASE_URL . $_SESSION['role']; ?>/dashboard.php" class="btn btn-medical-blue text-white btn-lg px-4 py-3 rounded-pill shadow fw-semibold font-outfit">
                            <i class="fa-solid fa-gauge-high me-1"></i> Access Your Dashboard
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>auth/register.php" class="btn btn-medical-blue text-white btn-lg px-4 py-3 rounded-pill shadow fw-semibold font-outfit">
                            <i class="fa-solid fa-calendar-check me-1"></i> Book Appointment Now
                        </a>
                        <a href="<?php echo BASE_URL; ?>auth/login.php" class="btn btn-outline-light btn-lg px-4 py-3 rounded-pill fw-semibold font-outfit">
                            Patient Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6 text-center d-none d-lg-block">
                <!-- Medical Illustration Icon Grouping for Premium Design -->
                <div class="position-relative py-5">
                    <div class="bg-medical-blue bg-opacity-10 rounded-circle position-absolute top-50 start-50 translate-middle" style="width: 400px; height: 400px; z-index: -1;"></div>
                    <i class="fa-solid fa-user-shield text-medical-blue position-absolute top-0 start-20 fs-1 shadow-sm p-3 bg-white rounded-circle"></i>
                    <i class="fa-solid fa-hospital-user text-success position-absolute bottom-10 end-10 fs-1 shadow-sm p-3 bg-white rounded-circle"></i>
                    <i class="fa-solid fa-laptop-medical text-warning position-absolute top-50 start-0 fs-1 shadow-sm p-3 bg-white rounded-circle"></i>
                    <i class="fa-solid fa-clock text-danger position-absolute bottom-0 start-50 fs-2 shadow-sm p-2 bg-white rounded-circle"></i>
                    <i class="fa-solid fa-house-medical text-medical-blue display-1 py-5 text-opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 2. Search Doctor Section -->
<section class="py-5 bg-white shadow-xs" id="find-doctor">
    <div class="container my-3">
        <div class="card border-0 shadow-sm p-4 rounded-4 mt-n5 bg-white position-relative z-3">
            <div class="card-body">
                <h4 class="font-outfit fw-bold mb-3 text-dark-blue">
                    <i class="fa-solid fa-magnifying-glass me-2 text-medical-blue"></i>Find and Consult a Specialist
                </h4>
                <form action="<?php echo BASE_URL; ?><?php echo isset($_SESSION['user_id']) ? 'patient/doctors.php' : 'auth/login.php'; ?>" method="GET">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="searchName" name="search" placeholder="Doctor Name or Specialty">
                                <label for="searchName"><i class="fa-solid fa-user-doctor text-muted me-1"></i> Doctor Name / Specialty</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <select class="form-select" id="searchDept" name="dept_id">
                                    <option value="" selected>All Departments</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept['id']; ?>"><?php echo sanitize($dept['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="searchDept"><i class="fa-solid fa-hospital text-muted me-1"></i> Medical Specialty</label>
                            </div>
                        </div>
                        <div class="col-md-3 d-grid">
                            <button type="submit" class="btn btn-dark-blue text-white btn-lg rounded-3 fw-semibold font-outfit py-3">
                                <i class="fa-solid fa-search me-1"></i> Search Doctor
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- 3. Departments / Specialties Section -->
<section class="py-5" id="departments">
    <div class="container my-4">
        <div class="text-center mb-5">
            <h2 class="font-outfit fw-extrabold text-dark-blue">Clinical Departments</h2>
            <p class="text-muted col-lg-6 mx-auto">Explore our range of clinical offerings. Each department is staffed by certified specialists utilizing modern therapeutic methodologies.</p>
        </div>
        
        <div class="row g-4">
            <?php if (empty($departments)): ?>
                <div class="col-12 text-center text-muted">No specialties currently listed.</div>
            <?php else: ?>
                <?php 
                $icons = [
                    'Cardiology' => 'fa-heart-pulse text-danger bg-danger bg-opacity-10',
                    'Pediatrics' => 'fa-baby text-primary bg-primary bg-opacity-10',
                    'Orthopedics' => 'fa-bone text-warning bg-warning bg-opacity-10',
                    'Dermatology' => 'fa-hand-holding-hand text-info bg-info bg-opacity-10',
                    'Neurology' => 'fa-brain text-success bg-success bg-opacity-10'
                ];
                foreach ($departments as $dept): 
                    $iconClass = isset($icons[$dept['name']]) ? $icons[$dept['name']] : 'fa-hand-holding-medical text-medical-blue bg-medical-light';
                ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-feature h-100 p-3 bg-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-3 p-3 me-3 fs-3 d-flex align-items-center justify-content-center <?php echo $iconClass; ?>" style="width: 55px; height: 55px;">
                                        <i class="fa-solid <?php echo explode(' ', $iconClass)[0]; ?>"></i>
                                    </div>
                                    <h4 class="font-outfit fw-bold m-0 fs-5 text-dark-blue"><?php echo sanitize($dept['name']); ?></h4>
                                </div>
                                <p class="text-muted fs-8 mb-0 text-justify"><?php echo sanitize($dept['description']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 4. How It Works Section -->
<section class="py-5 bg-dark-blue text-white" id="how-it-works">
    <div class="container my-4">
        <div class="text-center mb-5">
            <h2 class="font-outfit fw-extrabold text-white">How CareSync Works</h2>
            <p class="text-slate-300 col-lg-6 mx-auto">Get connected with doctors and book appointments in four simple steps.</p>
        </div>

        <div class="row g-4 text-center">
            <div class="col-md-6 col-lg-3">
                <div class="p-3">
                    <div class="rounded-circle bg-medical-blue text-white fs-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <h5 class="font-outfit fw-bold">1. Register Account</h5>
                    <p class="text-slate-400 fs-8 mb-0">Create a patient account with your details to log in securely.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="p-3">
                    <div class="rounded-circle bg-medical-blue text-white fs-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                        <i class="fa-solid fa-search"></i>
                    </div>
                    <h5 class="font-outfit fw-bold">2. Search Doctor</h5>
                    <p class="text-slate-400 fs-8 mb-0">Filter doctors by specialty, fee range, and experience.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="p-3">
                    <div class="rounded-circle bg-medical-blue text-white fs-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <h5 class="font-outfit fw-bold">3. Select Time-Slot</h5>
                    <p class="text-slate-400 fs-8 mb-0">Check actual schedule and choose an empty appointment slot.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="p-3">
                    <div class="rounded-circle bg-medical-blue text-white fs-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                        <i class="fa-solid fa-notes-medical"></i>
                    </div>
                    <h5 class="font-outfit fw-bold">4. Attend Consultation</h5>
                    <p class="text-slate-400 fs-8 mb-0">Get approved instantly and consult with your selected physician.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 5. Featured Doctors -->
<section class="py-5" id="doctors">
    <div class="container my-4">
        <div class="text-center mb-5">
            <h2 class="font-outfit fw-extrabold text-dark-blue">Meet Our Specialists</h2>
            <p class="text-muted col-lg-6 mx-auto">Get treated by professional, vetted, and highly experienced clinical experts.</p>
        </div>

        <div class="row g-4 justify-content-center">
            <?php if (empty($doctors)): ?>
                <div class="col-12 text-center text-muted">No doctors found.</div>
            <?php else: ?>
                <?php foreach ($doctors as $doc): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 text-center p-4 h-100 bg-white">
                            <div class="card-body">
                                <div class="mb-3 position-relative d-inline-block">
                                    <div class="rounded-circle bg-medical-light d-flex align-items-center justify-content-center mx-auto" style="width: 130px; height: 130px;">
                                        <i class="fa-solid fa-user-md display-4 text-medical-blue"></i>
                                    </div>
                                    <span class="position-absolute bottom-0 end-0 badge bg-success border border-white border-3 rounded-circle p-2"><span class="visually-hidden">Active</span></span>
                                </div>
                                <h4 class="font-outfit fw-bold mb-1 fs-5 text-dark-blue"><?php echo sanitize($doc['doctor_name']); ?></h4>
                                <p class="text-medical-blue fs-8 fw-semibold mb-2"><?php echo sanitize($doc['specialization']); ?></p>
                                <span class="badge bg-medical-light text-medical-blue rounded-pill px-3 py-1 fs-9 mb-3"><?php echo sanitize($doc['department_name']); ?></span>
                                <p class="text-muted fs-8 text-justify line-clamp-3 mb-3"><?php echo sanitize($doc['bio']); ?></p>
                                
                                <hr class="my-3">
                                
                                <div class="d-flex justify-content-between align-items-center mb-3 fs-8">
                                    <span class="text-muted"><i class="fa-solid fa-briefcase text-secondary me-1"></i> Experience</span>
                                    <strong class="text-dark"><?php echo $doc['experience']; ?> Years</strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-4 fs-8">
                                    <span class="text-muted"><i class="fa-solid fa-hand-holding-dollar text-secondary me-1"></i> Consultation Fee</span>
                                    <strong class="text-success">$<?php echo number_format($doc['consultation_fee'], 2); ?></strong>
                                </div>
                                
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'patient'): ?>
                                    <a href="<?php echo BASE_URL; ?>patient/doctor-details.php?id=<?php echo $doc['id']; ?>" class="btn btn-medical-blue text-white w-100 py-2 rounded-pill fw-medium fs-8 shadow-sm">
                                        <i class="fa-solid fa-calendar-check me-1"></i> Book Consultation
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo BASE_URL; ?>auth/login.php" class="btn btn-outline-secondary w-100 py-2 rounded-pill fw-medium fs-8">
                                        Login to Book
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 6. Benefits / Call-to-action Section -->
<section class="py-5 bg-light">
    <div class="container my-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <h2 class="font-outfit fw-extrabold text-dark-blue mb-4">Why Patients Trust CareSync</h2>
                
                <div class="d-flex align-items-start mb-4">
                    <div class="rounded-circle p-2 bg-success text-white me-3 fs-5 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; flex-shrink: 0;">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div>
                        <h5 class="font-outfit fw-bold text-dark-blue">100% Certified Specialists</h5>
                        <p class="text-muted fs-8">All registered medical staff undergo verification to guarantee proper qualifications and licensing.</p>
                    </div>
                </div>

                <div class="d-flex align-items-start mb-4">
                    <div class="rounded-circle p-2 bg-success text-white me-3 fs-5 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; flex-shrink: 0;">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div>
                        <h5 class="font-outfit fw-bold text-dark-blue">Real-Time Available Slots</h5>
                        <p class="text-muted fs-8">Eliminates back-and-forth telephone tags. Check live slot grids and secure calendar bookings instantly.</p>
                    </div>
                </div>

                <div class="d-flex align-items-start mb-4">
                    <div class="rounded-circle p-2 bg-success text-white me-3 fs-5 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; flex-shrink: 0;">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div>
                        <h5 class="font-outfit fw-bold text-dark-blue">DevOps Orchestrated Infrastructure</h5>
                        <p class="text-muted fs-8">Deployed via containerized Docker microservices, ready for autoscaling Kubernetes workloads and Jenkins pipelines.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card bg-medical-blue text-white border-0 shadow p-5 rounded-4 text-center">
                    <div class="card-body">
                        <i class="fa-solid fa-heart-pulse text-white display-4 mb-3"></i>
                        <h3 class="font-outfit fw-bold mb-3">Ready to Consult a Specialist?</h3>
                        <p class="text-white text-opacity-75 mb-4">Register in 2 minutes, choose from our listed specialists, and lock in your appointment date today.</p>
                        <?php if (isset($_SESSION['role'])): ?>
                            <a href="<?php echo BASE_URL . $_SESSION['role']; ?>/dashboard.php" class="btn btn-dark-blue text-white btn-lg px-5 py-3 rounded-pill fw-bold shadow-sm font-outfit">
                                Go to Dashboard
                            </a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>auth/register.php" class="btn btn-dark-blue text-white btn-lg px-5 py-3 rounded-pill fw-bold shadow-sm font-outfit">
                                <i class="fa-solid fa-user-plus me-1"></i> Register Free Account
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php 
require_once __DIR__ . '/includes/footer.php';
?>
