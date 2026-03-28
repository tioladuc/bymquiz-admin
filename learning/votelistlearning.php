<?php 
$db = DBCore::getInstance();
$userId = $_SESSION['user']['id'];
$languageChoice = new CustomField("language", "SELECT code as _key_, name as _value_ FROM languages ORDER BY name", null);

if($_POST && isset($_POST['votesubmit'])) {

    // =========================
    // VALIDATE INPUT
    // =========================
    $learningId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $vote       = isset($_POST['vote']) ? (int)$_POST['vote'] : -1;

    if ($learningId <= 0 || $vote < 0 || $vote > 10) {
        die("Invalid data");
    }

    // =========================
    // CHECK LEARNING ITEM EXISTS
    // =========================
    $learning = $db->selectOne(
        "SELECT id, users_publisher_id FROM data_learning WHERE id = ? AND active = 1",
        [$learningId]
    );

    if (!$learning) {
        die("Learning content not found");
    }

    // =========================
    // PREVENT SELF-VOTE
    // =========================
    if ($learning['users_publisher_id'] == $userId) {
        die("You cannot vote your own content");
    }

    // =========================
    // CHECK EXISTING VOTE
    // =========================
    $existingVote = $db->selectOne(
        "SELECT id FROM vote_learning WHERE data_learning_id = ? AND users_publisher_id = ?",
        [$learningId, $userId]
    );

    // =========================
    // INSERT OR UPDATE
    // =========================
    if ($existingVote) {
        // UPDATE
        $db->update(
            "vote_learning",
            [
                "vote_from_0_to_10" => $vote,
                "createdOn" => date('Y-m-d H:i:s')
            ],
            "id = :id",
            [":id" => $existingVote['id']]
        );
        $msg = "Vote updated successfully!";
    } else {
        // INSERT
        $db->insert("vote_learning", [
            "data_learning_id" => $learningId,
            "users_publisher_id" => $userId,
            "vote_from_0_to_10" => $vote,
            "createdOn" => date('Y-m-d H:i:s')
        ]);
        $msg = "Vote submitted successfully!";
    }
}

// =========================
// FILTERS
// =========================
$search   = $_POST['search'] ?? '';
$status   = $_POST['status'] ?? '1';
$date     = $_POST['date'] ?? '';
$language = $_POST['language'] ?? '';
$language = $_POST['language'] ?? '';

$sql = "
SELECT l.*, 
       IFNULL(SUM(v.vote_from_0_to_10),0) as total_votes,
       COUNT(v.id) as voters,
       uv.vote_from_0_to_10 as my_vote
FROM data_learning l
LEFT JOIN vote_learning v ON l.id = v.data_learning_id
LEFT JOIN vote_learning uv 
    ON l.id = uv.data_learning_id 
    AND uv.users_publisher_id = ?
WHERE l.users_publisher_id != ?
AND " . SearchOptions::formSearchListValueSQLQuery();

$params = [$userId, $userId];

// SEARCH
if (!empty($search)) {
    $sql .= " AND (l.title LIKE ? OR l.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// STATUS
if ($status !== '') {
    $sql .= " AND l.active = ?";
    $params[] = $status;
}

// DATE
if (!empty($date)) {
    $sql .= " AND DATE(l.created_on) = ?";
    $params[] = $date;
}

// LANGUAGE
if (!empty($language)) {
    $sql .= " AND l.language = ?";
    $params[] = $language;
}

$sql .= " GROUP BY l.id ORDER BY l.created_on DESC";
Paging::setParameters($sql, $params, $db);
$sql .= Paging::getValuePagingSQL();

$learnings = $db->selectAll($sql, $params);

$hiddenFields = "";
if($_POST) {
    foreach ($_POST as $key => $value) {
        if($key!="id" && $key!="votesubmit" && $key != "vote") {
            $hiddenFields .= "<input name='$key' type='hidden' value='$value' />";
        }
    }
}

?>

<div class="container">

    <form method="POST" class="row g-2">
    <div class="card p-4 shadow mb-4">
        <h3>Vote Learning Items</h3>

        <!-- FILTER FORM -->
        
        <div class="row">
            <div class="col-md-3">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control"
                    placeholder="Search...">
            </div>

            <div class="col-md-2">
                <select name="status" class="form-control">
                    <option value="">All</option>
                    <option value="1" <?= $status==='1'?'selected':'' ?>>Active</option>
                    <option value="0" <?= $status==='0'?'selected':'' ?>>Inactive</option>
                </select>
            </div>

            <div class="col-md-2">
                <input type="date" name="date" value="<?= htmlspecialchars($date) ?>" class="form-control">
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
    <div class="alert alert-info">No Learning items found</div>
    <?php endif; ?>

    <?php foreach($learnings as $item): ?>

    <div class="card p-3 mb-3 shadow-sm">

        <h5><a href="index.php?mnu=learning&op=view&id=<?= $item['id'] ?>"><?= htmlspecialchars($item['title']) ?></a></h5>

        <p><?= nl2br(htmlspecialchars($item['description'])) ?></p>
        <p class="searchoption">
            <i><?= SearchOptions::listSearchDisplay($item['searchoptions'], true) ?></i>
        </p>
        <small class="text-muted">
            <?= $item['created_on'] ?> |
            <?= $item['language'] ?> |
            <?= $item['active'] ? 'Active' : 'Inactive' ?> | 
            Language: <?= $item['language'] ?> |
            In Production: <?= trim($item['achived'])=="archived" ? 'Yes' : 'No' ?>
        </small>

        <!-- VOTES -->
        <p class="mt-2">
            ⭐ Score: <?= $item['total_votes'] ?>
            (<?= $item['voters'] ?> voters)
        </p>

        <!-- USER PREVIOUS VOTE -->
        <?php if($item['my_vote'] !== null): ?>
        <div class="alert alert-info p-2">
            Your vote: <strong><?= $item['my_vote'] ?>/10</strong>
        </div>
        <?php endif; ?>

        <!-- VOTE FORM -->
        <?php if($item['achived'] != "ARCHIVED") { ?>
        <form method="post" action="index.php?mnu=learning&op=vote" class="d-flex gap-2">

            <input type="hidden" name="id" value="<?= $item['id'] ?>">
            <input type="hidden" name="votesubmit" value="vote">

            <input type="number" name="vote" min="0" max="10"
                value="<?= $item['my_vote'] !== null ? $item['my_vote'] : '' ?>" class="form-control" required>

            <button class="btn btn-success">
                <?= $item['my_vote'] !== null ? 'Update Vote' : 'Vote' ?>
            </button>
            <?= $hiddenFields ?>
        </form>
        <?php } ?>

    </div>

    <?php endforeach; ?>
    <?= Paging::displayPaging() ?>

</div>