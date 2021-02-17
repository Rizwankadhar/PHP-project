<?php
  session_start();
  $errors = [];

  function matchDate($date){
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 0){
      return 'Date format is invalid!';
    }else {
      $dates = explode('-', $date);
      if (!checkdate($dates[1], $dates[2], $dates[0])){
          return "Date is invalid!";
      }
    }
    return '';
  }

  function changeDelimiter($date){
    $dates =  explode('-', $date);
    return implode('/', $dates);
  }

  if ($_POST){
    $date = $_POST['date'];
    $slots = $_POST['slots'];
    if (!isset($date)){
      $errors[] = 'New Date is required!';
    }else if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 0){
      $errors[] = 'Date format is invalid!';
    }else {
      $dates = explode('-', $date);
      if (!checkdate($dates[1], $dates[2], $dates[0])){
          $errors[] = "Date is invalid!";
      }
    }

    if (empty($slots)){
      $errors[] = 'Slots are required!';
    }else if (!is_numeric($slots)){
      $errors[] = 'Slots should be numeric!';
    }else if ($slots <= 0){
      $errors[] = 'There should be atleast 1 slot!';
    }
    if (empty($errors)){
      $_SESSION['newDate'] = [changeDelimiter($date), $slots];
      header('Location: listPage.php');
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
    <h1>Add a new Date</h1>
    <p>Please fill in this form to add a new date.</p>
    <hr>
    <label><b>Date</b></label>
    <input type="date" name="date" value="<?= setValue('date',$errors)?>">
    <p style="color:red"><?= $_POST && !isset($date) ? 'Date is required!' : ''?></p>
    <p style="color:red"><?= $_POST && isset($date) ? matchDate($date) : ''?></p>
    

    <label><b>Slots</b></label>
    <input type="text" name="slots" value="<?= setValue('slots',$errors)?>">
    <p style="color:red"><?= $_POST && empty($slots) ? 'Slots are required!' : ''?></p>
    <p style="color:red"><?= $_POST && !empty($slots) ? !is_numeric($slots) ? 'Slots should be numeric!' : '': ''?></p>
    <p style="color:red"><?= $_POST && !empty($slots) && is_numeric($slots) ? $slots <= 0 ? 'There should be atleast 1 slot!' : '': ''?></p>

    <button type="submit" class="registerbtn">Register</button>
  </div>
