<?php
$db = DBCore::getInstance();


//echo $GLOBALS['admin'][$_SESSION['userLogin']][1] . " ///" . $_SESSION['userPwd'];
if (!(isset($GLOBALS['admin'][$_SESSION['userLogin']])  
    && $GLOBALS['admin'][$_SESSION['userLogin']][1] == $_SESSION['userPwd'])) {
    die("Access denied. Only super administrators can validate accounts.");
}

// =========================
// PROCESS VALIDATION
// =========================
if ($_POST && isset($_POST['toggle'])) {
    $userId = (int)$_POST['id'];

    // Get current user
    $user = $db->selectOne("SELECT active FROM users_publisher WHERE id = ?", [$userId]);
    if ($user) {
        $newStatus = $user['active'] ? 0 : 1;
        $db->update(
            "users_publisher",
            ["active" => $newStatus],
            "id = :id",
            [":id" => $userId]
        );
        $msg = "User " . ($newStatus ? "activated" : "deactivated") . " successfully!";
    }
}

// =========================
// GET ALL USERS
// =========================
$users = $db->selectAll("SELECT id, username, email, active, created_at FROM users_publisher ORDER BY created_at DESC");
?>

<div class="container">

    <div class="card p-4 shadow mb-4">
        <h3>Validate User Accounts</h3>

        <?php if(isset($msg)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <?php if(empty($users)): ?>
            <div class="alert alert-info">No users found</div>
        <?php else: ?>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= $u['active'] ? 'Active' : 'Inactive' ?></td>
                    <td><?= $u['created_at'] ?></td>
                    <td>
                        <?php if(!in_array($u['username'], array_keys($GLOBALS['admin']))): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <button type="submit" name="toggle" class="btn btn-<?= $u['active'] ? 'danger' : 'success' ?> btn-sm">
                                <?= $u['active'] ? 'Deactivate' : 'Activate' ?>
                            </button>
                        </form>
                        <?php else: ?>
                            <span class="text-muted">Super Admin</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php endif; ?>
    </div>

</div>