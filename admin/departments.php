<?php
// admin/departments.php
// Departments Management (CRUD in a single page).

$page_title = "CareSync Admin - Clinical Departments";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

check_role(['admin']);

$error = '';
$success = '';

// Session messages
if (isset($_SESSION['success_msg'])) {
    $success = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}
if (isset($_SESSION['error_msg'])) {
    $error = $_SESSION['error_msg'];
    unset($_SESSION['error_msg']);
}

// Processing Add / Edit Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = "CSRF verification failed.";
    } else {
        $action = $_POST['action'] ?? '';
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        $status = sanitize($_POST['status'] ?? 'active');
        
        if (empty($name) || empty($description)) {
            $error = "All fields are required.";
        } else {
            if ($action === 'add') {
                try {
                    // Check duplicate name
                    $chk = $pdo->prepare("SELECT id FROM departments WHERE name = ?");
                    $chk->execute([$name]);
                    if ($chk->fetch()) {
                        $error = "A department with the name '{$name}' already exists.";
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO departments (name, description, status) VALUES (?, ?, ?)");
                        $stmt->execute([$name, $description, $status]);
                        $success = "Department '{$name}' created successfully.";
                    }
                } catch (PDOException $e) {
                    $error = "Failed to create department: " . $e->getMessage();
                }
            } elseif ($action === 'edit') {
                $id = (int)$_POST['id'];
                try {
                    // Check duplicate name for other department
                    $chk = $pdo->prepare("SELECT id FROM departments WHERE name = ? AND id != ?");
                    $chk->execute([$name, $id]);
                    if ($chk->fetch()) {
                        $error = "Another department with name '{$name}' already exists.";
                    } else {
                        $stmt = $pdo->prepare("UPDATE departments SET name = ?, description = ?, status = ? WHERE id = ?");
                        $stmt->execute([$name, $description, $status, $id]);
                        $success = "Department details updated successfully.";
                    }
                } catch (PDOException $e) {
                    $error = "Failed to update department: " . $e->getMessage();
                }
            }
        }
    }
}

// Handle Status Toggling via GET
if (isset($_GET['action']) && $_GET['action'] === 'toggle_status' && isset($_GET['id'])) {
    $deptId = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT name, status FROM departments WHERE id = ?");
        $stmt->execute([$deptId]);
        $dept = $stmt->fetch();
        if ($dept) {
            $newStatus = ($dept['status'] === 'active') ? 'inactive' : 'active';
            $upd = $pdo->prepare("UPDATE departments SET status = ? WHERE id = ?");
            $upd->execute([$newStatus, $deptId]);
            $_SESSION['success_msg'] = "Status of department '{$dept['name']}' set to " . $newStatus . ".";
            header("Location: departments.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Failed to toggle status: " . $e->getMessage();
        header("Location: departments.php");
        exit();
    }
}

// Check if we are editing a department
$editDept = null;
if (isset($_GET['edit_id'])) {
    $editId = (int)$_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM departments WHERE id = ?");
    $stmt->execute([$editId]);
    $editDept = $stmt->fetch();
}

// Fetch all departments
$departments = [];
try {
    $stmt = $pdo->query("SELECT * FROM departments ORDER BY name ASC");
    $departments = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Failed to load clinical directory: " . $e->getMessage();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 font-outfit fw-bold text-dark-blue">Clinic Departments</h1>
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
    <!-- Department Form Column -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <div class="card-body">
                <h5 class="card-title font-outfit fw-bold text-dark-blue mb-4 border-bottom pb-2">
                    <?php echo $editDept ? '<i class="fa-solid fa-pen-to-square text-medical-blue me-2"></i>Edit Specialty' : '<i class="fa-solid fa-plus text-medical-blue me-2"></i>New Specialty'; ?>
                </h5>
                
                <form action="departments.php" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="<?php echo $editDept ? 'edit' : 'add'; ?>">
                    <?php if ($editDept): ?>
                        <input type="hidden" name="id" value="<?php echo $editDept['id']; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="name" class="form-label fs-8 text-muted fw-semibold">Department Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo sanitize($editDept['name'] ?? ''); ?>" placeholder="e.g. Pediatrics" required>
                        <div class="invalid-feedback">Please enter the department name.</div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fs-8 text-muted fw-semibold">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5" placeholder="Details regarding department focus..." required><?php echo sanitize($editDept['description'] ?? ''); ?></textarea>
                        <div class="invalid-feedback">Please enter a description.</div>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label fs-8 text-muted fw-semibold">Department Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="active" <?php echo ($editDept['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($editDept['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-medical-blue text-white fw-semibold py-2">
                            <?php echo $editDept ? 'Save Changes' : 'Create Department'; ?>
                        </button>
                        <?php if ($editDept): ?>
                            <a href="departments.php" class="btn btn-outline-secondary py-2">Cancel Edit</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Departments Directory Column -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <div class="card-body">
                <h5 class="card-title font-outfit fw-bold text-dark-blue mb-4 border-bottom pb-2">Active Specialists Catalog</h5>
                <div class="table-responsive">
                    <table class="table align-middle table-hover mb-0 text-nowrap">
                        <thead class="table-light fs-9 text-uppercase">
                            <tr>
                                <th style="width: 25%">Name</th>
                                <th style="width: 50%">Description</th>
                                <th style="width: 15%">Status</th>
                                <th style="width: 10%" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="fs-8">
                            <?php if (empty($departments)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No departments created.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($departments as $dept): ?>
                                    <tr class="<?php echo $editDept && $editDept['id'] == $dept['id'] ? 'table-warning' : ''; ?>">
                                        <td class="fw-semibold text-dark"><?php echo sanitize($dept['name']); ?></td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 320px;" title="<?php echo sanitize($dept['description']); ?>">
                                                <?php echo sanitize($dept['description']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-opacity-15 px-3 py-1.5 rounded-pill fs-9 <?php echo $dept['status'] === 'active' ? 'bg-success text-success' : 'bg-danger text-danger'; ?>">
                                                <?php echo ucfirst($dept['status']); ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-2">
                                                <a href="departments.php?edit_id=<?php echo $dept['id']; ?>" class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width:32px; height:32px;" title="Edit Department details">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <a href="departments.php?action=toggle_status&id=<?php echo $dept['id']; ?>" 
                                                   class="btn <?php echo $dept['status'] === 'active' ? 'btn-outline-danger' : 'btn-outline-success'; ?> btn-sm rounded-circle d-flex align-items-center justify-content-center" 
                                                   style="width:32px; height:32px;" 
                                                   onclick="return confirm('Toggle status of department <?php echo sanitize($dept['name']); ?>?');" 
                                                   title="<?php echo $dept['status'] === 'active' ? 'Disable' : 'Enable'; ?>">
                                                    <i class="fa-solid <?php echo $dept['status'] === 'active' ? 'fa-ban' : 'fa-check'; ?>"></i>
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
    </div>
</div>

<?php 
require_once __DIR__ . '/../includes/footer.php';
?>
