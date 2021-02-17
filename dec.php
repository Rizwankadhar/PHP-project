<?php
    session_start();
    $newDates = get_contents('newDates.json');
    $appointments = get_contents('bookings.json') ? get_contents('bookings.json') : [] ;
    if (isset($_SESSION['date']) && empty(checkAppoint($appointments)) && !isAdmin()){
        echo 'You have already selected a date and time, so you are being redirected to the booking page';
        header('Refresh: 1; url=booking.php?date=' . $_SESSION['date'] . '&time=' . $_SESSION['time']);
        exit;
    }

    function checkAppoint($appointments){
        $alreadyBooked = [];
        if (isset($_SESSION['loggeduser'])){
            foreach ($appointments as $appoint){
                $appointdate = key($appoint);
                foreach ($appoint as $time){
                    if ($time[key($time)] === $_SESSION['loggeduser']['email']){
                        $alreadyBooked = [$appointdate, key($time)];
                    }
                }
            }
        }
        return $alreadyBooked;
    }

    function countAppoints($appointments, $date){
        $count = 0;
        foreach ($appointments as $appoint){
            $appointdate = key($appoint);
            if ($appointdate === $date){
                $count += 1;
            }
        }
        return $count;
    }

    function checkAppointTime($appointments, $date, $time){
        $flag = false;
        foreach ($appointments as $appoint){
            $appointdate = key($appoint);
            if ($appointdate === $date){
                foreach($appoint as $aptime){
                    $appointTime = key($aptime);
                    if ($appointTime === $time){
                        $flag = true;
                    }
                }
            }
        }
        return $flag;
    }

    function isAdmin(){
        return !empty($_SESSION['loggeduser']['email']) && ($_SESSION['loggeduser']['email'] === 'admin@nemkovid.hu' && $_SESSION['loggeduser']['password']);
    }

    function setColor($slots, $limit){
        return $slots < $limit ? 'background-color:green;' : 'background-color:red;';
    }

    function setNewDateColor($slots, $newDate, $date){
        if (!empty($newDate)){
            if ($date === $newDate[0]){
                return setColor($slots, $newDate[1]);
            }
        }else {
            return '';
        }
    }

    function get_contents($filename){
        $raw_contents = file_get_contents($filename);
        return json_decode($raw_contents, TRUE);
    }

    function put_contents($filename, $contents){
        file_put_contents($filename, json_encode($contents));
    }

    function prefixI($i){
        return $i <= 9 ? '0'. $i : $i;
    }

    $newDate = [];
    if (isset($_SESSION['newDate'])){
        $newDate = $_SESSION['newDate'];
        unset($_SESSION['newDate']);
        $newDates[] = $newDate;
        put_contents('newDates.json', $newDates);
    }

    function isNewDate($newDates, $prospectDate){
        foreach ($newDates as $date){
            if ($date[0] === $prospectDate){
                return $date;
            }
        }
        return [];
    }
    
    if ($_GET){
        $date = $_GET['date'];
        $time = $_GET['time'];
        $new_appointments = [];
        foreach ($appointments as $appoint){
            if (key($appoint) === $date){
                foreach ($appoint as $aptime){
                    if (key($aptime) === $time){
                        $new_appointments[] = [];
                    }else {
                        $new_appointments[] = [
                            key($appoint) => $aptime
                        ];
                    }
                }
            }else {
                $new_appointments[] = $appoint;
            }

        }
        put_contents('bookings.json', $new_appointments);
        header('Location: listPage.php');
        exit;
    }
    $weekDays = ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'];
?>


<!DOCTYPE html>
<html>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <head>
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/3/w3.css">
    </head>
    <style>
        table, td, th {
            width: 580px;
            height: 50px;
            border: 1px black solid;
            border-collapse: collapse;
        }
        td { text-align: center; }
        .center{
            margin-left: auto;
            margin-right: auto;
        }
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
    <body>
        <nav class="navbar">
            <a href="login.php">Login</a>
            <a href="registration.php">Registration</a>
            <?= isset($_SESSION['loggeduser']) ? '<a href=login.php?out=true style=float:right>LogOut</a>' : ''?>
            <h2 style="color:blue"><?= !empty(checkAppoint($appointments)) ? 'You already have an appointment on ' . checkAppoint($appointments)[0] . ' at '. checkAppoint($appointments)[1] : ''?></h2>
        </nav> 
        <h1>Welcome to the National Health Center's Covid Vaccinantion Page</h1>
        <p>Here you would be able to find the available dates and time slots for the CoronaVirus Vaccinantion. You can also book an appointment
            on an available day and time slot. But you need to register yourself first. For registration, you need to have a SSN(Social Security Number)
            and a valid email address</p>
        <h2 style="text-align: center">December 2020</h2>
        <table class="center">
            <tr>
                <?php foreach ($weekDays as $day):?>
                    <th style="background-color:blue;"><?= $day?></th>
                <?php endforeach?>
            </tr>
            <tr>
                <?php for ($i=1; $i<=7;$i++):?>
                    <td style="<?= ($i % 2 === 0) ? setColor(countAppoints($appointments, '2020/12/0' . $i), 4) : setNewDateColor(countAppoints($appointments, '2020/12/0' . $i), isNewDate($newDates, '2020/12/0' . $i), ('2020/12/0' . $i)) ?>">
                        <?= $i?><br>
                        <?php if ($i % 2 === 0): ?>
                            <a href="listPage.php?date=<?= !empty(checkAppoint($appointments)) ? checkAppoint($appointments)[0] : '' ?>&time=<?=!empty(checkAppoint($appointments)) ? checkAppoint($appointments)[1] : ''  ?>"><?= !empty(checkAppoint($appointments)) && '2020/12/0' . $i === checkAppoint($appointments)[0] ? 'Cancel this appointment' : '' ?></a>
                            <?= countAppoints($appointments, '2020/12/0' . $i) < 4 ? '<div>' : '<div hidden>' ?>
                                <a href="booking.php?date=2020/12/0<?=$i?>& time=10:00"><?= !empty(checkAppoint($appointments)) ? '' : (checkAppointTime($appointments, '2020/12/0' . $i, '10:00') ?  '' : '10:00') ?></a>
                                <a href="booking.php?date=2020/12/0<?=$i?>& time=10:30"><?= !empty(checkAppoint($appointments)) ? '' : (checkAppointTime($appointments, '2020/12/0' . $i, '10:30') ?  '' : '10:30') ?></a>
                                <a href="booking.php?date=2020/12/0<?=$i?>& time=14:00"><?= !empty(checkAppoint($appointments)) ? '' : (checkAppointTime($appointments, '2020/12/0' . $i, '14:00') ?  '' : '14:00') ?></a>
                                <a href="booking.php?date=2020/12/0<?=$i?>& time=14:30"><?= !empty(checkAppoint($appointments)) ? '' : (checkAppointTime($appointments, '2020/12/0' . $i, '14:30') ?  '' : '14:30') ?></a>
                            </div>
                            <?= (isAdmin() && countAppoints($appointments, '2020/12/0' . $i) >= 4) ? "<a href=booking.php?date=2020/12/0" . $i . "&time=14:30". '> Check appointments </a>' : '<a hidden></a>'  ?>
                            <p>Slots : <?= countAppoints($appointments, '2020/12/0' . $i) . '/4' ?></p>
                        <?php elseif (!empty($newDates) && !empty(isNewDate($newDates, '2020/12/0' . $i))): ?>
                            <?php $newAddedDate = isNewDate($newDates, '2020/12/0' . $i) ?>
                            <a href="listPage.php?date=<?= !empty(checkAppoint($appointments)) ? checkAppoint($appointments)[0] : '' ?>&time=<?=!empty(checkAppoint($appointments)) ? checkAppoint($appointments)[1] : ''  ?>"><?= !empty(checkAppoint($appointments)) && '2020/12/0' . $i === checkAppoint($appointments)[0] ? 'Cancel this appointment' : '' ?></a>
                            <?= countAppoints($appointments, '2020/12/0' . $i) < $newAddedDate[1] ? '<div>' : '<div hidden>' ?>
                                <?php $var = ['10', '00'] ?>
                                <?php for ($j = 1; $j<= $newAddedDate[1]; $j++): ?>
                                    
                                    <a href="booking.php?date=2020/12/0<?=$i?>& time=<?= $var[0] . ':' . $var[1] ?>"><?= !empty(checkAppoint($appointments)) ? '' : (checkAppointTime($appointments, '2020/12/0' . $i, $var[0] . ':' . $var[1]) ? '' : $var[0] . ':' . $var[1]) ?></a>
                                    <?php $var = [$var[1] === '00' ? $var[0] : (string)(intval($var[0]) + 4) , $var[1] === '00' ? '30' : '00' ] ?>
                                <?php endfor ?>
                            </div>
                            <?= (isAdmin() && countAppoints($appointments, '2020/12/0' . $i) >= $newAddedDate[1]) ? "<a href=booking.php?date=2020/12/0" . $i . "&time=" . $var[0] . ":" . $var[1] . '> Check appointments </a>' : '<a hidden></a>'  ?>
                            <p>Slots : <?= countAppoints($appointments, '2020/12/0' . $i) . '/' . $newAddedDate[1] ?></p>
                        <?php endif?>
                    </td>
                <?php endfor?>
            </tr>
            <tr>
                <?php for ($i=8; $i<=14;$i++):?>
                    <td style="<?= ($i % 2 !== 0) ? setColor(countAppoints($appointments, '2020/12/' . prefixI($i)), 4) : setNewDateColor(countAppoints($appointments, '2020/12/' . prefixI($i)), isNewDate($newDates, '2020/12/' . prefixI($i)), ('2020/12/' . prefixI($i))) ?>">
                        <?= $i?><br>
                        <?php if ($i % 2 !== 0): ?>
                            <a href="listPage.php?date=<?= !empty(checkAppoint($appointments)) ? checkAppoint($appointments)[0] : '' ?>&time=<?=!empty(checkAppoint($appointments)) ? checkAppoint($appointments)[1] : ''  ?>"><?= !empty(checkAppoint($appointments)) && '2020/12/' . prefixI($i) === checkAppoint($appointments)[0] ? 'Cancel this appointment' : '' ?></a>
                            <?= countAppoints($appointments, '2020/12/' . prefixI($i)) < 4 ? '<div>' : '<div hidden>' ?>
                                <a href="booking.php?date=2020/12/<?=prefixI($i)?>& time=10:00"><?= !empty(checkAppoint($appointments)) ? '' : (checkAppointTime($appointments, '2020/12/0' . prefixI($i), '10:00') ?  '' : '10:00') ?></a>
                                <a href="booking.php?date=2020/12/<?=prefixI($i)?>& time=10:30"><?= !empty(checkAppoint($appointments)) ? '' : (checkAppointTime($appointments, '2020/12/0' . prefixI($i), '10:30') ?  '' : '10:30') ?></a>
                                <a href="booking.php?date=2020/12/<?=prefixI($i)?>& time=14:00"><?= !empty(checkAppoint($appointments)) ? '' : (checkAppointTime($appointments, '2020/12/0' . prefixI($i), '14:00') ?  '' : '14:00') ?></a>
                                <a href="booking.php?date=2020/12/<?=prefixI($i)?>& time=14:30"><?= !empty(checkAppoint($appointments)) ? '' : (checkAppointTime($appointments, '2020/12/0' . prefixI($i), '14:30') ?  '' : '14:30') ?></a>
                            </div>
                            <?= (isAdmin() && countAppoints($appointments, '2020/12/' . prefixI($i)) >= 4) ? "<a href=booking.php?date=2020/12/" . prefixI($i) . "&time=14:30". '> Check appointments </a>' : '<a hidden></a>'  ?>
                            <p>Slots : <?= countAppoints($appointments, '2020/12/' . prefixI($i)) . '/4' ?></p>
                        <?php elseif (!empty($newDates) && !empty(isNewDate($newDates, '2020/12/' . prefixI($i)))): ?>
                            <?php $newAddedDate = isNewDate($newDates, '2020/12/' . prefixI($i)) ?>
                            <a href="listPage.php?date=<?= !empty(checkAppoint($appointments)) ? checkAppoint($appointments)[0] : '' ?>&time=<?=!empty(checkAppoint($appointments)) ? checkAppoint($appointments)[1] : ''  ?>"><?= !empty(checkAppoint($appointments)) && '2020/12/' . prefixI($i) === checkAppoint($appointments)[0] ? 'Cancel this appointment' : '' ?></a>
                            <?= countAppoints($appointments, '2020/12/' . prefixI($i)) < $newAddedDate[1] ? '<div>' : '<div hidden>' ?>
                                <?php $var = ['10', '00'] ?>
                                <?php for ($j = 1; $j<= $newAddedDate[1]; $j++): ?>
                                    
                                    <a href="booking.php?date=2020/12/<?=prefixI($i)?>& time=<?= $var[0] . ':' . $var[1] ?>"><?= !empty(checkAppoint($appointments)) ? '' : (checkAppointTime($appointments, '2020/12/' . prefixI($i), $var[0] . ':' . $var[1]) ? '' : $var[0] . ':' . $var[1])?></a>
                                    <?php $var = [$var[1] === '00' ? $var[0] : (string)(intval($var[0]) + 4) , $var[1] === '00' ? '30' : '00' ] ?>
                                <?php endfor ?>
                            </div>
                            <?= (isAdmin() && countAppoints($appointments, '2020/12/' . prefixI($i)) >= $newAddedDate[1]) ? "<a href=booking.php?date=2020/12/" . prefixI($i) . "&time=" . $var[0] . ":" . $var[1] . '> Check appointments </a>' : '<a hidden></a>'  ?>
                            <p>Slots : <?= countAppoints($appointments, '2020/12/' . prefixI($i)) . '/' . $newAddedDate[1] ?></p>
                        <?php endif?>
                    </td>
                <?php endfor?>
            </tr>
            <tr>
                <?php for ($i=15; $i<=21;$i++):?>
                    <td style="<?= ($i % 2 === 0) ? setColor(countAppoints($appointments, '2020/12/' . $i), 4) : setNewDateColor(countAppoints($appointments, '2020/12/' . $i), isNewDate($newDates, '2020/12/' . $i), ('2020/12/' . $i)) ?>">
                        <?= $i?><br>
                        <?php if ($i % 2 === 0): ?>
                            <a href="listPage.php?date=<?= !empty(checkAppoint($appointments)) ? checkAppoint($appointments)[0] : '' ?>&time=<?=!empty(checkAppoint($appointments)) ? checkAppoint($appointments)[1] : ''  ?>"><?= !empty(checkAppoint($appointments)) && '2020/12/' . $i === checkAppoint($appointments)[0] ? 'Cancel this appointment' : '' ?></a>
                            <?= countAppoints($appointments, '2020/12/' . $i) < 4 ? '<div>' : '<div hidden>' ?>
                                <a href="booking.php?date=2020/12/<?=$i?>& time=10:00"><?= !empty(checkAppoint($appointments)) ? '' : (checkAppointTime($appointments, '2020/12/' . $i, '10:00') ?  '' : '10:00') ?></a>
                                <a href="booking.php?date=2020/12/<?=$i?>& time=10:30"><?= !empty(checkAppoint($appointments)) ? '' : (checkAppointTime($appointments, '2020/12/' . $i, '10:30') ?  '' : '10:30') ?></a>
                                <a href="booking.php?date=2020/12/<?=$i?>& time=14:00"><?= !empty(checkAppoint($appointments)) ? '' : (checkAppointTime($appointments, '2020/12/' . $i, '14:00') ?  '' : '14:00') ?></a>
                                <a href="booking.php?date=2020/12/<?=$i?>& time=14:30"><?= !empty(checkAppoint($appointments)) ? '' : (checkAppointTime($appointments, '2020/12/' . $i, '14:30') ?  '' : '14:30') ?></a>
                            </div>
                            <?= (isAdmin() && countAppoints($appointments, '2020/12/' . $i) >= 4) ? "<a href=booking.php?date=2020/12/" . $i . "&time=14:30". '> Check appointments </a>' : '<a hidden></a>'  ?>
                            <p>Slots : <?= countAppoints($appointments, '2020/12/' . $i) . '/4' ?></p>
                        <?php elseif (!empty($newDates) && !empty(isNewDate($newDates, '2020/12/' . $i))): ?>
                            <?php $newAddedDate = isNewDate($newDates, '2020/12/' . $i) ?>
                            <a href="listPage.php?date=<?= !empty(checkAppoint($appointments)) ? checkAppoint($appointments)[0] : '' ?>&time=<?=!empty(checkAppoint($appointments)) ? checkAppoint($appointments)[1] : ''  ?>"><?= !empty(checkAppoint($appointments)) && '2020/12/' . $i === checkAppoint($appointments)[0] ? 'Cancel this appointment' : '' ?></a>
                            <?= countAppoints($appointments, '2020/12/' . $i) < $newAddedDate[1] ? '<div>' : '<div hidden>' ?>
                                <?php $var = ['10', '00'] ?>
                                <?php for ($j = 1; $j<= $newAddedDate[1]; $j++): ?>
                                    
                                    <a href="booking.php?date=2020/12/<?=$i?>& time=<?= $var[0] . ':' . $var[1] ?>"><?= !empty(checkAppoint($appointments)) ? '' : (checkAppointTime($appointments, '2020/12/' . $i, $var[0] . ':' . $var[1]) ? '' : $var[0] . ':' . $var[1]) ?></a>
                                    <?php $var = [$var[1] === '00' ? $var[0] : (string)(intval($var[0]) + 4) , $var[1] === '00' ? '30' : '00' ] ?>
                                <?php endfor ?>
                            </div>
                            <?= (isAdmin() && countAppoints($appointments, '2020/12/' . $i) >= $newAddedDate[1]) ? "<a href=booking.php?date=2020/12/" . $i . "&time=" . $var[0] . ":" . $var[1] . '> Check appointments </a>' : '<a hidden></a>'  ?>
                            <p>Slots : <?= countAppoints($appointments, '2020/12/' . $i) . '/' . $newAddedDate[1] ?></p>
                        <?php endif?>
                    </td>
                <?php endfor?>
            </tr>
            <tr>
                <?php for ($i=22; $i<=28;$i++):?>
                    <td style="<?= ($i % 2 !== 0) ? setColor(countAppoints($appointments, '2020/12/' . $i), 4) : setNewDateColor(countAppoints($appointments, '2020/12/' . $i), isNewDate($newDates, '2020/12/' . $i), ('2020/12/' . $i)) ?>">
                        <?= $i?><br>
                        <?php if ($i % 2 !== 0): ?>
                            <a href="listPage.php?date=<?= !empty(checkAppoint($appointments)) ? checkAppoint($appointments)[0] : '' ?>&time=<?=!empty(checkAppoint($appointments)) ? checkAppoint($appointments)[1] : ''  ?>"><?= !empty(checkAppoint($appointments)) && '2020/12/' . $i === checkAppoint($appointments)[0] ? 'Cancel this appointment' : '' ?></a>
                            <?= countAppoints($appointments, '2020/12/' . $i) < 4 ? '<div>' : '<div hidden>' ?>
                                <a href="booking.php?date=2020/12/<?=$i?>& time=10:00"><?= !empty(checkAppoint($appointments)) ? '' : (checkAppointTime($appointments, '2020/12/' . $i, '10:00') ?  '' : '10:00') ?></a>
                                <a href="booking.php?date=2020/12/<?=$i?>& time=10:30"><?= !empty(checkAppoint($appointments)) ? '' : (checkAppointTime($appointments, '2020/12/' . $i, '10:30') ?  '' : '10:30') ?></a>
                                <a href="booking.php?date=2020/12/<?=$i?>& time=14:00"><?= !empty(checkAppoint($appointments)) ? '' : (checkAppointTime($appointments, '2020/12/' . $i, '14:00') ?  '' : '14:00') ?></a>
                                <a href="booking.php?date=2020/12/<?=$i?>& time=14:30"><?= !empty(checkAppoint($appointments)) ? '' : (checkAppointTime($appointments, '2020/12/' . $i, '14:30') ?  '' : '14:30') ?></a>
                            </div>
                            <?= (isAdmin() && countAppoints($appointments, '2020/12/' . $i) >= 4) ? "<a href=booking.php?date=2020/12/" . $i . "&time=14:30". '> Check appointments </a>' : '<a hidden></a>'  ?>
                            <p>Slots : <?= countAppoints($appointments, '2020/12/' . $i) . '/4' ?></p>
                        <?php elseif (!empty($newDates) && !empty(isNewDate($newDates, '2020/12/' . $i))): ?>
                            <?php $newAddedDate = isNewDate($newDates, '2020/12/' . $i) ?>
                            <a href="listPage.php?date=<?= !empty(checkAppoint($appointments)) ? checkAppoint($appointments)[0] : '' ?>&time=<?=!empty(checkAppoint($appointments)) ? checkAppoint($appointments)[1] : ''  ?>"><?= !empty(checkAppoint($appointments)) && '2020/12/' . $i === checkAppoint($appointments)[0] ? 'Cancel this appointment' : '' ?></a>
                            <?= countAppoints($appointments, '2020/12/' . $i) < $newAddedDate[1] ? '<div>' : '<div hidden>' ?>
                                <?php $var = ['10', '00'] ?>
                                <?php for ($j = 1; $j<= $newAddedDate[1]; $j++): ?>
                                    
                                    <a href="booking.php?date=2020/12/<?=$i?>& time=<?= $var[0] . ':' . $var[1] ?>"><?= !empty(checkAppoint($appointments)) ? '' : (checkAppointTime($appointments, '2020/12/' . $i, $var[0] . ':' . $var[1]) ? '' : $var[0] . ':' . $var[1]) ?></a>
                                    <?php $var = [$var[1] === '00' ? $var[0] : (string)(intval($var[0]) + 4) , $var[1] === '00' ? '30' : '00' ] ?>
                                <?php endfor ?>
                            </div>
                            <?= (isAdmin() && countAppoints($appointments, '2020/12/' . $i) >= $newAddedDate[1]) ? "<a href=booking.php?date=2020/12/" . $i . "&time=" . $var[0] . ":" . $var[1] . '> Check appointments </a>' : '<a hidden></a>'  ?>
                            <p>Slots : <?= countAppoints($appointments, '2020/12/' . $i) . '/' . $newAddedDate[1] ?></p>
                        <?php endif?>
                    </td>
                <?php endfor?>
            </tr>
            <tr>
                <?php for ($i=29; $i<=31;$i++):?>
                    <td style="<?= ($i % 2 === 0) ? setColor(countAppoints($appointments, '2020/12/' . $i), 4) : setNewDateColor(countAppoints($appointments, '2020/12/' . $i), isNewDate($newDates, '2020/12/' . $i), ('2020/12/' . $i)) ?>">
                        <?= $i?><br>
                        <?php if ($i % 2 === 0): ?>
                            <a href="listPage.php?date=<?= !empty(checkAppoint($appointments)) ? checkAppoint($appointments)[0] : '' ?>&time=<?=!empty(checkAppoint($appointments)) ? checkAppoint($appointments)[1] : ''  ?>"><?= !empty(checkAppoint($appointments)) && '2020/12/' . $i === checkAppoint($appointments)[0] ? 'Cancel this appointment' : '' ?></a>
                            <?= countAppoints($appointments, '2020/12/' . $i) < 4 ? '<div>' : '<div hidden>' ?>
                                <a href="booking.php?date=2020/12/<?=$i?>& time=10:00"><?= !empty(checkAppoint($appointments)) ? '' : (checkAppointTime($appointments, '2020/12/' . $i, '10:00') ?  '' : '10:00') ?></a>
                                <a href="booking.php?date=2020/12/<?=$i?>& time=10:30"><?= !empty(checkAppoint($appointments)) ? '' : (checkAppointTime($appointments, '2020/12/' . $i, '10:30') ?  '' : '10:30') ?></a>
                                <a href="booking.php?date=2020/12/<?=$i?>& time=14:00"><?= !empty(checkAppoint($appointments)) ? '' : (checkAppointTime($appointments, '2020/12/' . $i, '14:00') ?  '' : '14:00') ?></a>
                                <a href="booking.php?date=2020/12/<?=$i?>& time=14:30"><?= !empty(checkAppoint($appointments)) ? '' : (checkAppointTime($appointments, '2020/12/' . $i, '14:30') ?  '' : '14:30') ?></a>
                            </div>
                            <?= (isAdmin() && countAppoints($appointments, '2020/12/' . $i) >= 4) ? "<a href=booking.php?date=2020/12/" . $i . "&time=14:30". '> Check appointments </a>' : '<a hidden></a>'  ?>
                            <p>Slots : <?= countAppoints($appointments, '2020/12/' . $i) . '/4' ?></p>
                        <?php elseif (!empty($newDates) && !empty(isNewDate($newDates, '2020/12/' . $i))): ?>
                            <?php $newAddedDate = isNewDate($newDates, '2020/12/' . $i) ?>
                            <a href="listPage.php?date=<?= !empty(checkAppoint($appointments)) ? checkAppoint($appointments)[0] : '' ?>&time=<?=!empty(checkAppoint($appointments)) ? checkAppoint($appointments)[1] : ''  ?>"><?= !empty(checkAppoint($appointments)) && '2020/12/' . $i === checkAppoint($appointments)[0] ? 'Cancel this appointment' : '' ?></a>
                            <?= countAppoints($appointments, '2020/12/' . $i) < $newAddedDate[1] ? '<div>' : '<div hidden>' ?>
                                <?php $var = ['10', '00'] ?>
                                <?php for ($j = 1; $j<= $newAddedDate[1]; $j++): ?>
                                    
                                    <a href="booking.php?date=2020/12/<?=$i?>& time=<?= $var[0] . ':' . $var[1] ?>"><?= !empty(checkAppoint($appointments)) ? '' : (checkAppointTime($appointments, '2020/12/' . $i, $var[0] . ':' . $var[1]) ? '' : $var[0] . ':' . $var[1]) ?></a>
                                    <?php $var = [$var[1] === '00' ? $var[0] : (string)(intval($var[0]) + 4) , $var[1] === '00' ? '30' : '00' ] ?>
                                <?php endfor ?>
                            </div>
                            <?= (isAdmin() && countAppoints($appointments, '2020/12/' . $i) >= $newAddedDate[1]) ? "<a href=booking.php?date=2020/12/" . $i . "&time=" . $var[0] . ":" . $var[1] . '> Check appointments </a>' : '<a hidden></a>'  ?>
                            <p>Slots : <?= countAppoints($appointments, '2020/12/' . $i) . '/' . $newAddedDate[1] ?></p>
                        <?php endif?>
                    </td>
                <?php endfor?>
            </tr>
        </table><br>
        <nav class="navbar">
            <?= isAdmin() ? '<a href="addDate.php">Post a new date</a>' : '' ?>
            <a href="listPage.php">Next Month</a>
        </nav>
    </body>
</html>