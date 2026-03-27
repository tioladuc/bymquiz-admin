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
// FETCH LEARNING + VOTES
// =========================
$learning = $db->selectOne("
    SELECT l.*, 
           IFNULL(SUM(v.vote_from_0_to_10),0) as total_votes,
           COUNT(v.id) as voters
    FROM data_learning l
    LEFT JOIN vote_learning v ON l.id = v.data_learning_id
    WHERE l.id = ? 
    GROUP BY l.id
", [$id]);

if (!$learning) {
    die("Learning content not found");
}

// =========================
// CHECK IF USER ALREADY VOTED
// =========================
$userVote = $db->selectOne(
    "SELECT * FROM vote_learning WHERE data_learning_id = ? AND users_publisher_id = ?",
    [$id, $userId]
);
?>

<div class="container">

    <div class="card p-4 shadow" style="max-width:800px; margin:auto; border-radius:15px;">

        <!-- TITLE -->
        <h3><?= htmlspecialchars($learning['title']) ?> (<?= htmlspecialchars($learning['language']) ?>)</h3>

        <!-- DESCRIPTION -->
        <p class="mt-3"><?= nl2br(htmlspecialchars($learning['description'])) ?></p>

        <!-- BIBLE REFERENCES -->
        <?php if(!empty($learning['bible_references'])): ?>
            <p><strong>📖 <?= htmlspecialchars($learning['bible_references']) ?></strong></p>
        <?php endif; ?>

        <!-- ACTIVE -->
        <div class="form-check mb-3 searchoption">
            <?= SearchOptions::formSearchDisplay($learning['searchoptions'], true) ?>
        </div>
        <!-- VOTES -->
        <div class="mb-3">
            ⭐ <strong>Total Score:</strong> <?= $learning['total_votes'] ?> <br>
            👥 <strong>Voters:</strong> <?= $learning['voters'] ?>
        </div>

        <!-- VOTING -->
        <?php if($learning['users_publisher_id'] != $userId): ?>

            <?php if(!$userVote): ?>
                <form method="post" action="vote.php" class="d-flex gap-2">

                    <input type="hidden" name="id" value="<?= $learning['id'] ?>">
                    <input type="hidden" name="type" value="learning">

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
                You cannot vote your own content
            </div>
        <?php endif; ?>

        <!-- BACK / EDIT / DELETE -->
        <div class="mt-3">
            <?php if($learning['achived'] != "ARCHIVED") { ?>
                <form action="<?= $learning['users_publisher_id']==$userId ? 'index.php?mnu=learning&op=mylist' : 'index.php?mnu=learning&op=vote' ?>" method="post">
                    <?php if($learning['users_publisher_id']==$userId) { ?>
                        <!-- DELETE -->
                        <input type='hidden' name='id' value="<?= $learning['id'] ?>" />
                        <input type='submit' name='delete' value='Delete' 
                            class="btn btn-danger btn-sm"
                            onclick="return confirm('Delete this content?')" />

                        <!-- EDIT -->
                        <a href="index.php?mnu=learning&op=edit&id=<?= $learning['id'] ?>"
                            class="btn btn-warning btn-sm">Edit</a>

                        <a href="index.php?mnu=learning&op=mylist" class="btn btn-outline-primary">⬅ Back to list</a>
                    <?php } else { ?>
                        <input type="hidden" name="id" value="<?= $learning['id'] ?>">
                        <input type="hidden" name="votesubmit" value="vote">

                        <input type="number" name="vote" min="0" max="10"
                            value="" class="form-control" required>

                        <br/><button class="btn btn-success">Vote</button>
                    <?php } ?>
                </form>
            <?php } ?>
        </div>

    </div>

</div>