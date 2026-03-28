<?php 

$db = DBCore::getInstance();

$userId = $_SESSION['user']['id'];
$message = "";
$languageChoice = new CustomField("language", "SELECT code as _key_, name as _value_ FROM languages ORDER BY name", null);
// =========================
// DELETE
// =========================
if($_POST) {
    if(isset($_POST['delete'])) {
        $id = $_POST['id'];

        // delete votes first
        $db->delete('vote_learning', ' data_learning_id = ? ', [$id]);

        // delete learning
        $db->delete('data_learning', ' id = ? ', [$id]);

        $message = "Item deleted successfully !";
    }
}

// =========================
// FILTERS
// =========================
$search = $_POST['search'] ?? '';
$status = $_POST['status'] ?? '';
$date   = $_POST['date'] ?? '';
$language = $_POST['language'] ?? '';

$sql = "SELECT * FROM data_learning WHERE users_publisher_id = ? AND " . SearchOptions::formSearchListValueSQLQuery();
$params = [$userId];

// SEARCH
if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR description LIKE ?)";
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

// LANGUAGE
if (!empty($language)) {
    $sql .= " AND language = ?";
    $params[] = $language;
}

$sql .= " ORDER BY created_on DESC ";
Paging::setParameters($sql, $params, $db);
$sql .= Paging::getValuePagingSQL();

$learnings = $db->selectAll($sql, $params);

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

    <form method="POST" class="row g-2">
    <div class="card p-4 shadow mb-4">
        <h3>My Learning Content</h3>

        <!-- FILTER FORM -->
        
        <div class="row">
            <div class="col-md-4">
                <input type="text" name="search"
                    value="<?= htmlspecialchars($search) ?>"
                    class="form-control"
                    placeholder="Search title or description">
            </div>

            <div class="col-md-2">
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="1" <?= $status==='1'?'selected':'' ?>>Active</option>
                    <option value="0" <?= $status==='0'?'selected':'' ?>>Inactive</option>
                </select>
            </div>

            <div class="col-md-2">
                <input type="date" name="date"
                    value="<?= htmlspecialchars($date) ?>"
                    class="form-control">
            </div>

            <div class="col-md-2">
                <?= $languageChoice->displaySelect($_POST['language']??null, false, true); ?>
            </div>

            <div class="col-md-2">
                <button class="btn btn-primary w-100">Filter</button>
            </div>
</div>
    </div>
    <div class="card p-4 shadow mb-4 mySearchBox search-gray-box">
        <?= SearchOptions::formSearchListDisplay() ?>
    </div>
    </form>

    <!-- LIST -->
    <?php if(empty($learnings)): ?>
        <div class="alert alert-info">No learning content found</div>
    <?php endif; ?>

    <?php foreach($learnings as $learning): ?>
    <div class="card p-3 mb-3 shadow-sm">

        <h5><?= htmlspecialchars($learning['title']) ?></h5>

        <p><?= nl2br(htmlspecialchars($learning['description'])) ?></p>

        <?php if(!empty($learning['bible_references'])): ?>
            <p><strong>📖 <?= htmlspecialchars($learning['bible_references']) ?></strong></p>
        <?php endif; ?>

        <p class="searchoption">
            <i><?= SearchOptions::listSearchDisplay($learning['searchoptions'], true) ?></i>
        </p>
        <small class="text-muted">
            Created: <?= $learning['created_on'] ?> |
            Status: <?= $learning['active'] ? 'Active' : 'Inactive' ?> | 
            Language: <?= $learning['language'] ?> |
            In Production: <?= trim($learning['achived'])=="archived" ? 'Yes' : 'No' ?>
        </small>

        <div class="mt-2">

            <form action="" method="post">
                <?php if( $learning['achived'] != "ARCHIVED" ) { ?>
                    <!-- DELETE -->
                    <input type="hidden" name="id" value="<?= $learning['id'] ?>" />
                    <input type="submit" name="delete" value="Delete"
                        class="btn btn-danger btn-sm"
                        onclick="return confirm('Delete this content?')" />

                    <!-- EDIT -->
                    <a href="index.php?mnu=learning&op=edit&id=<?= $learning['id'] ?>"
                    class="btn btn-warning btn-sm">
                    Edit
                    </a>
                <?php } ?>
                <!-- VIEW -->
                <a href="index.php?mnu=learning&op=view&id=<?= $learning['id'] ?>"
                   class="btn btn-info btn-sm">
                   View
                </a>
                <?= $hiddenFields ?>
            </form>

        </div>

    </div>
    <?php endforeach; ?>
    <?= Paging::displayPaging() ?>
</div>