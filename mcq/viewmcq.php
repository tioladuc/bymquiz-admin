<?php 
$db = DBCore::getInstance();
$userId = $_SESSION['user']['id'];

// =========================
// CHECK ID
// =========================
if (!isset($_GET['id'])) {
    die("Invalid request");
}

$id = (int) $_GET['id'];

// =========================
// FETCH MCQ + VOTES
// =========================
$mcq = $db->selectOne("
    SELECT m.*, 
           IFNULL(SUM(v.vote_from_0_to_10),0) as total_votes,
           COUNT(v.id) as voters
    FROM data_mcq m
    LEFT JOIN vote_mcq v ON m.id = v.data_mcq_id
    WHERE m.id = ? 
    GROUP BY m.id
", [$id]);

if (!$mcq) {
    die("MCQ not found");
}

// =========================
// CHECK IF USER ALREADY VOTED
// =========================
$userVote = $db->selectOne(
    "SELECT * FROM vote_mcq WHERE data_mcq_id = ? AND users_publisher_id = ?",
    [$id, $userId]
);
?>

<div class="container">

    <div class="card p-4 shadow" style="max-width:800px; margin:auto; border-radius:15px;">

        <!-- TITLE -->
        <h3><?= htmlspecialchars($mcq['title']) ?> (<?= htmlspecialchars($mcq['language']) ?>)</h3>

        <!-- QUESTION -->
        <p class="mt-3"><?= nl2br(htmlspecialchars($mcq['question'])) ?></p>

        <!-- CHOICES -->
        <ul class="list-group mb-3">
            <li class="list-group-item">A. <?= htmlspecialchars($mcq['choiceA']) ?></li>
            <li class="list-group-item">B. <?= htmlspecialchars($mcq['choiceB']) ?></li>
            <li class="list-group-item">C. <?= htmlspecialchars($mcq['choiceC']) ?></li>
            <li class="list-group-item">D. <?= htmlspecialchars($mcq['choiceD']) ?></li>
        </ul>

        <!-- ANSWER -->
        <details class="mb-3">
            <summary>Show Answer</summary>
            <strong>Correct Answer: <?= $mcq['reponseChoice'] ?></strong>
            <br>
            <small><?= htmlspecialchars($mcq['explication']) ?></small>
        </details>

        <!-- BIBLE -->
        <?php if(!empty($mcq['bible_references'])): ?>
            <p><strong>📖 <?= htmlspecialchars($mcq['bible_references']) ?></strong></p>
        <?php endif; ?>

        <div class="form-check mb-3 searchoption">
            <?= SearchOptions::formSearchDisplay($mcq['searchoptions'], true) ?>
        </div>
        <!-- VOTES -->
        <div class="mb-3">
            ⭐ <strong>Total Score:</strong> <?= $mcq['total_votes'] ?> <br>
            👥 <strong>Voters:</strong> <?= $mcq['voters'] ?>
        </div>

        <!-- VOTING -->
        <?php if($mcq['users_publisher_id'] != $userId): ?>

            <?php if(!$userVote): ?>
                <form method="post" action="vote.php" class="d-flex gap-2">

                    <input type="hidden" name="id" value="<?= $mcq['id'] ?>">
                    <input type="hidden" name="type" value="mcq">

                    <input 
                        type="number" 
                        name="vote" 
                        min="0" 
                        max="10" 
                        class="form-control"
                        placeholder="Rate 0-10"
                        required>

                    <button class="btn btn-success">Submit Vote</button>

                </form>
            <?php else: ?>
                <div class="alert alert-info">
                    You already voted: <strong><?= $userVote['vote_from_0_to_10'] ?>/10</strong>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="alert alert-secondary">
                You cannot vote your own MCQ
            </div>
        <?php endif; ?>

        <!-- BACK -->
        <div class="mt-3">
            <?php if($mcq['achived'] != "ARCHIVED") { ?>
                <form action="<?= $mcq['users_publisher_id']==$userId ? 'index.php?mnu=mcq&op=mylist' : 'index.php?mnu=mcq&op=vote' ?>" method="post">
                    <?php if($mcq['users_publisher_id']==$userId) { ?>
                        <!-- DELETE -->
                        <input type='hidden' name='id' value="<?= $mcq['id'] ?>" />
                        <input type='submit' name='delete' value='Delete' 
                        class="btn btn-danger btn-sm"
                        onclick="return confirm('Delete this MCQ?')" />

                            <!-- OPTIONAL EDIT -->
                        <a href="index.php?mnu=mcq&op=edit&id=<?= $mcq['id'] ?>"
                        class="btn btn-warning btn-sm">
                        Edit
                        </a>

                        <!-- OPTIONAL VIEW -->
                        <a href="index.php?mnu=mcq&op=mylist" class="btn btn-outline-primary">⬅ Back to list</a>
                    <?php }else { ?>
                        <input type="hidden" name="id" value="<?= $mcq['id'] ?>">
                        <input type="hidden" name="votesubmit" value="vote">

                        <input type="number" name="vote" min="0" max="10"
                            value="" class="form-control" required>

                        <br/><button class="btn btn-success">
                            Vote
                        </button>
                    <?php } ?>
                </form>
            <?php } ?>  
            <!--a href="index.php?mnu=mcq&op=list" class="btn btn-outline-primary">⬅ Back to list</a>
            <a href="index.php?mnu=mcq&op=edit&id=<?= $id ?>" class="btn btn-outline-primary">Edit</a>
            <a href="index.php?mnu=mcq&op=list" class="btn btn-outline-primary">⬅ Back to list</a-->
        </div>

    </div>

</div>
