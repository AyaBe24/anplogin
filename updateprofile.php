<?php
include 'config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Initialize message arrays
$error_messages = [];
$success_messages = [];

// Handle profile update
if (isset($_POST['update_profile'])) {
    $update_fname = mysqli_real_escape_string($conn, $_POST['update_fname']);
    $update_lname = mysqli_real_escape_string($conn, $_POST['update_lname']);
    $update_email = mysqli_real_escape_string($conn, $_POST['update_email']);
    $update_ice = mysqli_real_escape_string($conn, $_POST['update_ice']);
    $update_cin = mysqli_real_escape_string($conn, $_POST['update_cin']);
    $update_entity_type = mysqli_real_escape_string($conn, $_POST['update_entity_type']);

    // Validate ICE format for companies
    if ($update_entity_type === 'company' && !preg_match('/^\d{15}$/', $update_ice)) {
        $error_messages[] = 'Invalid ICE format. It should be a 15-digit number.';
    }

    // Validate CIN format for individuals
    if ($update_entity_type === 'individual' && !preg_match('/^[A-Z0-9]{6,10}$/', $update_cin)) {
        $error_messages[] = 'Invalid CIN format. It should be between 6 to 10 alphanumeric characters.';
    }

    if (empty($error_messages)) {
        // Prepare the SQL query with entity type condition
        $update_query = "UPDATE `user_form` SET first_name = '$update_fname', last_name = '$update_lname', email = '$update_email', entity_type = '$update_entity_type'";
        if ($update_entity_type === 'company') {
            $update_query .= ", ice = '$update_ice', cin = ''"; // Clear CIN for companies
        } elseif ($update_entity_type === 'individual') {
            $update_query .= ", cin = '$update_cin', ice = ''"; // Clear ICE for individuals
        }
        $update_query .= " WHERE id = '$user_id'";

        mysqli_query($conn, $update_query) or die('Query failed: ' . mysqli_error($conn));
        $success_messages[] = 'Profile updated successfully!';
    }

    if (isset($_POST['update_pass'], $_POST['new_pass'], $_POST['confirm_pass'])) {
        $old_pass_input = $_POST['update_pass'];
        $new_pass = $_POST['new_pass'];
        $confirm_pass = $_POST['confirm_pass'];
    
        if (!empty($old_pass_input) && !empty($new_pass) && !empty($confirm_pass)) {
            // Fetch the current password from the database
            $result = mysqli_query($conn, "SELECT password FROM `user_form` WHERE id = '$user_id'") or die('Query failed: ' . mysqli_error($conn));
            $row = mysqli_fetch_assoc($result);
    
            if ($row) {
                $stored_pass = $row['password'];
    
                // Verify the old password
                if (!password_verify($old_pass_input, $stored_pass)) {
                    $error_messages[] = 'Old password does not match!';
                } elseif ($new_pass !== $confirm_pass) {
                    $error_messages[] = 'Confirm password does not match!';
                } else {
                    // Hash the new password before storing it
                    $hashed_new_pass = password_hash($new_pass, PASSWORD_BCRYPT);
                    mysqli_query($conn, "UPDATE `user_form` SET password = '$hashed_new_pass' WHERE id = '$user_id'") or die('Query failed: ' . mysqli_error($conn));
                    $success_messages[] = 'Password updated successfully!';
                }
            } else {
                $error_messages[] = 'User not found!';
            }
        } else {
            $error_messages[] = 'Please fill in all fields!';
        }
    }

    // Handle image upload
    $update_image = $_FILES['update_image']['name'];
    $update_image_size = $_FILES['update_image']['size'];
    $update_image_tmp_name = $_FILES['update_image']['tmp_name'];
    $update_image_folder = 'uploaded_img/' . $update_image;

    if (!empty($update_image)) {
        if ($update_image_size > 2000000) {
            $error_messages[] = 'Image is too large';
        } else {
            $image_update_query = mysqli_query($conn, "UPDATE `user_form` SET image = '$update_image' WHERE id = '$user_id'") or die('Query failed: ' . mysqli_error($conn));
            if ($image_update_query) {
                move_uploaded_file($update_image_tmp_name, $update_image_folder);
                $success_messages[] = 'Image updated successfully!';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --primary-color: #3498db;
            --dark-color: #2c3e50;
            --light-bg: #f4f4f4;
            --white: #ffffff;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
            --error-color: #e74c3c;
            --error-bg: #fce4e4;
            --success-color: #155724;
            --success-bg: #d4edda;
        }

        .update-profile {
            background-color: var(--white);
            padding: 20px;
            box-shadow: var(--box-shadow);
            border-radius: var(--border-radius);
            width: 450px;
            text-align: center;
        }

        .update-profile h3 {
            margin-bottom: 20px;
            font-size: 24px;
            color: var(--dark-color);
            font-weight: 500;
        }

        .update-profile .box {
            width: 100%;
            padding: 10px 15px;
            border-radius: var(--border-radius);
            font-size: 14px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            background-color: var(--light-bg);
        }

        .update-profile .box:focus {
            border-color: var(--primary-color);
        }

        .update-profile .btn {
            width: 100%;
            padding: 12px;
            border-radius: var(--border-radius);
            background-color: var(--primary-color);
            color: var(--white);
            cursor: pointer;
            font-size: 16px;
            border: none;
            margin-top: 20px;
            box-shadow: var(--box-shadow);
        }

        .update-profile .btn:hover {
            background-color: #2980b9;
        }

        .update-profile .delete-btn {
            margin-top: 10px;
        }

        .update-profile .delete-btn:hover {
            color: #c0392b;
        }

        .update-profile .flex {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .update-profile .inputBox {
            width: 48%;
        }

        .update-profile img {
            border-radius: 50%;
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin-bottom: 20px;
            box-shadow: var(--box-shadow);
        }

        .message {
            background-color: var(--error-bg);
            color: var(--error-color);
            padding: 10px;
            border-radius: var(--border-radius);
            margin-bottom: 15px;
            font-size: 14px;
        }

        .success-message {
            background-color: var(--success-bg);
            color: var(--success-color);
            padding: 10px;
            border-radius: var(--border-radius);
            margin-bottom: 15px;
            font-size: 14px;
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="update-profile">
        <?php
        $select = mysqli_query($conn, "SELECT * FROM `user_form` WHERE id = '$user_id'") or die('query failed');
        if (mysqli_num_rows($select) > 0) {
            $fetch = mysqli_fetch_assoc($select);
        } else {
            echo '<p class="error-message">User data could not be fetched. Please try again later.</p>';
            exit();
        }
        ?>

        <form action="" method="post" enctype="multipart/form-data">
            <?php
            if (empty($fetch['image'])) {
                echo '<img src="images/default-avatar.png">';
            } else {
                echo '<img src="uploaded_img/' . $fetch['image'] . '">';
            }

            // Display messages
            if (!empty($error_messages)) {
                foreach ($error_messages as $error_message) {
                    echo '<div class="message">' . htmlspecialchars($error_message) . '</div>';
                }
            }
            
            if (!empty($success_messages)) {
                foreach ($success_messages as $success_message) {
                    echo '<div class="success-message">' . htmlspecialchars($success_message) . '</div>';
                }
            }
            ?>
            
            <div class="flex">
                <div class="inputBox">
                    <span>First name:</span>
                    <input type="text" name="update_fname" value="<?php echo htmlspecialchars($fetch['first_name']); ?>" class="box">
                    <span>Last name:</span>
                    <input type="text" name="update_lname" value="<?php echo htmlspecialchars($fetch['last_name']); ?>" class="box">
                    <span>Your email:</span>
                    <input type="email" name="update_email" value="<?php echo htmlspecialchars($fetch['email']); ?>" class="box">
                    <span>Update your pic:</span>
                    <input type="file" name="update_image" accept="image/jpg, image/jpeg, image/png" class="box">
                </div>
                <div class="inputBox">
                    <span>Entity Type:</span>
                    <select name="update_entity_type" id="entity_type" class="box" onchange="toggleEntityFields()">
                        <option value="" disabled>Select entity type</option>
                        <option value="individual" <?php echo $fetch['entity_type'] == 'individual' ? 'selected' : ''; ?>>Individual</option>
                        <option value="company" <?php echo $fetch['entity_type'] == 'company' ? 'selected' : ''; ?>>Company</option>
                    </select>
                    <span id="ice-label" class="<?php echo $fetch['entity_type'] == 'company' ? '' : 'hidden'; ?>">ICE (for companies):</span>
                    <input type="text" name="update_ice" id="ice" value="<?php echo htmlspecialchars($fetch['ice']); ?>" class="box <?php echo $fetch['entity_type'] == 'company' ? '' : 'hidden'; ?>">
                    <span id="cin-label" class="<?php echo $fetch['entity_type'] == 'individual' ? '' : 'hidden'; ?>">CIN (for individuals):</span>
                    <input type="text" name="update_cin" id="cin" value="<?php echo htmlspecialchars($fetch['cin']); ?>" class="box <?php echo $fetch['entity_type'] == 'individual' ? '' : 'hidden'; ?>">
                    <input type="hidden" name="old_pass" value="<?php echo htmlspecialchars($fetch['password']); ?>">
                    <span>Old password:</span>
                    <input type="password" name="update_pass" placeholder="Enter previous password" class="box">
                    <span>New password:</span>
                    <input type="password" name="new_pass" placeholder="Enter new password" class="box">
                    <span>Confirm password:</span>
                    <input type="password" name="confirm_pass" placeholder="Confirm new password" class="box">
                </div>
            </div>
            <input type="submit" value="Update Profile" name="update_profile" class="btn">
            <a href="deleteacc.php" class="delete-btn">Delete Account</a>
            <a href="home.php" class="delete-btn">Go back</a>
        </form>
    </div>

    <script>
        function toggleEntityFields() {
            var entityType = document.getElementById('entity_type').value;
            var iceField = document.getElementById('ice');
            var iceLabel = document.getElementById('ice-label');
            var cinField = document.getElementById('cin');
            var cinLabel = document.getElementById('cin-label');
            
            if (entityType === 'company') {
                iceField.classList.remove('hidden');
                iceLabel.classList.remove('hidden');
                cinField.classList.add('hidden');
                cinLabel.classList.add('hidden');
            } else if (entityType === 'individual') {
                iceField.classList.add('hidden');
                iceLabel.classList.add('hidden');
                cinField.classList.remove('hidden');
                cinLabel.classList.remove('hidden');
            } else {
                iceField.classList.add('hidden');
                iceLabel.classList.add('hidden');
                cinField.classList.add('hidden');
                cinLabel.classList.add('hidden');
            }
        }

        // Initialize fields based on current selection
        window.onload = function() {
            toggleEntityFields();
        }
    </script>
</body>
</html>
