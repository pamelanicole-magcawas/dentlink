<?php
// patient_records.php
session_start();

// ---------- DB CONFIG ----------
include 'db_connect.php'; // uses $conn

// ---------- UTILS ----------
function esc($v)
{
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

$uploads_dir = __DIR__ . '/upload';
$uploads_web = 'upload';

if (!is_dir($uploads_dir)) {
    mkdir($uploads_dir, 0755, true);
}

// ---------- Handle POST ACTIONS ----------
$action = $_POST['action'] ?? null;

if ($action === 'add_patient') {

    $first = $_POST['first_name'];
    $last  = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $password = password_hash($_POST['password'] ?: bin2hex(random_bytes(4)), PASSWORD_DEFAULT);

    $profile_pic_name = null;

    $stmt = $conn->prepare("
        INSERT INTO users (first_name, last_name, email, phone, address, role, profile_pic, password)
        VALUES (?, ?, ?, ?, ?, 'Patient', ?, ?)
    ");
    $stmt->bind_param("sssssss", $first, $last, $email, $phone, $address, $profile_pic_name, $password);
    $stmt->execute();
    $stmt->close();

    $_SESSION['flash'] = "Patient added.";
    header("Location: patient_records.php");
    exit;
}

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

    $_SESSION['flash'] = "Patient deleted.";
    header("Location: patient_records.php");
    exit;
}

// ---------- COUNT PATIENTS ----------
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM users WHERE role='Patient'");
$stmt->execute();
$totalPatients = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// ---------- Search / Filter ----------
$search_q  = trim($_GET['q'] ?? '');
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
        $types .= 's';
        $params[] = "%$search_q%";
    } elseif ($filter_by === "email") {
        $where .= " AND email LIKE ?";
        $types .= 's';
        $params[] = "%$search_q%";
    } elseif ($filter_by === "phone") {
        $where .= " AND phone LIKE ?";
        $types .= 's';
        $params[] = "%$search_q%";
    } elseif ($filter_by === "address") {
        $where .= " AND address LIKE ?";
        $types .= 's';
        $params[] = "%$search_q%";
    } else {
        $where .= " AND (
            CONCAT(first_name,' ',last_name) LIKE ?
            OR email LIKE ?
            OR phone LIKE ?
            OR address LIKE ?
        )";
        $types .= 'ssss';
        $params = ["%$search_q%", "%$search_q%", "%$search_q%", "%$search_q%"];
    }
}

// Count total filtered
$countSql = "SELECT COUNT(*) AS cnt FROM users $where";
$countStmt = $conn->prepare($countSql);
if ($types) $countStmt->bind_param($types, ...$params);

$countStmt->execute();
$totalMatched = $countStmt->get_result()->fetch_assoc()['cnt'];
$countStmt->close();

$totalPages = max(1, ceil($totalMatched / $per_page));

// Fetch patient list
$fetchSql = "SELECT * FROM users $where ORDER BY created_at DESC LIMIT ? OFFSET ?";
$fetchStmt = $conn->prepare($fetchSql);

if ($types) {
    $bindTypes = $types . "ii";
    $fullParams = array_merge($params, [$per_page, $offset]);

    $a = [];
    $a[] = &$bindTypes;
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
    <title>Patient Records</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: var(--bg, #f8f9fa);
            color: var(--text, #212529);
        }

        .dark {
            --bg: #0b0f14;
            --card: #0f1720;
            --text: #e6eef8;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .patient-img {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
        }

        .card {
            border-radius: 15px;
            padding: 35px !important;
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="admin_dashboard.php" class="btn btn-outline-primary mb-3">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
        <div class="container py-4">

            <div class="d-flex justify-content-between mb-4">
                <div>
                    <h3>Patient Records</h3>
                    <small>Total Patients: <?= $totalPatients ?></small>
                </div>


            </div>

            <?php if ($flash): ?>
                <div class="alert alert-success"><?= $flash ?></div>
            <?php endif; ?>

            <!-- SEARCH BAR -->
            <form class="row g-2 mb-4" method="GET">
                <div class="col-md-8">
                    <input name="q" value="<?= esc($search_q) ?>" class="form-control" placeholder="Search patients...">
                </div>
                <div class="col-md-2">
                    <select name="filter_by" class="form-select">
                        <option value="all">All</option>
                        <option value="name" <?= $filter_by === 'name' ? 'selected' : '' ?>>Name</option>
                        <option value="email" <?= $filter_by === 'email' ? 'selected' : '' ?>>Email</option>
                        <option value="phone" <?= $filter_by === 'phone' ? 'selected' : '' ?>>Phone</option>
                        <option value="address" <?= $filter_by === 'address' ? 'selected' : '' ?>>Address</option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-primary">Search</button>
                </div>
            </form>

            <!-- ADD PATIENT BUTTON -->
            <button class="btn btn-outline-primary mb-4" data-bs-toggle="modal" data-bs-target="#modalAdd">+ Add Patient</button>

            <!-- PATIENT CARD GRID -->
            <div class="card-grid mb-4">

                <?php if ($result->num_rows == 0): ?>
                    <div class="alert alert-warning">No patient found.</div>
                <?php endif; ?>

                <?php while ($row = $result->fetch_assoc()):
                    $pic = $row['profile_pic']
                        ? "$uploads_web/" . urlencode($row['profile_pic'])
                        : "default-avatar.png";
                ?>
                    <div class="card text-center shadow-sm">

                        <img src="<?= esc($pic) ?>" class="patient-img mb-3">

                        <h5 class="fw-bold mb-4">
                            <?= esc($row['first_name'] . " " . $row['last_name']) ?>
                        </h5>

                        <div class="d-flex justify-content-center gap-2">
                            <a href="patient_details.php?id=<?= $row['user_id'] ?>" class="btn btn-outline-primary btn-sm px-4">View</a>

                            <button class="btn btn-danger btn-sm px-4 btn-delete"
                                data-id="<?= $row['user_id'] ?>">
                                Delete
                            </button>
                        </div>

                    </div>
                <?php endwhile; ?>

            </div>

            <!-- PAGINATION -->
            <nav>
                <ul class="pagination">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Prev</a>
                    </li>

                    <li class="page-item disabled">
                        <span class="page-link">Page <?= $page ?> / <?= $totalPages ?></span>
                    </li>

                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next</a>
                    </li>
                </ul>
            </nav>

        </div>


        <!-- ADD PATIENT MODAL -->
        <div class="modal fade" id="modalAdd">
            <div class="modal-dialog modal-lg">
                <form method="POST" class="modal-content">
                    <input type="hidden" name="action" value="add_patient">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Patient</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col-md-6"><input required name="first_name" class="form-control" placeholder="First Name"></div>
                            <div class="col-md-6"><input required name="last_name" class="form-control" placeholder="Last Name"></div>
                            <div class="col-md-6"><input required type="email" name="email" class="form-control" placeholder="Email"></div>
                            <div class="col-md-6"><input name="phone" class="form-control" placeholder="Phone"></div>
                            <div class="col-12"><input name="address" class="form-control" placeholder="Address"></div>
                            <div class="col-12"><input name="password" type="password" class="form-control" placeholder="Password (optional)"></div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary">Add Patient</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- DELETE CONFIRM MODAL -->
        <div class="modal fade" id="modalDelete">
            <div class="modal-dialog">
                <form method="POST" class="modal-content">
                    <input type="hidden" name="action" value="delete_patient">
                    <input type="hidden" name="user_id" id="delete_user_id">

                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <p>Are you sure you want to delete this patient?</p>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-danger">Delete</button>
                    </div>

                </form>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <script>
            // DARK MODE
            const toggle = document.getElementById('darkToggle');

            function applyMode() {
                const mode = localStorage.getItem('mode') || 'light';
                document.documentElement.classList.toggle('dark', mode === 'dark');
                toggle.checked = (mode === 'dark');
            }
            toggle.addEventListener('change', () => {
                localStorage.setItem('mode', toggle.checked ? 'dark' : 'light');
                applyMode();
            });
            applyMode();

            // DELETE BUTTON HANDLER
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.getElementById('delete_user_id').value = btn.dataset.id;
                    new bootstrap.Modal(document.getElementById('modalDelete')).show();
                });
            });
        </script>

</body>

</html>