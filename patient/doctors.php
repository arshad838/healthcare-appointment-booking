<?php
// patient/doctors.php
// Browse and search active doctors.

$page_title = "CareSync Patient - Browse Doctors";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

check_role(['patient']);

$search = sanitize($_GET['search'] ?? '');
$dept_filter = (int)($_GET['dept_id'] ?? 0);

$error = '';
$doctors = [];
$departments = [];

try {
    // Fetch departments for filter select
    $departments = $pdo->query("SELECT * FROM departments WHERE status = 'active' ORDER BY name ASC")->fetchAll();

    // Query active doctors
    $query = "
        SELECT d.*, u.name AS doctor_name, dep.name AS department_name
        FROM doctors d
        JOIN users u ON d.user_id = u.id
        JOIN departments dep ON d.department_id = dep.id
        WHERE d.status = 'active' AND u.status = 'active'
    ";
    $params = [];

    if (!empty($search)) {
        $query .= " AND (u.name LIKE ? OR d.specialization LIKE ? OR d.qualification LIKE ?)";
        $term = "%{$search}%";
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
    }

    if ($dept_filter > 0) {
        $query .= " AND d.department_id = ?";
        $params[] = $dept_filter;
    }

    $query .= " ORDER BY u.name ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $doctors = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = "Failed to load clinic doctors: " . $e->getMessage();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 font-outfit fw-bold text-dark-blue">Browse Specialists</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <span class="badge bg-dark-blue text-white rounded-pill px-3 py-2 fs-8"><i class="fa-solid fa-user-doctor me-1"></i> Medical Specialists Directory</span>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<!-- Search Filters Card -->
<div class="card border-0 shadow-sm rounded-3 bg-white p-3 mb-4">
    <div class="card-body">
        <form action="doctors.php" method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label for="search" class="form-label fs-8 text-muted fw-semibold">Search Doctors</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" class="form-control bg-light border-start-0" id="search" name="search" value="<?php echo sanitize($search); ?>" placeholder="Search by name, specialty, or credentials...">
                </div>
            </div>
            
            <div class="col-md-4">
                <label for="dept_id" class="form-label fs-8 text-muted fw-semibold">Department Specialty</label>
                <select class="form-select bg-light" id="dept_id" name="dept_id">
                    <option value="">All Specialties</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>" <?php echo $dept_filter === $dept['id'] ? 'selected' : ''; ?>><?php echo sanitize($dept['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3 d-grid gap-2 d-md-flex">
                <button type="submit" class="btn btn-dark-blue text-white fw-semibold w-100 py-2">Apply Filters</button>
                <a href="doctors.php" class="btn btn-outline-secondary fw-semibold w-100 py-2">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Doctor List Grid -->
<div class="row g-4">
    <?php if (empty($doctors)): ?>
        <div class="col-12 text-center text-muted py-5">
            <i class="fa-solid fa-user-slash display-4 text-muted mb-3"></i>
            <p>No specialists found matching your search preferences.</p>
        </div>
    <?php else: ?>
        <?php foreach ($doctors as $doc): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white d-flex flex-column justify-content-between">
                    <div>
                        <div class="text-center mb-3">
                            <div class="rounded-circle bg-medical-light d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 90px; height: 90px;">
                                <i class="fa-solid fa-user-md fs-1 text-medical-blue"></i>
                            </div>
                            <h5 class="font-outfit fw-bold mb-1 text-dark-blue"><?php echo sanitize($doc['doctor_name']); ?></h5>
                            <span class="badge bg-medical-light text-medical-blue rounded-pill px-3 py-1 fs-9 fw-semibold text-uppercase"><?php echo sanitize($doc['department_name']); ?></span>
                        </div>
                        
                        <div class="mb-3 text-center">
                            <small class="text-muted d-block font-outfit fs-8">Specialization</small>
                            <span class="fw-semibold text-dark fs-8"><?php echo sanitize($doc['specialization']); ?></span>
                        </div>

                        <p class="text-muted fs-8 text-justify line-clamp-3 mb-4"><?php echo sanitize($doc['bio']); ?></p>
                        
                        <div class="border-top pt-3 fs-8">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted"><i class="fa-solid fa-briefcase text-muted me-1"></i> Experience:</span>
                                <strong class="text-dark"><?php echo $doc['experience']; ?> Years</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted"><i class="fa-solid fa-graduation-cap text-muted me-1"></i> Qualification:</span>
                                <strong class="text-dark text-truncate" style="max-width: 160px;" title="<?php echo sanitize($doc['qualification']); ?>"><?php echo sanitize($doc['qualification']); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted"><i class="fa-solid fa-dollar-sign text-muted me-1"></i> Consulting Fee:</span>
                                <strong class="text-success">$<?php echo number_format($doc['consultation_fee'], 2); ?></strong>
                            </div>
                        </div>
                    </div>
                    
                    <div class="pt-3 border-top mt-3">
                        <a href="doctor-details.php?id=<?php echo $doc['id']; ?>" class="btn btn-medical-blue text-white w-100 py-2 rounded-pill fw-semibold fs-8 shadow-xs">
                            <i class="fa-solid fa-calendar-check me-1"></i> View & Book Appointment
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php 
require_once __DIR__ . '/../includes/footer.php';
?>
