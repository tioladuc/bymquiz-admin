<!-- HERO SECTION -->
<div class="text-center p-5 bg-primary text-white rounded shadow">
    <h1 class="display-4">Welcome to BYM Quiz</h1>
    <p class="lead">Learn, Create, and Share Bible Knowledge</p>

    <?php if(!isLogged()): ?>
        <a href="index.php?mnu=login" class="btn btn-light btn-lg me-2">Login</a>
        <a href="index.php?mnu=createaccount" class="btn btn-warning btn-lg">Create Account</a>
    <?php else: ?>
        <a href="#" class="btn btn-light btn-lg">Go to Dashboard</a>
    <?php endif; ?>
</div>

<!-- FEATURES -->
<div class="row mt-5 text-center">

    <div class="col-md-4">
        <div class="card p-4 shadow h-100">
            <h4>📚 Learn</h4>
            <p>Explore Bible teachings and deepen your understanding through structured learning content.</p>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-4 shadow h-100">
            <h4>❓ Create MCQs</h4>
            <p>Create multiple-choice questions and challenge others in a fun and interactive way.</p>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-4 shadow h-100">
            <h4>⭐ Vote</h4>
            <p>Rate content created by others and help highlight the best quizzes and lessons.</p>
        </div>
    </div>

</div>

<!-- HOW IT WORKS -->
<div class="mt-5">
    <h2 class="text-center mb-4">How It Works</h2>

    <div class="row text-center">

        <div class="col-md-3">
            <div class="card p-3 shadow h-100">
                <h5>1. Create Account</h5>
                <p>Register and wait for admin validation.</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 shadow h-100">
                <h5>2. Add Content</h5>
                <p>Create MCQs and learning materials.</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 shadow h-100">
                <h5>3. Vote</h5>
                <p>Evaluate other users' content.</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 shadow h-100">
                <h5>4. Grow</h5>
                <p>Improve knowledge and help others learn.</p>
            </div>
        </div>

    </div>
</div>

<!-- STATS (OPTIONAL) -->
<?php
$db = DBCore::getInstance();

$totalUsers = $db->selectOne("SELECT COUNT(*) as total FROM users_publisher")['total'] ?? 0;
$totalMcq = $db->selectOne("SELECT COUNT(*) as total FROM data_mcq")['total'] ?? 0;
$totalLearning = $db->selectOne("SELECT COUNT(*) as total FROM data_learning")['total'] ?? 0;
?>

<div class="row text-center mt-5">

    <div class="col-md-4">
        <div class="card p-4 shadow">
            <h3><?= $totalUsers ?></h3>
            <p>Users</p>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-4 shadow">
            <h3><?= $totalMcq ?></h3>
            <p>MCQs</p>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-4 shadow">
            <h3><?= $totalLearning ?></h3>
            <p>Learning Contents</p>
        </div>
    </div>

</div>

<!-- CALL TO ACTION -->
<div class="text-center mt-5 mb-5">
    <?php if(!isLogged()): ?>
        <h3>Start your journey today!</h3>
        <a href="index.php?mnu=createaccount" class="btn btn-success btn-lg mt-3">Join Now</a>
    <?php else: ?>
        <h3>Continue learning and sharing!</h3>
        <a href="#" class="btn btn-primary btn-lg mt-3">Go to Dashboard</a>
    <?php endif; ?>
</div>