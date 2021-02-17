<?php
    session_start();

    function get_contents($filename){
        $raw_contents = file_get_contents($filename);
        return json_decode($raw_contents, TRUE);
    }

    function put_contents($filename, $contents){
        file_put_contents($filename, json_encode($contents));
    }

    $bookings = get_contents('bookings.json') ? get_contents('bookings.json') : [];
    $errors = [];
    $registered_users = get_contents('registered.json') ? get_contents('registered.json') : []  ;
    if (empty($_SESSION['loggeduser'])){
        echo 'You are not logged in so you are being redirected to the login page';
        $_SESSION['date'] = $_GET['date'];
        $_SESSION['time'] = $_GET['time'];
        header("refresh: 1; url=login.php");
        exit;
    }

    function isAdmin(){
        return ($_SESSION['loggeduser']['email'] === 'admin@nemkovid.hu' && $_SESSION['loggeduser']['password']);
    }

    

    function checkAppointments($appointments, $registered_users){
        $dayBookings = [];
        foreach ($appointments as $appoint){
            if (key($appoint) === $_GET['date']){
                foreach ($appoint as $dayTime){
                    $data = [];
                    foreach ($registered_users as $user){
                        if ($user['email'] === $dayTime[key($dayTime)]){
                            $data = $user;
                        }
                    }
                    $dayBookings[] = $data; 
                }
            }
        }
        return $dayBookings;
    }

    $data = [];
    $date = '';
    $time = '';
    if ($_GET){
        $date = $_GET['date'];
        $time = $_GET['time'];
    }

    foreach ($registered_users as $user){
        if ($user['email'] === $_SESSION['loggeduser']['email']){
            $data = $user;
        }
    }
    if ($_POST){
        if (!isset($_POST['chkbx'])){
            $errors[] = "Terms and conditions must be accepted!";
        }

        $bookings[] = [
            $date => [
                $time => $_SESSION['loggeduser']['email']
            ]
        ];
        
        if (empty($errors)){
            put_contents('bookings.json', $bookings);
            $_SESSION['booking'] = [$date, $time];
            unset($_SESSION['date']);
            unserialize($_SESSION['time']);
            header('Location: successfulBooking.php?date=' . $date . '&time=' . $time);
            exit;
            
            //session_destroy();
        }
    }
    //session_destroy();
?>
<link rel="stylesheet" href="https://www.w3schools.com/w3css/3/w3.css">
<style>
    .navbar{
            color:#fff!important;
            background-color:#4CAF50;
            width:100%;
            overflow:hidden 
        }
        .navbar a {
            float: left;
            color: #f2f2f2;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
            font-size: 17px;
        }
        .navbar a:hover {
            background-color: #ddd;
            color: black;
        }
        .navbar h2{
            padding:8px 16px;
            float:left;
            width:auto;
            text-align: center;
        }
</style>
<nav class="navbar">
    <a href="listPage.php">ListPage  </a>
    <a href="login.php">Login</a>
    <a href="registration.php">Registration</a>
</nav>
Appointment Data:<br>
<p>Date:  <?= $date?></p>
<?= isAdmin() ? '<div hidden>' : '<div>' ?>
    <p>Time:  <?= $time?></p>
    <p>Name: <?= $data['name']?> </p>
    <p>Address:  <?= $data['address']?></p>
    <p>SSN:  <?= $data['ssn']?></p>
    Terms and Conditions:
    <ul>
        <li>It is mandatory to appear on the time selected for vaccination.</li>
        <li>There maybe side affects of the vaccine.</li>
        <li>In case of a delay of more than 30 minutes, booking is liable to be cancelled.</li>
    </ul>
    <p style="color:red"><?=$_POST && !isset($_POST['chkbx']) ? 'Terms and conditions must be accepted!' : '' ?></p>
    <form method="POST">
        <input type="checkbox" name="chkbx">By checking this box, you agree with the terms and conditions.<br><br>
        <button type="submit" name="submit">Confirm</button>
    </form>
</div>
<?= isAdmin() ? '<div>' : '<div hidden>' ?>
<p><?= empty(checkAppointments($bookings, $registered_users)) ? 'No one has booked an appointment for this day' : 'Following persons have appointments for this date:' ?></p>
    <ul>
        <?php foreach (checkAppointments($bookings, $registered_users) as $booking): ?>
            <li><?='Name: ' . $booking['name'] . ', SSN: ' . $booking['ssn'] . ', email:' . $booking['email']  ?></li>
        <?php endforeach?>
    </ul>
</div>