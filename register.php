<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tastybites";

// Connect to DB
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize inputs
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // Basic validations
    if (!$user) $errors[] = "Username is required.";
    if (!$pass) $errors[] = "Password is required.";
    if (!$name) $errors[] = "Name is required.";
    if (!$address) $errors[] = "Address is required.";
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (!$phone) $errors[] = "Phone number is required.";

    if (empty($errors)) {
        // Check if username exists
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $user);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = "Username already taken.";
        } else {
            // Hash password
            $pass_hash = password_hash($pass, PASSWORD_DEFAULT);

            // Insert new user
            $stmt_insert = mysqli_prepare($conn, "INSERT INTO users (username, password, name, address, email, phone) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_insert, "ssssss", $user, $pass_hash, $name, $address, $email, $phone);

            if (mysqli_stmt_execute($stmt_insert)) {
                $success = "Registration successful! You can now <a href='auth.php'>login</a>.";
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
            mysqli_stmt_close($stmt_insert);
        }
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Register - TastyBites</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 400px; margin: auto; }
        input, textarea { width: 100%; padding: 8px; margin: 6px 0; box-sizing: border-box; }
        .error { color: red; }
        .success { color: green; }
        button { padding: 10px 20px; background: #4CAF50; color: white; border: none; cursor: pointer; }
        button:hover { background: #45a049; }
        a { color: blue; text-decoration: none; }
    </style>
</head>
<body>
    <h2>Register</h2>

    <?php if ($errors): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="success"><?php echo $success; ?></p>
    <?php else: ?>
    <form method="POST" action="">
        <label>Username:</label>
        <input type="text" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? '') ?>" />

        <label>Password:</label>
        <input type="password" name="password" required />

        <label>Full Name:</label>
        <input type="text" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? '') ?>" />

        <label>Address:</label>
        <textarea name="address" required><?php echo htmlspecialchars($_POST['address'] ?? '') ?></textarea>

        <label>Email:</label>
        <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? '') ?>" />

        <label>Phone:</label>
        <input type="text" name="phone" required value="<?php echo htmlspecialchars($_POST['phone'] ?? '') ?>" />

        <button type="submit">Register</button>
    </form>
    <?php endif; ?>

    <p>Already have an account? <a href="auth.php">Login here</a>.</p>
</body>
</html>
