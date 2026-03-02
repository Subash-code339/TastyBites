<?php
session_start();
include 'db_connect.php';

$mode = $_GET['mode'] ?? 'login'; // To toggle between login and register

// Handle Registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $reg_error = "Passwords do not match.";
    } else {
        // Check if username or email exists
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username=? OR email=?");
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $reg_error = "Username or email already taken.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sss", $username, $email, $password_hash);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success'] = "Registration successful! Please log in.";
                header("Location: auth.php?mode=login");
                exit;
            } else {
                $reg_error = "Registration failed. Try again.";
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username_or_email = trim($_POST['username_or_email']);
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT id, username, password FROM users WHERE username=? OR email=?");
    mysqli_stmt_bind_param($stmt, "ss", $username_or_email, $username_or_email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (!empty($hashed_password) && password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['username'] = $username;
        header("Location: menu.php");
        exit;
    } else {
        $login_error = "Invalid username/email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Login / Register - TastyBites</title>
<style>
    body { font-family: Arial, sans-serif; max-width: 400px; margin: auto; padding: 20px; }
    form { border: 1px solid #ccc; padding: 15px; border-radius: 5px; }
    input { width: 100%; padding: 8px; margin: 5px 0 15px 0; box-sizing: border-box; }
    button { padding: 10px; width: 100%; background-color: #28a745; border: none; color: white; font-size: 16px; cursor: pointer; }
    button:hover { background-color: #218838; }
    .error { color: red; }
    .success { color: green; }
    .toggle-link { text-align: center; margin-top: 10px; }
    .toggle-link a { cursor: pointer; color: blue; text-decoration: underline; }
</style>
</head>
<body>

<h2><?= $mode === 'register' ? 'Register' : 'Login' ?></h2>

<?php if (!empty($_SESSION['success'])) { echo "<p class='success'>" . $_SESSION['success'] . "</p>"; unset($_SESSION['success']); } ?>

<?php if ($mode === 'register'): ?>
    <?php if (!empty($reg_error)) echo "<p class='error'>$reg_error</p>"; ?>
    <form method="POST" action="auth.php?mode=register">
        <input type="text" name="username" placeholder="Username" required />
        <input type="email" name="email" placeholder="Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <input type="password" name="confirm_password" placeholder="Confirm Password" required />
        <button type="submit" name="register">Register</button>
    </form>
    <div class="toggle-link">
        Already have an account? <a href="auth.php?mode=login">Login here</a>.
    </div>

<?php else: ?>
    <?php if (!empty($login_error)) echo "<p class='error'>$login_error</p>"; ?>
    <form method="POST" action="auth.php?mode=login">
        <input type="text" name="username_or_email" placeholder="Username or Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit" name="login">Login</button>
    </form>
    <div class="toggle-link">
        Don't have an account? <a href="auth.php?mode=register">Register here</a>.
    </div>
<?php endif; ?>

</body>
</html>
