<?php

include 'config.php';
session_start();

if(isset($_POST['submit'])){

   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $pass = mysqli_real_escape_string($conn, $_POST['password']);

   $select = mysqli_query($conn, "SELECT * FROM `user_form` WHERE email = '$email'") or die('query failed');

   if(mysqli_num_rows($select) > 0){
      $row = mysqli_fetch_assoc($select);
      // Verify the password with the hashed password from the database
      if(password_verify($pass, $row['password'])){
         $_SESSION['user_id'] = $row['id'];
         header('location:home.php');
      } else {
         $message[] = 'Incorrect email or password!';
      }
   } else {
      $message[] = 'Incorrect email or password!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login</title>

   <link rel="stylesheet" href="style.css">
   <style>
      .message {
            background-color: var(--error-bg);
            color: var(--error-color);
            padding: 10px;
            border-radius: var(--border-radius);
            margin-bottom: 15px;
            font-size: 14px;
         }
        .company-logo {
             max-width: 100px;
             margin-bottom: 20px;
        }
   </style>
</head>
<body>
   
<div class="form-container">
<img src="https://vectorseek.com/wp-content/uploads/2023/09/Anp-Maroc-Logo-Vector.svg-.png" alt="Company Logo" class="company-logo">
   <form action="" method="post" enctype="multipart/form-data">
      <h3>Login Now</h3>
      <?php
      if(isset($message)){
         foreach($message as $message){
            echo '<div class="message">'.$message.'</div>';
         }
      }
      ?>
      <input type="email" name="email" placeholder="Enter Email" class="box" required>
      <input type="password" name="password" placeholder="Enter Password" class="box" required>
      <input type="submit" name="submit" value="Login Now" class="btn">
      <p>Don't have an account? <a href="register.php">Register Now</a></p>
   </form>

</div>

</body>
</html>
