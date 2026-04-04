<?php
    require_once "core/connexion.php";
    require_once "core/searchoption.php";
    require_once "core/dbcustomfields.php";
    ini_set('default_charset', 'UTF-8');
    
    $GLOBALS['ApiUrl'] = ($_SERVER['HTTP_HOST']=="api.institutblaina.cm") ? "http://api.institutblaina.cm/bymquiz" : "https://yehoshoualevivant.com/bymquiz";//"http://api.institutblaina.cm/bymquiz";
    $GLOBALS['MaxPage'] = 10;
    $GLOBALS['msg'] = '';
    $_GET['mnu'] = isset($_GET['mnu']) ? $_GET['mnu'] : 'home';
    session_start();

    Connexion::run();
    
    if(!isset($_SESSION['typeUser'] ) && !($_GET['mnu'] == 'login' || $_GET['mnu'] == 'logout' 
        || $_GET['mnu'] == 'home' || $_GET['mnu'] == 'createaccount')) {
            header('Location: index.php?mnu=home');
            exit();   
    }

    function isLogged() {
        return isset($_SESSION['typeUser'] );
    }
    function isAdmin() {
        return isset($_SESSION['typeUser'] ) && $_SESSION['typeUser'] == 'admin';
    }
    $GLOBALS['msg'] = $_SESSION['msg'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BYM-Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
<div class="container-fluid">

    <a class="navbar-brand" href="index.php">BYM Quiz</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="menu">
    <ul class="navbar-nav me-auto">

        <!-- HOME -->
        <li class="nav-item">
            <a class="nav-link" href="index.php?mnu=home">Home</a>
        </li>

        <?php if(!isLogged()): ?>

            <!-- NOT LOGGED -->
            <li class="nav-item">
                <a class="nav-link" href="index.php?mnu=login">Login</a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="index.php?mnu=createaccount">Create Account</a>
            </li>

        <?php else: ?>

            <!-- LOGGED USER -->

            <!-- MCQ Dropdown -->
            <?php if(!isAdmin()): ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="mcqDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    MCQ
                </a>
                <ul class="dropdown-menu" aria-labelledby="mcqDropdown">
                    <li><a class="dropdown-item" href="index.php?mnu=mcq&op=add">Add MCQ</a></li>
                    <li><a class="dropdown-item" href="index.php?mnu=mcq&op=upload">Upload MCQ Data</a></li>
                    <li><a class="dropdown-item" href="index.php?mnu=mcq&op=mylist">View My MCQ</a></li>
                    <li><a class="dropdown-item" href="index.php?mnu=mcq&op=vote">Vote MCQ</a></li>
                </ul>
            </li>
            <?php endif; ?>

            <!-- Learning Dropdown -->
            <?php if(!isAdmin()): ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="learningDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Learning
                </a>
                <ul class="dropdown-menu" aria-labelledby="learningDropdown">
                    <li><a class="dropdown-item" href="index.php?mnu=learning&op=add">Add Learning</a></li>
                    <li><a class="dropdown-item" href="index.php?mnu=learning&op=upload">Upload Learning Data</a></li>
                    <li><a class="dropdown-item" href="index.php?mnu=learning&op=mylist">View My Learning</a></li>
                    <li><a class="dropdown-item" href="index.php?mnu=learning&op=vote">Vote Learning</a></li>
                </ul>
            </li>
            <?php endif; ?>

            <!-- ADMIN ONLY -->
            <?php if(isAdmin()): ?>
                <li class="nav-item">
                    <a class="nav-link text-warning" href="index.php?mnu=validate">Validate Account</a>
                </li>
            <?php endif; ?>
            <?php if(isAdmin()): ?>
                <li class="nav-item">
                    <a class="nav-link text-warning" href="index.php?mnu=production">Going online</a>
                </li>
            <?php endif; ?>

            <li class="nav-item">
                <a class="nav-link text-danger" href="index.php?mnu=logout">Logout</a>
            </li>

        <?php endif; ?>

    </ul>

    <!-- RIGHT SIDE USER -->
    <span class="navbar-text text-white">
        <?php if(isLogged()): ?>
            👤 <?= htmlspecialchars($_SESSION['userLogin']) ?>
        <?php endif; ?>
    </span>

</div>
</div>
</nav>

<div>
    <?php if($GLOBALS['msg']){echo "<center><font style='color:red;'>". $GLOBALS['msg'] ."</font></center>";} ?>
</div>
<div class="container mt-4">
    <?php 
        if($_GET['mnu']=='login') require_once("account/login.php");
        if($_GET['mnu']=='createaccount') require_once("account/createaccount.php");
        if($_GET['mnu']=='forgotpassword') require_once("account/forgotpassword.php");
        if($_GET['mnu']=='validate') require_once("account/validateaccount.php");
        if($_GET['mnu']=='production') require_once("core/miseenproduction.php");

        if($_GET['mnu']=='home') require_once("core/home.php");
        if($_GET['mnu']=='mcq') {
            if($_GET['op']=='add') require_once("mcq/addmcq.php");
            if($_GET['op']=='mylist') require_once("mcq/listmcq.php");
            if($_GET['op']=='edit') require_once("mcq/editmcq.php");
            if($_GET['op']=='view') require_once("mcq/viewmcq.php");
            if($_GET['op']=='upload') require_once("mcq/uploadmcq.php");
            if($_GET['op']=='vote') require_once("mcq/votelistmcq.php");
        }

        if($_GET['mnu']=='learning') {
            if($_GET['op']=='add') require_once("learning/addlearning.php");
            if($_GET['op']=='mylist') require_once("learning/listlearning.php");
            if($_GET['op']=='edit') require_once("learning/editlearning.php");
            if($_GET['op']=='view') require_once("learning/viewlearning.php");
            if($_GET['op']=='upload') require_once("learning/uploadlearning.php");
            if($_GET['op']=='vote') require_once("learning/votelistlearning.php");
        }
        
    ?>



</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>