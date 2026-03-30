<?php 
$message = "";
$error = "";
$db = DBCore:: getInstance();
$languageChoice = new CustomField("language", "SELECT code as _key_, name as _value_ FROM languages ORDER BY name", null);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $bible = trim($_POST['bible_references']);
    $keywords = trim($_POST['keywords']);
    $language = trim($_POST['language']);
    $active = isset($_POST['active']) ? 1 : 0;
    
    // =========================
    // VALIDATION
    // =========================
    if (!$title || !$description) {
        $error = "Title and Description are required";
    } else {

        $db->insert("data_learning", [
            "title" => $title,
            "description" => $description,
            "bible_references" => $bible,
            "language" => $language,
            "keywords" => $keywords,
            "searchoptions" => SearchOptions::valueSearchDisplay(),
            "users_publisher_id" => $_SESSION['user']['id'],
            "created_on" => date("Y-m-d H:i:s"),
            "active" => $active
        ]);

        $message = "Learning content added successfully!";
    }
}
?>

<div class="container">

    <div class="card p-4 shadow" style="max-width:700px; margin:auto; border-radius:15px;">

        <h3 class="text-center mb-3">Add Learning Content</h3>

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

            <!-- DESCRIPTION -->
            <div class="mb-3">
                <label>Description *</label>
                <textarea name="description" class="form-control" rows="5" required></textarea>
            </div>

            <!-- BIBLE REFERENCES -->
            <div class="mb-3">
                <label>Bible References</label>
                <input type="text" name="bible_references" class="form-control">
            </div>

            <!-- LANGUAGE -->
            <div class="mb-3">
                <label>Language</label>
                <?= $languageChoice->displaySelect(null, false); ?>
            </div>
            
            <!-- KEYWORDS -->
            <div class="mb-3">
                <label>Keywords *</label>
                <textarea name="keywords" class="form-control" rows="5" required></textarea>
            </div>

            <!-- ACTIVE -->
            <div class="form-check mb-3">
                <input type="checkbox" name="active" class="form-check-input" checked>
                <label class="form-check-label">Active</label>
            </div>

            <!-- ACTIVE -->
            <div class="form-check mb-3 searchoption">
                <?= SearchOptions::formSearchDisplay('', false) ?>
            </div>

            <!-- SUBMIT -->
            <button class="btn btn-primary w-100">Save Learning</button>

        </form>

    </div>

</div>
