<?php
    session_start();
    function get_contents($filename){
        $raw_contents = file_get_contents($filename);
        return json_decode($raw_contents, TRUE);
    }

    function put_contents($filename, $contents){
        file_put_contents($filename, json_encode($contents));
    }

    if ($_GET){
        if ($_GET['out']){
            unset($_SESSION['loggeduser']);
        }
    }

    $errors = [];
    $mis_match = false;
    $registered_users = get_contents('registered.json') ? get_contents('registered.json') : [];
    function requiredCheck($val){
        return empty($val);
    }

    if ($_POST){
        $email = $_POST['email'];
        $pswd = $_POST['pswd'];

        if (requiredCheck($email)){
            $errors[] = 'Email is required!';
        }else if (!stristr($email, '@')){
            $errors[] = 'Email not formatted well!';
        }

        if (requiredCheck($pswd)){
            $errors[] = 'Password is required!';
        }

        if (empty($errors)){
            $flag = false;
            if ($email === 'admin@nemkovid.hu' && $pswd === 'admin'){
                $flag = true;
            }else {
                foreach ($registered_users as $user){
                    if ($user['email'] === $email && $user['password'] === $pswd){
                        $flag = true;
                    }
                }
            }
            
            if ($flag) {
                $_SESSION['loggeduser'] = [
                    'email' => $email,
                    'password' => $pswd
                ];
                header('Location:listPage.php');
                exit;
            }else {
                $mis_match = true;
                $errors[] = 'Incorrect Email or Password!';
            }
        }
    }
    function setValue($val, $errors) {
        return $_POST && !empty($errors) ? $_POST[$val] : '';
    }
?>

<link rel="stylesheet" href="default.css">
<form method="POST" novalidate>
  <div class="container">
    <h1>Login</h1>
    <p>Please give your login details</p>
    <hr>
    <?php if ($mis_match):?>
        <p style="color:red">Incorrect Email or Password!</p>
    <?php endif?>

    <label><b>Email</b></label>
    <input type="text" name="email" value="<?= setValue('email',$errors)?>">
    <p style="color:red"><?= $_POST ? requiredCheck($email)? 'Email is required!' : '' : ''?></p>
    <p style="color:red"><?= $_POST && !requiredCheck($email)? !stristr($email, '@') ? 'Email not formatted well!': '' : ''?></p>

    <label><b>Password</b></label>
    <input type="password" name="pswd" value="<?= setValue('pswd',$errors)?>">
    <p style="color:red"><?= $_POST ? requiredCheck($pswd)? 'Password is required!' : '' : ''?></p>


    <button type="submit" class="registerbtn">Login</button>
  </div>
  <div class="container signin">
    <p>Don't have an account? <a href="registration.php">Register</a>.</p>
  </div>
</form>  