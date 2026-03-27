<?php
require_once("DBCore.php");

$GLOBALS['admin'] = array(
    "administrator1"=> array(-1, "password1"), 
    "administrator2"=>array(-2, "password2")
);

class Connexion {
    public static function run() {
        if($_GET['mnu'] == 'login') {
            self::login();
        }

        if($_GET['mnu'] == 'logout') {
            self::logout();
        }

        if($_GET['mnu'] == 'createaccount') {
            self::createAccount();
        }

        $_SESSION['msg'] = $GLOBALS['msg'];
    }

    private static function login() {        
        if($_POST) {
            $typeConnexion = $_POST['typeuser'];
            $login = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if($typeConnexion == "admin" && isset($GLOBALS['admin'][$login]) && $GLOBALS['admin'][$login][1] == $password) {

                $_SESSION['typeUser'] = 'admin';
                $_SESSION['userId'] = $GLOBALS['admin'][$login][0];
                $_SESSION['userPwd'] = $password;
                $_SESSION['userLogin'] = $login;
                $_SESSION['userEmail'] = $login;
                $_SESSION['user']['id'] = $GLOBALS['admin'][$login][0];
                $GLOBALS['msg'] = "Vous êtes connecté en tant qu'administrateur";
                $_SESSION['msg'] = $GLOBALS['msg'];
                header('Location: index.php?mnu=home');
                exit();
            }
            else {
                $sql = "SELECT * FROM `users_publisher` WHERE email = ? AND active = 1";
                //echo "SELECT * FROM `users_publisher` WHERE email ='$login' AND active = 1";
                $data = DBCore::getInstance()->selectOne($sql, [ $login ]);
                if(isset($data['password']) && password_verify($password, $data['password'])) {
                    $_SESSION['typeUser'] = 'user';
                    $_SESSION['userId'] = $data['id'];
                    $_SESSION['userPwd'] = $data['password'];
                    $_SESSION['userLogin'] = $data['username'];
                    $_SESSION['userEmail'] = $data['email'];
                    $_SESSION['user'] = $data;
                    $GLOBALS['msg'] = "Vous êtes connecté en tant qu'utilisateur";
                    $_SESSION['msg'] = $GLOBALS['msg'];
                    header('Location: index.php?mnu=home');
                    exit();
                }                
                else {
                    $GLOBALS['msg'] = "Erreur pendant la connexion de l'utilisateur";
                }
            }

        }
    }
    private static function logout() {
        unset($_SESSION['typeUser']);
        unset($_SESSION['userId']);
        unset($_SESSION['userPwd']);
        unset($_SESSION['userLogin']);
        unset($_SESSION['userEmail']);
        foreach ($_SESSION as $key => $value) {
            unset($_SESSION[$key]);
        }
        $GLOBALS['msg'] = "Vous êtes deconnecté";
        $_SESSION['msg'] = $GLOBALS['msg'];
        header('Location: index.php?mnu=home');
        exit();
    }
    private static function createAccount() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = DBCore::getInstance();
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $confirm = $_POST['confirm_password'];
        
            // =========================
            // VALIDATION
            // =========================
            if (empty($username) || empty($email) || empty($password)) {
                $GLOBALS['msg'] = "All fields are required";
            }
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $GLOBALS['msg'] = "Invalid email format";
            }
            elseif ($password !== $confirm) {
                $GLOBALS['msg'] = "Passwords do not match";
            }
            else {
        
                // Check if user already exists
                $existing = $db->selectOne(
                    "SELECT id FROM users_publisher WHERE email = ? OR username = ?",
                    [$email, $username]
                );
        
                if ($existing) {
                    $GLOBALS['msg'] = "Username or email already exists";
                } else {
        
                    // Insert user (inactive by default)
                    $db->insert("users_publisher", [
                        "username" => $username,
                        "email" => $email,
                        "password" => password_hash($password, PASSWORD_DEFAULT),
                        "active" => 0
                    ]);
        
                    $GLOBALS['msg'] = "Account created successfully! Wait for admin validation.";
                    $_SESSION['msg'] = $GLOBALS['msg'];
                    header('Location: index.php?mnu=home');
                    exit();
                }
            }
        }
    }

}