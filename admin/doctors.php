<?php
// admin/doctors.php
// Admin list and manage doctors.

$page_title = "CareSync Admin - Doctors Management";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

check_role(['admin']);

$search = sanitize($_GET['search'] ?? '');
$status_filter = sanitize($_GET['status'] ?? '');

$success = '';
$error = '';

if (isset($_SESSION['success_msg'])) {
    $success = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}
if (isset($_SESSION['error_msg'])) {
    $error = $_SESSION['error_msg'];
    unset($_SESSION['error_msg']);
}

// Handle Doctor Status Toggle (Activate/Deactivate)
if (isset($_GET['action']) && $_GET['action'] === 'toggle_status' && isset($_GET['id'])) {
    $doctorId = (int)$_GET['id'];
    
    try {
        // Fetch current status
        $stmt = $pdo->prepare("SELECT status, user_id FROM doctors WHERE id = ?");
        $stmt->execute([$doctorId]);
        $doctor = $stmt->fetch();
        
        if ($doctor) {
            $newStatus = ($doctor['status'] === 'active') ? 'inactive' : 'active';
            
            // Toggle both doctor and base user status
            $pdo->beginTransaction();
            
            $stmt1 = $pdo->prepare("UPDATE doctors SET status = ? WHERE id = ?");
            $stmt1->execute([$newStatus, $doctorId]);
            
            $stmt2 = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt2->execute([$newStatus, $doctor['user_id']]);
            
            $pdo->commit();
            
            $_SESSION['success_msg'] = "Doctor status updated to " . $newStatus . " successfully.";
            header("Location: doctors.php");
            exit();
        } else {
            $_SESSION['error_msg'] = "Doctor record not found.";
            header("Location: doctors.php");
            exit();
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $_SESSION['error_msg'] = "Failed to update doctor status: " . $e->getMessage();
        header("Location: doctors.php");
        exit();
    }
}

// Build Search Query
$query = "
    SELECT d.*, u.name, u.email, dep.name AS department_name
    FROM doctors d
    JOIN users u ON d.user_id = u.id
    JOIN departments dep ON d.department_id = dep.id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $query .= " AND (u.name LIKE ? OR d.specialization LIKE ? OR dep.name LIKE ?)";
    $term = "%{$search}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if (!empty($status_filter)) {
    $query .= " AND d.status = ?";
    $params[] = $status_filter;
}

$query .= " ORDER BY d.created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $doctors = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 font-outfit fw-bold text-dark-blue">Manage Doctors</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="doctor-add.php" class="btn btn-medical-blue text-white rounded-pill px-4 py-2 fs-8 fw-semibold shadow-xs">
            <i class="fa-solid fa-plus me-1"></i> Add Doctor
        </a>
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

<!-- Search Card -->
<div class="card border-0 shadow-sm rounded-3 bg-white p-3 mb-4">
    <div class="card-body">
        <form action="doctors.php" method="GET" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label for="search" class="form-label fs-8 text-muted fw-semibold">Search Doctors</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                    <input type="text" class="form-control bg-light border-start-0" id="search" name="search" value="<?php echo sanitize($search); ?>" placeholder="Search by name, specialty, or department...">
                </div>
            </div>
            
            <div class="col-md-3">
                <label for="status" class="form-label fs-8 text-muted fw-semibold">Status</label>
                <select class="form-select bg-light" id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            
            <div class="col-md-3 d-grid gap-2 d-md-flex">
                <button type="submit" class="btn btn-dark-blue text-white fw-semibold w-100 py-2">Filter</button>
                <a href="doctors.php" class="btn btn-outline-secondary fw-semibold w-100 py-2">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Doctors Table -->
<div class="card border-0 shadow-sm rounded-3 bg-white p-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0 text-nowrap">
                <thead class="table-light fs-9 text-uppercase">
                    <tr>
                        <th>Doctor Details</th>
                        <th>Department</th>
                        <th>Contact</th>
                        <th>Fee</th>
                        <th>Experience</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="fs-8">
                    <?php if (empty($doctors)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No doctors found matching filters.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($doctors as $doc): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-medical-light d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px; flex-shrink:0;">
                                            <i class="fa-solid fa-user-md text-medical-blue fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark fs-7"><?php echo sanitize($doc['name']); ?></div>
                                            <div class="text-medical-blue fs-9"><?php echo sanitize($doc['specialization']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1"><?php echo sanitize($doc['department_name']); ?></span>
                                </td>
                                <td>
                                    <div><i class="fa-solid fa-envelope text-muted me-1"></i> <?php echo sanitize($doc['email']); ?></div>
                                    <div><i class="fa-solid fa-phone text-muted me-1"></i> <?php echo sanitize($doc['phone']); ?></div>
                                </td>
                                <td class="fw-bold text-success">$<?php echo number_format($doc['consultation_fee'], 2); ?></td>
                                <td><?php echo $doc['experience']; ?> Years</td>
                                <td>
                                    <span class="badge bg-opacity-15 px-3 py-1.5 rounded-pill fs-9 <?php echo $doc['status'] === 'active' ? 'bg-success text-success' : 'bg-danger text-danger'; ?>">
                                        <?php echo ucfirst($doc['status']); ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <a href="doctor-edit.php?id=<?php echo $doc['id']; ?>" class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width:32px; height:32px;" title="Edit Doctor Profile">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        
                                        <a href="doctors.php?action=toggle_status&id=<?php echo $doc['id']; ?>" 
                                           class="btn <?php echo $doc['status'] === 'active' ? 'btn-outline-danger' : 'btn-outline-success'; ?> btn-sm rounded-circle d-flex align-items-center justify-content-center" 
                                           style="width:32px; height:32px;" 
                                           onclick="return confirm('Are you sure you want to change this doctor\'s status to <?php echo $doc['status'] === 'active' ? 'inactive' : 'active'; ?>?');" 
                                           title="<?php echo $doc['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>">
                                            <i class="fa-solid <?php echo $doc['status'] === 'active' ? 'fa-user-slash' : 'fa-user-check'; ?>"></i>
                                        </a>
                                    </div>
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
