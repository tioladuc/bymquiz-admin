<?php 
$message = "";
$error = "";
$db = DBCore:: getInstance();
$languageChoice = new CustomField("language", "SELECT code as _key_, name as _value_ FROM languages ORDER BY name", null);


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

    // =========================
    // VALIDATION
    // =========================
    if (!$title || !$question || !$A || !$B || !$C || !$D || !$answer) {
        $error = "All required fields must be filled";
    }
    elseif (!in_array($answer, ['A','B','C','D'])) {
        $error = "Correct answer must be A, B, C or D";
    }
    else {
        print_r($_SESSION);
        $db->insert("data_mcq", [
            "title" => $title,
            "question" => $question,
            "choiceA" => $A,
            "choiceB" => $B,
            "choiceC" => $C,
            "choiceD" => $D,
            "reponseChoice" => $answer,
            "explication" => $explication,
            "bible_references" => $bible,
            "searchoptions" => SearchOptions::valueSearchDisplay(),
            "users_publisher_id" => $_SESSION['user']['id'],
            "language" => $language,
            "created_on" => date("Y-m-d H:i:s"),
            "active" => 1
        ]);

        $message = "MCQ added successfully!";
    }
}
?>

<div class="container">

    <div class="card p-4 shadow" style="max-width:700px; margin:auto; border-radius:15px;">

        <h3 class="text-center mb-3">Add MCQ</h3>

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
                <input type="text" name="title" class="form-control" required>
            </div>

            <!-- QUESTION -->
            <div class="mb-3">
                <label>Question *</label>
                <textarea name="question" class="form-control" rows="3" required></textarea>
            </div>

            <!-- CHOICES -->
            <div class="mb-3">
                <label>Choice A *</label>
                <input type="text" name="choiceA" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Choice B *</label>
                <input type="text" name="choiceB" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Choice C *</label>
                <input type="text" name="choiceC" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Choice D *</label>
                <input type="text" name="choiceD" class="form-control" required>
            </div>

            <!-- ANSWER -->
            <div class="mb-3">
                <label>Correct Answer (A, B, C or D) *</label>
                <input type="text" name="answer" class="form-control" maxlength="1" required>
            </div>

            <!-- EXPLANATION -->
            <div class="mb-3">
                <label>Explanation</label>
                <textarea name="explication" class="form-control"></textarea>
            </div>

            <!-- BIBLE REFERENCES -->
            <div class="mb-3">
                <label>Bible References</label>
                <input type="text" name="bible_references" class="form-control">
            </div>

            <!-- LANGUAGE -->
            <div class="mb-3">
                <label>Language </label>
                <?= $languageChoice->displaySelect(null, false); ?>
            </div>

            <!-- ACTIVE -->
            <div class="form-check mb-3 searchoption">
                <?= SearchOptions::formSearchDisplay('', false) ?>
            </div>

            <!-- SUBMIT -->
            <button class="btn btn-primary w-100">Save MCQ</button>

        </form>

    </div>

</div>
