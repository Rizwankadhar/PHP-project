<?php
    session_start();

    $date = '';
    $time = '';

    if ($_GET){
        $date = $_GET['date'];
        $time = $_GET['time'];
    }
?>


<style>
    .navbar{
        color:#fff!important;
        background-color:#4CAF50;
        width:100%;
        overflow:hidden 
    }
    a {
        color: dodgerblue;
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
    <a href="listPage.php">ListPage</a>
    <a href="login.php">Login</a>
    <a href="registration.php">Registration</a>
</nav> 
<h1 style="color:red;">Congratulations! You have successfully booked an appointment.</h1>
<p>Your appointment details are:<p>
    <ul>
        <li>Date: <?= $date?></li>
        <li>Time: <?= $time?></li>
    </ul>
<p>Please make sure that you arrive National Health Center at least 15 minutes before you appointment.</p>