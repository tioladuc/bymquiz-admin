<?php
$db = DBCore:: getInstance();

$message = '';

if($_POST) {
    if(isset($_POST['password'])){ //new password define
        $email = $_POST['email'] ?? '';
        $code = $_POST['code'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $existing = $db->selectOne(
            "SELECT id FROM users_publisher WHERE email = ? AND code = ?",
            [$email, $code]
        );
        if($existing && ($password == $confirm_password)) {
            $sql = "UPDATE users_publisher SET code = NULL, password = ? WHERE email = ?";
            $stmt = $db->getConnection()->prepare($sql);
            $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $email]);
            $message = "Password updated successfully!";
            $_SESSION['msg'] = $GLOBALS['msg'];
            header('Location: index.php?mnu=home');
            exit();
        }
    }
    else { //sendign code section
        $email = $_POST['email'] ?? '';
        $existing = $db->selectOne(
            "SELECT * FROM users_publisher WHERE email = ? ",
            [$email]
        );
        if($existing) {
            $code = rand(100000, 999999); //print_r($existing);
            $sql = "UPDATE users_publisher SET code = ? WHERE email = ?";
            $stmt = $db->getConnection()->prepare($sql);
            $stmt->execute([$code, $email]);
            //$message = "A code has been sent to your email";

            $subject = "BYM Quiz - Password Reset";
            $body = "Hello ".$existing['username'].",\n\n".
                    "Your new code is: $code\n\n".
                    "Please login and change it.\n\nBYM Quiz";

            $headers = "From: no-reply@bymquiz.com";

            if (mail($email, $subject, $body, $headers)) {
                $message = "A new password has been sent to your email.";
            } else {
                $message = "Failed to send email (check server mail config)";
            }
        }
        else {
            $message = 'Error with the process, check your email or retry later';
        }
    }
}

?>
<div class="container d-flex justify-content-center align-items-center" style="height:100vh;">

    <div class="card p-4 shadow" style="width:400px; border-radius:15px;">

        <h3 class="text-center mb-3">Forgot Password</h3>

        <?php if($GLOBALS['msg']): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($GLOBALS['msg']) ?></div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label>Enter your email</label>
                <input 
                    type="email" 
                    name="email" 
                    class="form-control"
                    required>
            </div>
            <?php  if($_POST) {  ?>
                <div class="mb-3">
                <label>Enter your code received by email</label>
                <input 
                    type="email" 
                    name="code" 
                    class="form-control"
                    required>
            </div>

            <div class="mb-3">
                <label>Enter your new password</label>
                <input 
                    type="password" 
                    name="password" 
                    class="form-control"
                    required>
            </div>

            <div class="mb-3">
                <label>Enter your new password again</label>
                <input 
                    type="password" 
                    name="confirm_password" 
                    class="form-control"
                    required>
            </div>

            <?php    }   ?>

            <button class="btn btn-warning w-100">Send Reset Code</button>

        </form>

        <div class="text-center mt-3">
            <a href="index.php?mnu=login">Back to Login</a>
        </div>

    </div>