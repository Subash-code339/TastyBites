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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    if (!$user) $errors[] = "Username is required.";
    if (!$pass) $errors[] = "Password is required.";

    if (empty($errors)) {
        // Find user in DB
        $stmt = mysqli_prepare($conn, "SELECT id, password, name FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $user);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) === 1) {
            mysqli_stmt_bind_result($stmt, $id, $hashed_password, $name);
            mysqli_stmt_fetch($stmt);

            // Verify password
            if (password_verify($pass, $hashed_password)) {
                // Password correct, set session
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $user;
                $_SESSION['name'] = $name;

                // Redirect to menu
                header("Location: menu.php");
                exit;
            } else {
                $errors[] = "Incorrect password.";
            }
        } else {
            $errors[] = "Username not found.";
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
    <title>Login - TastyBites</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 400px; margin: auto; }
        input { width: 100%; padding: 8px; margin: 6px 0; box-sizing: border-box; }
        .error { color: red; }
        button { padding: 10px 20px; background: #4CAF50; color: white; border: none; cursor: pointer; }
        button:hover { background: #45a049; }
        a { color: blue; text-decoration: none; }
    </style>
</head>
<body>
    <h2>Login</h2>

    <?php if ($errors): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Username:</label>
        <input type="text" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? '') ?>" />

        <label>Password:</label>
        <input type="password" name="password" required />

        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register here</a>.</p>
</body>
</html>
