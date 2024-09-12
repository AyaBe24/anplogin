<?php
include 'config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Initialize message array
$message = [];

// Handle account deletion
if (isset($_POST['delete_account'])) {
    // Delete user data from the database
    $delete_query = mysqli_query($conn, "DELETE FROM `user_form` WHERE id = '$user_id'") or die('query failed');

    if ($delete_query) {
        // Logout the user and redirect to the login page
        session_unset();
        session_destroy();
        header('Location: login.php');
        exit();
    } else {
        $message[] = 'Failed to delete account. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .delete-account {
            background-color: #fff;
            padding: 30px;
            margin: 20px;
            width: 350px;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .delete-account h2 {
            font-size: 1.5em;
            margin-bottom: 20px;
            color: #333;
        }

        .delete-account .message {
            color: #e74c3c;
            margin-bottom: 20px;
            font-size: 0.9em;
        }

        .delete-account .btn {
            background-color: #e74c3c;
            color: #fff;
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }

        .delete-account .btn:hover {
            background-color: #c0392b;
        }

        .delete-account a {
            display: block;
            margin-top: 20px;
            color: #3498db;
            text-decoration: none;
            font-size: 0.9em;
            transition: color 0.3s ease;
        }

        .delete-account a:hover {
            color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="delete-account">
        <h2>Are you sure you want to delete your account?</h2>
        <?php
        if (!empty($message)) {
            foreach ($message as $msg) {
                echo '<div class="message">' . $msg . '</div>';
            }
        }
        ?>
        <form action="" method="post">
            <input type="submit" value="Delete Account" name="delete_account" class="btn">
        </form>
        <a href="updateprofile.php"><i class="fas fa-arrow-left"></i> Cancel</a>
    </div>
</body>
</html>

