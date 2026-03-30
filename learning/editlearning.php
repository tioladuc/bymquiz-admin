<?php 
$db = DBCore::getInstance();

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$id = (int) $_GET['id'];
$userId = $_SESSION['user']['id'];
$languageChoice = new CustomField("language", "SELECT code as _key_, name as _value_ FROM languages ORDER BY name", null);


// =========================
// FETCH LEARNING (OWNER)
// =========================
$learning = $db->selectOne(
    "SELECT * FROM data_learning WHERE id = ? AND users_publisher_id = ?",
    [$id, $userId]
);

if (!$learning || ($learning['achived'] == "ARCHIVED")) {
    die("Access denied or content not found");
}

$message = "";
$error = "";

// =========================
// UPDATE
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $bible = trim($_POST['bible_references']);
    $keywords = trim($_POST['keywords']);
    $language = trim($_POST['language']);
    $active = isset($_POST['active']) ? 1 : 0;

    // VALIDATION
    if (!$title || !$description) {
        $error = "Title and Description are required";
    } else {

        $db->update(
            "data_learning",
            [
                "title" => $title,
                "description" => $description,
                "bible_references" => $bible,
                "keywords" => $keywords,
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

        $message = "Learning content updated successfully!";

        // REFRESH (SAFE)
        $learning = $db->selectOne(
            "SELECT * FROM data_learning WHERE id = ? AND users_publisher_id = ?",
            [$id, $userId]
        );
    }
}
?>

<div class="container">

    <div class="card p-4 shadow" style="max-width:700px; margin:auto; border-radius:15px;">

        <h3 class="text-center mb-3">Edit Learning Content</h3>

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
                       value="<?= htmlspecialchars($learning['title']) ?>" required>
            </div>

            <!-- DESCRIPTION -->
            <div class="mb-3">
                <label>Description *</label>
                <textarea name="description" class="form-control" rows="5" required><?= htmlspecialchars($learning['description']) ?></textarea>
            </div>

            <!-- BIBLE REFERENCES -->
            <div class="mb-3">
                <label>Bible References</label>
                <input type="text" name="bible_references" class="form-control"
                       value="<?= htmlspecialchars($learning['bible_references']) ?>">
            </div>

            <!-- LANGUAGE -->
            <div class="mb-3">
                <label>Language </label>
                <?= $languageChoice->displaySelect($learning['language'], false); ?>
            </div>

            <!-- KEYWORDS -->
            <div class="mb-3">
                <label>Bible References</label>
                <input type="text" name="keywords" class="form-control"
                       value="<?= htmlspecialchars($learning['keywords']) ?>">
            </div>

            <!-- ACTIVE -->
            <div class="form-check mb-3">
                <input type="checkbox" name="active" class="form-check-input"
                       <?= $learning['active'] ? 'checked' : '' ?>>
                <label class="form-check-label">Active</label>
            </div>

            <!-- ACTIVE -->
            <div class="form-check mb-3 searchoption">
                <?= SearchOptions::formSearchDisplay($learning['searchoptions'], false) ?>
            </div>

            <!-- SUBMIT -->
            <button class="btn btn-primary w-100">Update Learning</button>

        </form>

    </div>

</div>
