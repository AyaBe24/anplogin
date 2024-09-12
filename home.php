<?php

include 'config.php';
session_start();
$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
};

if(isset($_GET['logout'])){
   unset($user_id);
   session_destroy();
   header('location:login.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>home</title>

  
    <link rel="stylesheet" href="style.css">
    <style>
      .container {
    background-color: var(--white);
    padding: 20px;
    box-shadow: var(--box-shadow);
    border-radius: var(--border-radius);
    width: 320px;
    text-align: center;
}

.container .profile {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.container .profile img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    margin-bottom: 15px;
    object-fit: cover;
    border: 2px solid var(--primary-color);
}

.container .profile h3 {
    font-size: 22px;
    color: var(--dark-color);
    margin-bottom: 15px;
    font-weight: 500;
}

.container .profile .btn,
.container .profile .delete-btn {
    display: inline-block;
    padding: 12px 20px;
    border-radius: var(--border-radius);
    text-decoration: none;
    font-size: 16px;
    margin: 10px 0;
    box-shadow: var(--box-shadow);
}

.container .profile .btn {
    background-color: var(--primary-color);
    color: var(--white);
}

.container .profile .btn:hover {
    background-color: #2980b9;
}

.container .profile .delete-btn {
    background-color: #e74c3c;
    color: var(--white);
}

.container .profile .delete-btn:hover {
    color: #c0392b; /* Darker text color on hover */
}

.container .profile p {
    margin-top: 10px;
    font-size: 14px;
    color: var(--dark-color);
}

.container .profile p a {
    color: var(--primary-color);
}

.container .profile p a:hover {
    text-decoration: underline;
}
.company-logo {
             max-width: 100px;
             margin-bottom: 20px;
}
</style>

</head>
<body>
   
<div class="container">
<img src="https://vectorseek.com/wp-content/uploads/2023/09/Anp-Maroc-Logo-Vector.svg-.png" alt="Company Logo" class="company-logo">
   <div class="profile">
      <?php
         $select = mysqli_query($conn, "SELECT * FROM `user_form` WHERE id = '$user_id'") or die('query failed');
         if(mysqli_num_rows($select) > 0){
            $fetch = mysqli_fetch_assoc($select);
         }
         if($fetch['image'] == ''){
            echo '<img src="images/default-avatar.png">';
         }else{
            echo '<img src="uploaded_img/'.$fetch['image'].'">';
         }
      ?>
      <h3><?php echo $fetch['last_name'] . ' ' . $fetch['first_name']; ?></h3>
      <a href="updateprofile.php" class="btn">update profile</a>
      <a href="home.php?logout=<?php echo $user_id; ?>" class="delete-btn">logout</a>
      <p>new <a href="login.php">login</a> or <a href="register.php">register</a></p>
   </div>

</div>

</body>
</html>