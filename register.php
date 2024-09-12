<?php
include 'config.php';

$message = [
    'email' => '',
    'password' => '',
    'confirm_password' => '',
    'ice' => '',
    'cin' => '',
    'entity_type' => '',
    'image' => ''
];

if (isset($_POST['submit'])) {
    // Validate and sanitize inputs
    $fname = isset($_POST['first_name']) ? mysqli_real_escape_string($conn, trim($_POST['first_name'])) : '';
    $lname = isset($_POST['last_name']) ? mysqli_real_escape_string($conn, trim($_POST['last_name'])) : '';
    $email = isset($_POST['email']) ? mysqli_real_escape_string($conn, trim($_POST['email'])) : '';
    $pass = isset($_POST['password']) ? mysqli_real_escape_string($conn, trim($_POST['password'])) : '';
    $cpass = isset($_POST['cpassword']) ? mysqli_real_escape_string($conn, trim($_POST['cpassword'])) : '';
    $entityType = isset($_POST['entity_type']) ? mysqli_real_escape_string($conn, trim($_POST['entity_type'])) : '';

    $image = isset($_FILES['image']['name']) ? $_FILES['image']['name'] : '';
    $image_size = isset($_FILES['image']['size']) ? $_FILES['image']['size'] : 0;
    $image_tmp_name = isset($_FILES['image']['tmp_name']) ? $_FILES['image']['tmp_name'] : '';
    $image_folder = 'uploaded_img/' . $image;
    $ice = isset($_POST['ice']) ? mysqli_real_escape_string($conn, trim($_POST['ice'])) : '';
    $cin = isset($_POST['cin']) ? mysqli_real_escape_string($conn, trim($_POST['cin'])) : '';

    // Validate email format
    if (empty($email)) {
        $message['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message['email'] = 'Invalid email format!';
    } else {
        // Check if user already exists
        $select = mysqli_query($conn, "SELECT * FROM `user_form` WHERE email = '$email'");
        if (mysqli_num_rows($select) > 0) {
            $message['email'] = 'User already exists';
        }
    }

    // Password validation: at least 8 characters, including uppercase, lowercase, and special characters
    if (empty($pass)) {
        $message['password'] = 'Password is required.';
    } elseif (strlen($pass) < 8 || !preg_match('/[A-Z]/', $pass) || !preg_match('/[a-z]/', $pass) || !preg_match('/[\W_]/', $pass)) {
        $message['password'] = 'Invalid password';
    } elseif ($pass !== $cpass) {
        $message['confirm_password'] = 'Passwords do not match!';
    }

    // Ice ou cin valide
    if ($entityType === 'company') {
        if (empty($ice)) {
            $message['ice'] = 'ICE is required for companies.';
        } elseif (!preg_match('/^[A-Z0-9]{15}$/', $ice)) {
            $message['ice'] = 'Invalid ICE format. ICE must be exactly 15 alphanumeric characters.';
        }
        $cin = '';
    } elseif ($entityType === 'individual') {
        if (empty($cin)) {
            $message['cin'] = 'CIN is required for individuals.';
        } elseif(!preg_match('/^[A-Z0-9]{8}$/', $cin)) {
            $message['cin'] = 'Invalid CIN format. It must be 8 characters long and can include letters and digits.';
        }
        
        $ice = '';
    } elseif (empty($entityType)) {
        $message['entity_type'] = 'Please select an entity type.';
    }

    // Check if image size is acceptable
    if (empty($message['ice']) && empty($message['cin']) && empty($message['entity_type']) && empty($message['email']) && empty($message['password']) && empty($message['confirm_password'])) {
        if ($image_size > 2000000) {
            $message['image'] = 'Image size is too large! Maximum size is 2MB.';
        } else {
            // Hash the password before storing it
            $hashed_password = password_hash($pass, PASSWORD_BCRYPT);

            // Insert user data
            $insert = mysqli_query($conn, "INSERT INTO `user_form` (first_name, last_name, email, password, image, entity_type, ice, cin) VALUES ('$fname', '$lname', '$email', '$hashed_password', '$image', '$entityType', '$ice', '$cin')");

            if ($insert) {
                if ($image_tmp_name != '') {
                    move_uploaded_file($image_tmp_name, $image_folder);
                }
                header('Location: login.php');
                exit(); // Ensure no further code is executed after redirect
            } else {
                $message['email'] = 'Registration failed. Please try again.';
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
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .error-message {
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
        <h3>Register now</h3>
        <input type="text" name="first_name" placeholder="Enter first name" class="box" value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" required>
        <input type="text" name="last_name" placeholder="Enter last name" class="box" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" required>
        <input type="email" name="email" placeholder="Enter email" class="box" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
        <?php if (!empty($message['email'])) { echo '<div class="error-message">' . $message['email'] . '</div>'; } ?>
        <input type="password" name="password" placeholder="Enter password" class="box" required>
        <?php if (!empty($message['password'])) { echo '<div class="error-message">' . $message['password'] . '</div>'; } ?>
        <input type="password" name="cpassword" placeholder="Confirm password" class="box" required>
        <?php if (!empty($message['confirm_password'])) { echo '<div class="error-message">' . $message['confirm_password'] . '</div>'; } ?>
        <select name="entity_type" class="box" required>
            <option value="">Select entity type</option>
            <option value="individual" <?php echo isset($_POST['entity_type']) && $_POST['entity_type'] === 'individual' ? 'selected' : ''; ?>>Individual</option>
            <option value="company" <?php echo isset($_POST['entity_type']) && $_POST['entity_type'] === 'company' ? 'selected' : ''; ?>>Company</option>
        </select>
        <?php if (!empty($message['entity_type'])) { echo '<div class="error-message">' . $message['entity_type'] . '</div>'; } ?>
        <input type="text" name="ice" placeholder="Enter ICE" class="box" id="ice-field" style="display: none;" value="<?php echo isset($_POST['ice']) ? htmlspecialchars($_POST['ice']) : ''; ?>">
        <?php if (!empty($message['ice'])) { echo '<div class="error-message">' . $message['ice'] . '</div>'; } ?>
        <input type="text" name="cin" placeholder="Enter CIN" class="box" id="cin-field" style="display: none;" value="<?php echo isset($_POST['cin']) ? htmlspecialchars($_POST['cin']) : ''; ?>">
        <?php if (!empty($message['cin'])) { echo '<div class="error-message">' . $message['cin'] . '</div>'; } ?>
        <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png">
        <?php if (!empty($message['image'])) { echo '<div class="error-message">' . $message['image'] . '</div>'; } ?>
        <input type="submit" name="submit" value="Register now" class="btn">
        <p>Already have an account? <a href="login.php">Login now</a></p>
    </form>
</div>
<script>
    document.querySelector('select[name="entity_type"]').addEventListener('change', function() {
        var entityType = this.value;
        document.getElementById('ice-field').style.display = (entityType === 'company') ? 'block' : 'none';
        document.getElementById('cin-field').style.display = (entityType === 'individual') ? 'block' : 'none';
    });
</script>
</body>
</html>
