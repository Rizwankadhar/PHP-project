<?php
    session_start();
    function get_contents($filename){
        $raw_contents = file_get_contents($filename);
        return json_decode($raw_contents, TRUE);
    }

    function put_contents($filename, $contents){
        file_put_contents($filename, json_encode($contents));
    }

    function add_contents($filename, $new_content){
        $contents = get_contents($filename);
        $contents[] = $new_content;
        put_contents($filename, $contents);
    }

    function filter_contents($filename, $predicate){
        $contents = get_contents($filename);
        return array_filter($contents, $predicate);
    }

    $registered_users = get_contents('registered.json') ? get_contents('registered.json') : [] ;
    $errors = [];

    function requiredCheck($val){
        return empty($val);
    }
    function repeatCheck($registered_users, $email){
        $flag = false;
        foreach ($registered_users as $user){
            if ($user['email'] === $email){
                $flag = true;
                break;
            }
        }
        return $flag;
    }

    if ($_POST){
        $name = $_POST['fullname'];
        $ssn = $_POST['ssn'];
        $address = $_POST['address'];
        $email = $_POST['email'];
        $pswd = $_POST['pswd'];
        $rpt_pswd = $_POST['repeat-psw'];
        if (requiredCheck($name)){
            $errors[] = 'Name is required!';
        }
        if (requiredCheck($ssn)){
            $errors[] = 'SSN is required!';
        }else if (!is_numeric($ssn)){
            $errors[] = 'SSN should be numeric!';
        }else if (strlen($ssn) !== 9){
            $errors[] = 'SSN should be a 9 digit number!';
        }
        if (requiredCheck($address)){
            $errors[] = 'Address is required!';
        }
        if (requiredCheck($email)){
            $errors[] = 'Email is required!';
        }else if (!stristr($email, '')){
            $errors[] = 'Email not formatted well!';
        }
        if (requiredCheck($pswd)){
            $errors[] = 'Password is required!';
        }
        if (requiredCheck($rpt_pswd)){
            $errors[] = 'Password Confirmation is required!';
        }else if ($rpt_pswd !== $pswd){
            $errors[] = 'Password Confirmation should match the Password!';
        }else if (repeatCheck($registered_users, $email)){
            $errors[] = 'Email already in use!';
        }
        if (empty($errors)){
            $registered_users[] = [
                'name' => $name,
                'address' => $address,
                'ssn' => $ssn,
                'email' => $email,
                'password' => $pswd
            ];
            put_contents('registered.json', $registered_users);
            header("Location:login.php");
           exit;
        }
    }
    function setValue($val, $errors) {
        return $_POST && !empty($errors) ? $_POST[$val] : '';
    }

?>

<link rel="stylesheet" href="default.css">
<form method="POST" novalidate>
  <div class="container">
    <h1>Register</h1>
    <p>Please fill in this form to create an account.</p>
    <hr>
    <label><b>Full Name</b></label>
    <input type="text" name="fullname" value="<?= setValue('fullname',$errors)?>">
    <p style="color:red"><?= $_POST ? requiredCheck($name)? 'Name is required!' : '' : ''?></p>
    

    <label><b>SSN Number</b></label>
    <input type="text" name="ssn" value="<?= setValue('ssn',$errors)?>">
    <p style="color:red"><?= $_POST ? requiredCheck($ssn)? 'SSN is required!' : '' : ''?></p>
    <p style="color:red"><?= $_POST && !requiredCheck($ssn) ? !is_numeric($ssn) ? 'SSN should be numeric!' : '': ''?></p>
    <p style="color:red"><?= $_POST && !requiredCheck($ssn) && is_numeric($ssn) ? strlen($ssn) !=9 ? 'SSN should be a 9 digit number!' : '': ''?></p>

    <label><b>Address</b></label>
    <input type="text" name="address" value="<?= setValue('address',$errors)?>">
    <p style="color:red"><?= $_POST ? requiredCheck($address)? 'Address is required!' : '' : ''?></p>

    <label><b>Email</b></label>
    <input type="text" name="email" value="<?= setValue('email',$errors)?>">
    <p style="color:red"><?= $_POST ? requiredCheck($email)? 'Email is required!' : '' : ''?></p>
    <p style="color:red"><?= $_POST && !requiredCheck($email)? !stristr($email, '@') ? 'Email not well formatted!': '' : ''?></p>
    <p style="color:red"><?= $_POST && !requiredCheck($email) && stristr($email, '@') ? repeatCheck($registered_users,$email) ? 'Email already in use!': '' : ''?></p>

    <label><b>Password</b></label>
    <input type="password" name="pswd" value="<?= setValue('pswd',$errors)?>">
    <p style="color:red"><?= $_POST ? requiredCheck($pswd)? 'Password is required!' : '' : ''?></p>

    <label><b>Repeat Password</b></label>
    <input type="password" name="repeat-psw" value="<?= setValue('repeat-psw', $errors)?>">
    <p style="color:red"><?= $_POST ? requiredCheck($rpt_pswd)? 'Password Confirmation is required!' : '' : ''?></p>
    <p style="color:red"><?= $_POST && !requiredCheck($rpt_pswd) ? $pswd != $rpt_pswd ? 'Password Confirmation should match the Password!': '' : ''?></p>
    <hr>

    <button type="submit" class="registerbtn">Register</button>
  </div>

  <div class="container signin">
    <p>Already have an account? <a href="login.php">Login</a>.</p>
  </div>
</form> 