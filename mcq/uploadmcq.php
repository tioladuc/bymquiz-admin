<?php 
//title,question,choiceA,choiceB,choiceC,choiceD,reponseChoice,explication,bible_references,language,active;bibleSection;difficulty
//Faith,What is faith?,Trust,Hope,Love,Prayer,A,Faith is trust,Hebrews 11:1,en,1 

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
                    
                    $data = array_map(function ($value) {
                        return mb_convert_encoding($value, 'UTF-8', 'auto');
                    }, $data);
                    $row++;

                    // SKIP HEADER
                    if ($row == 1) continue;

                    if (count($data) < 11) continue;

                    list(
                        $title,
                        $question,
                        $A,
                        $B,
                        $C,
                        $D,
                        $answer,
                        $explication,
                        $bible,
                        $language,
                        $active,
                        $bibleSection,
                        $difficulty,
                        $bibleBooks,
                        $typeOfKnowledge,
                        $keywords
                    ) = $data;

                    $answer = strtoupper(trim($answer));
                    //echo "****** $answer ******<br>"; print_r($data);
                    /*echo "<br><br><br>";*/
                    //print_r($data);
                    // VALIDATION
                    if($answer == null || trim($answer)=="") continue;
                    if (!in_array($answer, ['A','B','C','D'])) {
                        echo "sdfl;mds;f sd;f dl;fklfsdf;mf;dsfsdf ";
                        continue;
                    }

                    $db->insert("data_mcq", [
                        "title" => trim($title),
                        "question" => trim($question) . " ($bibleBooks)",
                        "choiceA" => trim($A),
                        "choiceB" => trim($B),
                        "choiceC" => trim($C),
                        "choiceD" => trim($D),
                        "reponseChoice" => $answer,
                        "explication" => trim($explication),
                        "bible_references" => trim($bible),

                        /*"bibleBooks" => trim($bibleBooks),
                        "typeOfKnowledge" => trim($typeOfKnowledge),*/
                        "keywords" => trim($keywords),
                        "language" => trim($language),
                        "active" => (int)$active,
                        "users_publisher_id" => $userId,
                        "searchoptions" => "<bibleSection>$bibleSection</bibleSection><difficulty>$difficulty</difficulty><bibleBooks>$bibleBooks</bibleBooks><typeOfKnowledge>$typeOfKnowledge</typeOfKnowledge>",
                        "created_on" => date('Y-m-d H:i:s')
                    ]);

                    $inserted++;
                }

                fclose($handle);

                $message = "$inserted MCQs uploaded successfully!";
            }
        }
    }
}
?>

<div class="container">

    <div class="card p-4 shadow" style="max-width:600px; margin:auto; border-radius:15px;">

        <h3 class="text-center mb-3">Upload MCQ CSV</h3>

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
            title, question, A, B, C, D, answer, explanation, bible, language, active <br/><br/>
        </small>
        

         <!-- HELP -->
         <small>
            <strong>CSV sample download:</strong><br>
            <a href="mcq.csv">Download Sample CSV</a>
        </small>

    </div>

</div>
