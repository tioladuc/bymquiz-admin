<?php
$db = DBCore::getInstance();


//echo $GLOBALS['admin'][$_SESSION['userLogin']][1] . " ///" . $_SESSION['userPwd'];
if (!(isset($GLOBALS['admin'][$_SESSION['userLogin']])  
    && $GLOBALS['admin'][$_SESSION['userLogin']][1] == $_SESSION['userPwd'])) {
    die("Access denied. Only super administrators can put online data.");
}

$minVoters = -1;
$minAverageScore = -1;
$achived = "archived";
$active = 0;
$msg = "";

if($_POST && isset($_POST['putonline'])) {
    $updateMCQ = "UPDATE data_mcq SET achived = '$achived' WHERE id IN (". ($_POST['mcq']==""? "0" : $_POST['mcq'] ) ." );";
    $updateLearning = "UPDATE data_learning SET achived = '$achived' WHERE id IN (". ($_POST['learning']==""? "0" : $_POST['learning'] ) ." );";

    $stmt = $db->getConnection()->prepare($updateMCQ);
    $stmt->execute([]);
    $stmt = $db->getConnection()->prepare($updateLearning);
    $stmt->execute();

    $inserLectMCQ = "INSERT INTO data_mcq_validate (   title,    question,  choiceA,
        choiceB,   choiceC,   choiceD,    reponseChoice,   explication,   bible_references,
        description,   language,   created_on,   active,  searchoptions,  users_publisher_id, data_mcq_id
    )   SELECT  title,   question,   choiceA,   choiceB,  choiceC,  choiceD,
        reponseChoice,  explication,   bible_references,   description,  language,  created_on,
        active,  searchoptions,  users_publisher_id, id AS data_mcq_id
    FROM data_mcq WHERE data_mcq.id IN (".  ($_POST['mcq']==""? "0" : $_POST['mcq'] ) ." );";

    $inserLectLearning = "INSERT INTO data_learning_validate (  title,  description,  bible_references,
    language,  created_on,  active,  searchoptions,  users_publisher_id,  data_learning_id)
    SELECT   title,  description,  bible_references,  language,  created_on,  active,
        searchoptions,   users_publisher_id,   id AS data_learning_id
    FROM data_learning WHERE data_learning.id IN (". ($_POST['learning']==""? "0" : $_POST['learning'] ) ." );";

    try {
        $stmt = $db->getConnection()->prepare($inserLectMCQ);
        $stmt->execute([]);
        $stmt = $db->getConnection()->prepare($inserLectLearning);
        $stmt->execute();

        $stmt = $db->getConnection()->prepare($updateMCQ);
        $stmt->execute();
        $stmt = $db->getConnection()->prepare($updateLearning);
        $stmt->execute();
        $msg = "Operation done! ";
    } catch (Exception $e) {
        $pdo->rollBack();
        $msg = "Error while processing";

    }
    
}

// =========================
// GET ALL USERS
// =========================
$wherePart = "b_stats.total_items > $minVoters AND b_stats.avg_mark > $minAverageScore";
//$wherePart = "(b_stats.total_items IS NULL OR b_stats.total_items > $minVoters) AND (b_stats.avg_mark IS NULL OR b_stats.avg_mark > $minAverageScore)";
$sqlMcq = "SELECT 
            d.*,
            COALESCE(b_stats.total_items, 0) AS total_items,
            COALESCE(b_stats.avg_mark, 0) AS avg_mark
            FROM data_mcq d
            LEFT OUTER JOIN (
            SELECT 
                v.data_mcq_id,
                COUNT(*) AS total_items,
                AVG(v.vote_from_0_to_10) AS avg_mark
            FROM vote_mcq v
            GROUP BY v.data_mcq_id
            ) b_stats ON b_stats.data_mcq_id = d.id
            WHERE d.active = $active AND d.achived NOT LIKE '$achived' 
            AND $wherePart ";

$sqlLearning = "SELECT 
            d.*,
            COALESCE(b_stats.total_items, 0) AS total_items,
            COALESCE(b_stats.avg_mark, 0) AS avg_mark
            FROM data_learning d
            LEFT OUTER JOIN (
            SELECT 
                v.data_learning_id,
                COUNT(*) AS total_items,
                AVG(v.vote_from_0_to_10) AS avg_mark
            FROM vote_learning v
            GROUP BY v.data_learning_id
            ) b_stats ON b_stats.data_learning_id = d.id
            WHERE d.active = $active AND d.achived NOT LIKE '$achived' 
            AND $wherePart ";

$dataMCQ = $db->selectAll($sqlMcq);

$dataLearning = $db->selectAll($sqlLearning);
//print_r($dataMCQ); echo "=============================================="; print_r($dataLearning);
$idsMCQ = "";
$idsLearning = "";
?>

<div class="container">
    
    <div class="card p-4 shadow mb-4">
        <h3>Going online with ata</h3>

        <?php if(isset($msg)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>


        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>MCQ Title</th>
                    <th>Voters</th>
                    <th>Score</th>
                    <th>&nbsp;</th>
                    <th>Learning Title</th>
                    <th>Voters</th>
                    <th>Score</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i=0; $i < max( count($dataMCQ), count($dataLearning)) ; $i++) { ?>
                  
                <tr>
                    <?php if( $i>=count($dataMCQ)) {
                        echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";} else { 
                            $idsMCQ .= ($idsMCQ=="" ? "": ",") . $dataMCQ[$i]['id'];    
                        ?>
                        <td><a href="index.php?mnu=mcq&op=view&id=<?= $dataMCQ[$i]['id'] ?>" target="_blank"><?= htmlspecialchars($dataMCQ[$i]['title']) ?></a></td>
                        <td><?= htmlspecialchars($dataMCQ[$i]['total_items']) ?></td>
                        <td><?= htmlspecialchars($dataMCQ[$i]['avg_mark']) ?></td>
                    <?php  } ?>
                    <td>&nbsp;</td>
                    <?php if( $i>=count($dataLearning)) { 
                        echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";} else { 
                            $idsLearning .= ($idsLearning=="" ? "": ",") . $dataLearning[$i]['id']; ?>
                        <td><a href="index.php?mnu=mcq&op=view&id=<?= $dataLearning[$i]['id'] ?>" target="_blank"><?= htmlspecialchars($dataLearning[$i]['title']) ?></a></td>
                        <td><?= htmlspecialchars($dataLearning[$i]['total_items']) ?></td>
                        <td><?= htmlspecialchars($dataLearning[$i]['avg_mark']) ?></td>
                    <?php  } ?>                    
                </tr>
                <?php } ?>
            </tbody>
        </table>
                <center>
                <form action="" method="post">
                    <input type="hidden" name="mcq" value="<?= $idsMCQ ?>" />
                    <input type="hidden" name="learning" value="<?= $idsLearning ?>" />
                    <input type="submit" value="Put Online" name="putonline">
                </form>
                </center>
        
    </div>

</div>