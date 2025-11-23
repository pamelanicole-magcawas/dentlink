<?php
session_start();
include 'db_connect.php';

function esc($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

$uploads_dir = __DIR__ . '/upload';
$uploads_web = 'upload';
if (!is_dir($uploads_dir)) mkdir($uploads_dir, 0755, true);

$action = $_POST['action'] ?? null;

if ($action === 'delete_patient') {
    $id = intval($_POST['user_id']);
    $q = $conn->prepare("SELECT profile_pic FROM users WHERE user_id=? LIMIT 1");
    $q->bind_param("i", $id);
    $q->execute();
    $res = $q->get_result()->fetch_assoc();
    $pic = $res['profile_pic'] ?? null;
    if ($pic && file_exists("$uploads_dir/$pic")) unlink("$uploads_dir/$pic");
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id=? AND role='Patient'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $_SESSION['flash'] = "Patient deleted successfully.";
    header("Location: patient_records.php");
    exit;
}

$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM users WHERE role='Patient'");
$stmt->execute();
$totalPatients = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$search_q = trim($_GET['q'] ?? '');
$filter_by = $_GET['filter_by'] ?? 'all';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 9;
$offset = ($page - 1) * $per_page;

$where = "WHERE role='Patient'";
$params = [];
$types = '';

if ($search_q !== '') {
    if ($filter_by === "name") {
        $where .= " AND CONCAT(first_name,' ',last_name) LIKE ?";
        $types .= 's'; $params[] = "%$search_q%";
    } elseif ($filter_by === "email") {
        $where .= " AND email LIKE ?";
        $types .= 's'; $params[] = "%$search_q%";
    } elseif ($filter_by === "phone") {
        $where .= " AND phone LIKE ?";
        $types .= 's'; $params[] = "%$search_q%";
    } elseif ($filter_by === "address") {
        $where .= " AND address LIKE ?";
        $types .= 's'; $params[] = "%$search_q%";
    } else {
        $where .= " AND (CONCAT(first_name,' ',last_name) LIKE ? OR email LIKE ? OR phone LIKE ? OR address LIKE ?)";
        $types .= 'ssss';
        $params = ["%$search_q%", "%$search_q%", "%$search_q%", "%$search_q%"];
    }
}

$countSql = "SELECT COUNT(*) AS cnt FROM users $where";
$countStmt = $conn->prepare($countSql);
if ($types) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$totalMatched = $countStmt->get_result()->fetch_assoc()['cnt'];
$countStmt->close();
$totalPages = max(1, ceil($totalMatched / $per_page));

$fetchSql = "SELECT * FROM users $where ORDER BY created_at DESC LIMIT ? OFFSET ?";
$fetchStmt = $conn->prepare($fetchSql);
if ($types) {
    $bindTypes = $types . "ii";
    $fullParams = array_merge($params, [$per_page, $offset]);
    $a = []; $a[] = &$bindTypes;
    foreach ($fullParams as $k => $v) $a[] = &$fullParams[$k];
    call_user_func_array([$fetchStmt, "bind_param"], $a);
} else {
    $fetchStmt->bind_param("ii", $per_page, $offset);
}
$fetchStmt->execute();
$result = $fetchStmt->get_result();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Patient Records - DentLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-page">

    <a href="admin_dashboard.php" class="btn-back-dashboard">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>

    <div class="admin-page-header">
        <h2><i class="bi bi-people-fill"></i> Patient Records</h2>
        <p>Total Patients: <strong><?= $totalPatients ?></strong></p>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?= esc($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Search Form -->
    <form class="admin-search-form" method="GET">
        <div class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label text-muted small">Search patients</label>
                <input name="q" value="<?= esc($search_q) ?>" class="form-control" placeholder="Enter name, email, phone...">
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small">Filter by</label>
                <select name="filter_by" class="form-select">
                    <option value="all">All Fields</option>
                    <option value="name" <?= $filter_by === 'name' ? 'selected' : '' ?>>Name</option>
                    <option value="email" <?= $filter_by === 'email' ? 'selected' : '' ?>>Email</option>
                    <option value="phone" <?= $filter_by === 'phone' ? 'selected' : '' ?>>Phone</option>
                    <option value="address" <?= $filter_by === 'address' ? 'selected' : '' ?>>Address</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-search w-100"><i class="bi bi-search me-2"></i>Search</button>
            </div>
        </div>
    </form>

    <!-- Patient Cards -->
    <div class="patient-card-grid">
        <?php if ($result->num_rows == 0): ?>
            <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>No patients found.</div>
        <?php endif; ?>

        <?php while ($row = $result->fetch_assoc()):
            $pic = $row['profile_pic'] ? "$uploads_web/" . urlencode($row['profile_pic']) : "default-avatar.png";
        ?>
        <div class="patient-card">
            <img src="<?= esc($pic) ?>" alt="Profile" class="patient-img">
            <h5><?= esc($row['first_name'] . " " . $row['last_name']) ?></h5>
            <div class="btn-group">
                <a href="patient_details.php?id=<?= $row['user_id'] ?>" class="btn-view">
                    <i class="bi bi-eye"></i> View
                </a>
                <button class="btn-delete" data-id="<?= $row['user_id'] ?>">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <!-- Pagination -->
    <nav>
        <ul class="pagination admin-pagination">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                    <i class="bi bi-chevron-left"></i> Prev
                </a>
            </li>
            <li class="page-item disabled">
                <span class="page-link">Page <?= $page ?> of <?= $totalPages ?></span>
            </li>
            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                    Next <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Delete Modal -->
    <div class="modal fade" id="modalDelete">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" class="modal-content">
                <input type="hidden" name="action" value="delete_patient">
                <input type="hidden" name="user_id" id="delete_user_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this patient? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-action btn-danger">
                        <i class="bi bi-trash"></i> Delete Patient
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('delete_user_id').value = btn.dataset.id;
                new bootstrap.Modal(document.getElementById('modalDelete')).show();
            });
        });
    </script>
</body>
</html>