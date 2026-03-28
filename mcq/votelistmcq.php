<?php 
$db = DBCore::getInstance();
$userId = $_SESSION['user']['id'];
$languageChoice = new CustomField("language", "SELECT code as _key_, name as _value_ FROM languages ORDER BY name", null);

if($_POST && isset($_POST['votesubmit'])) {
        $userId = $_SESSION['user']['id'];

    // =========================
    // VALIDATE INPUT
    // =========================
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        die("Invalid request");
    }

    $mcqId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $vote   = isset($_POST['vote']) ? (int)$_POST['vote'] : -1;

    if ($mcqId <= 0 || $vote < 0 || $vote > 10) {
        die("Invalid data");
    }

    // =========================
    // CHECK MCQ EXISTS
    // =========================
    $mcq = $db->selectOne(
        "SELECT id, users_publisher_id FROM data_mcq WHERE id = ? AND active = 1",
        [$mcqId]
    );

    if (!$mcq) {
        die("MCQ not found");
    }

    // =========================
    // PREVENT SELF-VOTE
    // =========================
    if ($mcq['users_publisher_id'] == $userId) {
        die("You cannot vote your own MCQ");
    }

    // =========================
    // CHECK EXISTING VOTE
    // =========================
    $existingVote = $db->selectOne(
        "SELECT id FROM vote_mcq WHERE data_mcq_id = ? AND users_publisher_id = ?",
        [$mcqId, $userId]
    );

    // =========================
    // INSERT OR UPDATE
    // =========================
    if ($existingVote) {

        // UPDATE
        $db->update(
            "vote_mcq",
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
        $db->insert("vote_mcq", [
            "data_mcq_id" => $mcqId,
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

$sql = "
SELECT m.*, 
       IFNULL(SUM(v.vote_from_0_to_10),0) as total_votes,
       COUNT(v.id) as voters,
       uv.vote_from_0_to_10 as my_vote
FROM data_mcq m
LEFT JOIN vote_mcq v ON m.id = v.data_mcq_id
LEFT JOIN vote_mcq uv 
    ON m.id = uv.data_mcq_id 
    AND uv.users_publisher_id = ?
WHERE m.users_publisher_id != ?
AND " . SearchOptions::formSearchListValueSQLQuery();

$params = [$userId, $userId];

// SEARCH
if (!empty($search)) {
    $sql .= " AND (m.title LIKE ? OR m.question LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// STATUS
if ($status !== '') {
    $sql .= " AND m.active = ?";
    $params[] = $status;
}

// DATE
if (!empty($date)) {
    $sql .= " AND DATE(m.created_on) = ?";
    $params[] = $date;
}

// LANGUAGE
if (!empty($language)) {
    $sql .= " AND m.language = ?";
    $params[] = $language;
}

$sql .= " GROUP BY m.id ORDER BY m.created_on DESC";
Paging::setParameters($sql, $params, $db);
$sql .= Paging::getValuePagingSQL();

$mcqs = $db->selectAll($sql, $params);
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
        <h3>Vote MCQs</h3>

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
    <?php if(empty($mcqs)): ?>
    <div class="alert alert-info">No MCQs found</div>
    <?php endif; ?>

    <?php foreach($mcqs as $mcq): ?>

    <div class="card p-3 mb-3 shadow-sm">

        <h5><a href="index.php?mnu=mcq&op=view&id=<?= $mcq['id'] ?>"><?= htmlspecialchars($mcq['title']) ?></a></h5>

        <p><?= nl2br(htmlspecialchars($mcq['question'])) ?></p>

        <p class="searchoption">
            <i><?= SearchOptions::listSearchDisplay($mcq['searchoptions'], true) ?></i>
        </p>
        <small class="text-muted">
            <?= $mcq['created_on'] ?> |
            <?= $mcq['active'] ? 'Active' : 'Inactive' ?> | 
            Language: <?= $mcq['language'] ?> |
            In Production: <?= trim($mcq['achived'])=="archived" ? 'Yes' : 'No' ?>
        </small>

        <!-- VOTES -->
        <p class="mt-2">
            ⭐ Score: <?= $mcq['total_votes'] ?>
            (<?= $mcq['voters'] ?> voters)
        </p>

        <!-- USER PREVIOUS VOTE -->
        <?php if($mcq['my_vote'] !== null): ?>
        <div class="alert alert-info p-2">
            Your vote: <strong><?= $mcq['my_vote'] ?>/10</strong>
        </div>
        <?php endif; ?>

        <!-- VOTE FORM -->
        <?php if($mcq['achived'] != "ARCHIVED") { ?>
        <form method="post" action="index.php?mnu=mcq&op=vote" class="d-flex gap-2">

            <input type="hidden" name="id" value="<?= $mcq['id'] ?>">
            <input type="hidden" name="votesubmit" value="vote">

            <input type="number" name="vote" min="0" max="10"
                value="<?= $mcq['my_vote'] !== null ? $mcq['my_vote'] : '' ?>" class="form-control" required>

            <button class="btn btn-success">
                <?= $mcq['my_vote'] !== null ? 'Update Vote' : 'Vote' ?>
            </button>

        </form>
        <?php } ?>
        <?= $hiddenFields ?>
    </div>

    <?php endforeach; ?>
    <?= Paging::displayPaging() ?>

</div>