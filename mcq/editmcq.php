<?php 
$db = DBCore::getInstance();

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$id = (int) $_GET['id'];
$userId = $_SESSION['user']['id'];
$languageChoice = new CustomField("language", "SELECT code as _key_, name as _value_ FROM languages ORDER BY name", null);

// =========================
// FETCH MCQ (CHECK OWNER)
// =========================
$mcq = $db->selectOne(
    "SELECT * FROM data_mcq WHERE id = ? AND users_publisher_id = ?",
    [$id, $userId]
);

if (!$mcq || ($mcq['achived'] == "ARCHIVED")) {
    die("Access denied or MCQ not found");
}

$message = "";
$error = "";

// =========================
// UPDATE
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title']);
    $question = trim($_POST['question']);
    $A = trim($_POST['choiceA']);
    $B = trim($_POST['choiceB']);
    $C = trim($_POST['choiceC']);
    $D = trim($_POST['choiceD']);
    $answer = strtoupper(trim($_POST['answer']));
    $explication = trim($_POST['explication']);
    $bible = trim($_POST['bible_references']);
    $language = trim($_POST['language']);
    $active = isset($_POST['active']) ? 1 : 0;

    // VALIDATION
    if (!$title || !$question || !$A || !$B || !$C || !$D || !$answer) {
        $error = "All required fields must be filled";
    }
    elseif (!in_array($answer, ['A','B','C','D'])) {
        $error = "Correct answer must be A, B, C or D";
    }
    else {

        $db->update(
            "data_mcq",
            [
                "title" => $title,
                "question" => $question,
                "choiceA" => $A,
                "choiceB" => $B,
                "choiceC" => $C,
                "choiceD" => $D,
                "reponseChoice" => $answer,
                "explication" => $explication,
                "bible_references" => $bible,
                "language" => $language,
                "searchoptions" => SearchOptions::valueSearchDisplay(),
                "active" => $active
            ],
            "id = :id AND users_publisher_id = :uid",
            [
                ":id" => $id,
                ":uid" => $userId
            ]
        );

        $message = "MCQ updated successfully!";

        // Refresh data
        $mcq = $db->selectOne(
            "SELECT * FROM data_mcq WHERE id = ?",
            [$id]
        );
    }
}
?>

<div class="container">

    <div class="card p-4 shadow" style="max-width:700px; margin:auto; border-radius:15px;">

        <h3 class="text-center mb-3">Edit MCQ</h3>

        <?php if($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST">

            <!-- TITLE -->
            <div class="mb-3">
                <label>Title *</label>
                <input type="text" name="title" class="form-control"
                       value="<?= htmlspecialchars($mcq['title']) ?>" required>
            </div>

            <!-- QUESTION -->
            <div class="mb-3">
                <label>Question *</label>
                <textarea name="question" class="form-control" rows="3" required><?= htmlspecialchars($mcq['question']) ?></textarea>
            </div>

            <!-- CHOICES -->
            <div class="mb-3">
                <label>Choice A *</label>
                <input type="text" name="choiceA" class="form-control"
                       value="<?= htmlspecialchars($mcq['choiceA']) ?>" required>
            </div>

            <div class="mb-3">
                <label>Choice B *</label>
                <input type="text" name="choiceB" class="form-control"
                       value="<?= htmlspecialchars($mcq['choiceB']) ?>" required>
            </div>

            <div class="mb-3">
                <label>Choice C *</label>
                <input type="text" name="choiceC" class="form-control"
                       value="<?= htmlspecialchars($mcq['choiceC']) ?>" required>
            </div>

            <div class="mb-3">
                <label>Choice D *</label>
                <input type="text" name="choiceD" class="form-control"
                       value="<?= htmlspecialchars($mcq['choiceD']) ?>" required>
            </div>

            <!-- ANSWER -->
            <div class="mb-3">
                <label>Correct Answer *</label>
                <select name="answer" class="form-control">
                    <option value="A" <?= $mcq['reponseChoice']=='A'?'selected':'' ?>>A</option>
                    <option value="B" <?= $mcq['reponseChoice']=='B'?'selected':'' ?>>B</option>
                    <option value="C" <?= $mcq['reponseChoice']=='C'?'selected':'' ?>>C</option>
                    <option value="D" <?= $mcq['reponseChoice']=='D'?'selected':'' ?>>D</option>
                </select>
            </div>

            <!-- EXPLANATION -->
            <div class="mb-3">
                <label>Explanation</label>
                <textarea name="explication" class="form-control"><?= htmlspecialchars($mcq['explication']) ?></textarea>
            </div>

            <!-- BIBLE REFERENCES -->
            <div class="mb-3">
                <label>Bible References</label>
                <input type="text" name="bible_references" class="form-control"
                       value="<?= htmlspecialchars($mcq['bible_references']) ?>">
            </div>

            <!-- LANGUAGE -->
            <div class="mb-3">
                <label>Language </label>
                <?= $languageChoice->displaySelect($mcq['language'], false); ?>
            </div>

            <!-- ACTIVE -->
            <div class="form-check mb-3">
                <input type="checkbox" name="active" class="form-check-input"
                       <?= $mcq['active'] ? 'checked' : '' ?>>
                <label class="form-check-label">Active</label>
            </div>

            <!-- ACTIVE -->
            <div class="form-check mb-3 searchoption">
                <?= SearchOptions::formSearchDisplay($mcq['searchoptions'], false) ?>
            </div>

            <!-- SUBMIT -->
            <button class="btn btn-primary w-100">Update MCQ</button>

        </form>

    </div>

</div>
