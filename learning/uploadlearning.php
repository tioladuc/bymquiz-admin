<?php 
// title;description;bible_references;language;active;bibleSection;difficulty

$db = DBCore::getInstance();
$userId = $_SESSION['user']['id'];

$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_FILES['csv']) || $_FILES['csv']['error'] !== 0) {
        $error = "Please upload a valid CSV file";
    } else {

        $fileTmp = $_FILES['csv']['tmp_name'];
        $fileName = $_FILES['csv']['name'];

        // CHECK EXTENSION
        if (pathinfo($fileName, PATHINFO_EXTENSION) !== 'csv') {
            $error = "Only CSV files are allowed";
        } else {

            $handle = fopen($fileTmp, "r");

            if ($handle === false) {
                $error = "Cannot open file";
            } else {

                $row = 0;
                $inserted = 0;

                while (($data = fgetcsv($handle, 1000, ";")) !== false) {

                    $row++;

                    // SKIP HEADER
                    if ($row == 1) continue;

                    if (count($data) < 5) continue;

                    list(
                        $title,
                        $description,
                        $bible,
                        $language,
                        $active,
                        $bibleSection,
                        $difficulty,
                        $difficulty,
                        $bibleBooks,
                        $typeOfKnowledge,
                    ) = $data;

                    // BASIC VALIDATION
                    if (empty(trim($title)) || empty(trim($description))) {
                        continue;
                    }

                    $db->insert("data_learning", [
                        "title" => trim($title),
                        "description" => trim($description),
                        "bible_references" => trim($bible),
                        /*"bibleBooks" => trim($bibleBooks),
                        "typeOfKnowledge" => trim($typeOfKnowledge),
                        "keywords" => trim($keywords),*/
                        "language" => trim($language),
                        "active" => (int)$active,
                        "searchoptions" => "<bibleSection>$bibleSection</bibleSection><difficulty>$difficulty</difficulty><bibleBooks>$bibleBooks</bibleBooks><typeOfKnowledge>$typeOfKnowledge</typeOfKnowledge><keywords>$keywords</keywords>",
                        "users_publisher_id" => $userId,
                        "created_on" => date('Y-m-d H:i:s')
                    ]);

                    $inserted++;
                }

                fclose($handle);

                $message = "$inserted learning items uploaded successfully!";
            }
        }
    }
}
?>

<div class="container">

    <div class="card p-4 shadow" style="max-width:600px; margin:auto; border-radius:15px;">

        <h3 class="text-center mb-3">Upload Learning CSV</h3>

        <?php if($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- FORM -->
        <form method="POST" enctype="multipart/form-data">

            <div class="mb-3">
                <label>Select CSV File</label>
                <input type="file" name="csv" class="form-control" accept=".csv" required>
            </div>

            <button class="btn btn-primary w-100">Upload</button>

        </form>

        <hr>

        <!-- HELP -->
        <small>
            <strong>CSV format:</strong><br>
            title;description;bible_references;language;active <br/><br/>
        </small>

        <br><br>

        <!-- SAMPLE -->
        <small>
            <strong>CSV sample download:</strong><br>
            <a href="learning.csv">Download Sample CSV</a>
        </small>

    </div>

</div>