<?php 


$db = DBCore::getInstance();

$userId = $_SESSION['user']['id'];
$message = "";

if($_POST) {
    if(isset($_POST['delete'])) {
        $id = $_POST['id'];
        $db->delete('vote_mcq', ' data_mcq_id= ? ',[$id]);
        $db->delete('data_mcq', ' id= ? ',[$id]);
        $message = "Item deleted successfully !";
    }
}
// =========================
// FILTERS
// =========================
$search = $_POST['search'] ?? '';
$status = $_POST['status'] ?? '';
$date = $_POST['date'] ?? '';

$sql = "SELECT * FROM data_mcq WHERE users_publisher_id = ?";
$params = [$userId];

// SEARCH
if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR question LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// STATUS FILTER
if ($status !== '') {
    $sql .= " AND active = ?";
    $params[] = $status;
}

// DATE FILTER
if (!empty($date)) {
    $sql .= " AND DATE(created_on) = ?";
    $params[] = $date;
}

$sql .= " ORDER BY created_on DESC";

$mcqs = $db->selectAll($sql, $params);

$hiddenFields = "";
if($_POST) {
    foreach ($_POST as $key => $value) {
        if($key!="id" && $key!="delete") {
            $hiddenFields .= "<input name='$key' type='hidden' value='$value' />";
        }
    }
}
?>

<div class="container">
    <div>
        <center>
            <font style="color:red;"><?= $message ?></font>
        </center>
    </div>
    <div class="card p-4 shadow mb-4">
        <h3>My MCQs</h3>

        <!-- FILTER FORM -->
        <form method="POST" class="row g-2">

            <div class="col-md-4">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control"
                    placeholder="Search title or question">
            </div>

            <div class="col-md-3">
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="1" <?= $status==='1'?'selected':'' ?>>Active</option>
                    <option value="0" <?= $status==='0'?'selected':'' ?>>Inactive</option>
                </select>
            </div>

            <div class="col-md-3">
                <input type="date" name="date" value="<?= htmlspecialchars($date) ?>" class="form-control">
            </div>

            <div class="col-md-2">
                <button class="btn btn-primary w-100">Filter</button>
            </div>

        </form>
    </div>

    <!-- LIST -->
    <?php if(empty($mcqs)): ?>
    <div class="alert alert-info">No MCQs found</div>
    <?php endif; ?>

    <?php foreach($mcqs as $mcq): ?>
    <div class="card p-3 mb-3 shadow-sm">

        <h5><?= htmlspecialchars($mcq['title']) ?></h5>

        <p><?= nl2br(htmlspecialchars($mcq['question'])) ?></p>
        <p class="searchoption">
            <i><?= SearchOptions::listSearchDisplay($mcq['searchoptions'], true) ?></i>
        </p>
        <small class="text-muted">
            Created: <?= $mcq['created_on'] ?> |
            Status: <?= $mcq['active'] ? 'Active' : 'Inactive' ?> | 
            Language: <?= $mcq['language'] ?> |
            In Production: <?= trim($mcq['achived'])=="archived" ? 'Yes' : 'No' ?>
        </small>

        <div class="mt-2">


            <form action="" method="post">
                <?php if( $mcq['achived'] != "ARCHIVED" ) { ?>
                    <!-- DELETE -->
                    <input type='hidden' name='id' value="<?= $mcq['id'] ?>" />
                    <input type='submit' name='delete' value='Delete' class="btn btn-danger btn-sm"
                        onclick="return confirm('Delete this MCQ?')" />

                    <!-- OPTIONAL EDIT -->
                    <a href="index.php?mnu=mcq&op=edit&id=<?= $mcq['id'] ?>" class="btn btn-warning btn-sm">
                        Edit
                    </a>
                <?php } ?>

                <!-- OPTIONAL VIEW -->
                <a href="index.php?mnu=mcq&op=view&id=<?= $mcq['id'] ?>" class="btn btn-warning btn-sm">
                    View
                </a>
                <?= $hiddenFields ?>
            </form>




        </div>

    </div>
    <?php endforeach; ?>

</div>