<?php
// admin/patients.php
// View patients registry.

$page_title = "CareSync Admin - Patients Registry";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

check_role(['admin']);

$search = sanitize($_GET['search'] ?? '');
$error = '';

// Build patient records list
$query = "
    SELECT p.*, u.name, u.email, u.status 
    FROM patients p
    JOIN users u ON p.user_id = u.id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $query .= " AND (u.name LIKE ? OR u.email LIKE ? OR p.phone LIKE ?)";
    $term = "%{$search}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$query .= " ORDER BY u.name ASC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $patients = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Failed to load patients registry: " . $e->getMessage();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 font-outfit fw-bold text-dark-blue">Patient Registry</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <span class="badge bg-dark-blue text-white rounded-pill px-3 py-2 fs-8"><i class="fa-solid fa-users me-1"></i> Patient Records Directory</span>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<!-- Search Card -->
<div class="card border-0 shadow-sm rounded-3 bg-white p-3 mb-4">
    <div class="card-body">
        <form action="patients.php" method="GET" class="row g-3 align-items-end">
            <div class="col-md-9">
                <label for="search" class="form-label fs-8 text-muted fw-semibold">Search Patient Registry</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" class="form-control bg-light border-start-0" id="search" name="search" value="<?php echo sanitize($search); ?>" placeholder="Search by name, email or phone number...">
                </div>
            </div>
            <div class="col-md-3 d-grid gap-2 d-md-flex">
                <button type="submit" class="btn btn-dark-blue text-white fw-semibold w-100 py-2">Search</button>
                <a href="patients.php" class="btn btn-outline-secondary fw-semibold w-100 py-2">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Registry Table -->
<div class="card border-0 shadow-sm rounded-3 bg-white p-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0 text-nowrap">
                <thead class="table-light fs-9 text-uppercase">
                    <tr>
                        <th>Patient Name</th>
                        <th>Email Address</th>
                        <th>Contact Phone</th>
                        <th>Gender / DOB</th>
                        <th>Home Address</th>
                        <th>Registered On</th>
                    </tr>
                </thead>
                <tbody class="fs-8">
                    <?php if (empty($patients)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No registered patient records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($patients as $pat): 
                            $age = date_diff(date_create($pat['date_of_birth']), date_create('today'))->y;
                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; flex-shrink: 0;">
                                            <i class="fa-solid fa-user-injured fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark fs-7"><?php echo sanitize($pat['name']); ?></div>
                                            <div class="text-muted fs-9">ID: #PAT-0<?php echo $pat['id']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-dark"><?php echo sanitize($pat['email']); ?></td>
                                <td class="text-dark"><?php echo sanitize($pat['phone']); ?></td>
                                <td>
                                    <div><?php echo $pat['gender']; ?></div>
                                    <div class="text-muted fs-9"><?php echo format_date($pat['date_of_birth']); ?> (<?php echo $age; ?> yrs)</div>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="<?php echo sanitize($pat['address']); ?>">
                                        <?php echo sanitize($pat['address']); ?>
                                    </div>
                                </td>
                                <td class="text-muted"><?php echo format_date($pat['created_at']); ?></td>
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
