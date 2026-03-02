<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php?mode=login");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tastybites";

// Connect to database
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item = trim($_POST['item'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $user_id = $_SESSION['user_id'];

    if ($item === '' || $price === '' || !is_numeric($price)) {
        echo "<h2>Invalid order data. Please try again.</h2>";
        exit;
    }

    $price_float = floatval($price);

    // Prepare and bind
    $stmt = mysqli_prepare($conn, "INSERT INTO orders (user_id, item, price) VALUES (?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "isd", $user_id, $item, $price_float);

    if (mysqli_stmt_execute($stmt)) {
        // Use htmlspecialchars only on output to avoid XSS
        $item_safe = htmlspecialchars($item, ENT_QUOTES);
        $price_safe = htmlspecialchars($price, ENT_QUOTES);

        echo "<h1>✅ Thank you for your order!</h1>";
        echo "<p>You ordered: <strong>$item_safe</strong> for <strong>Rs.$price_safe</strong>.</p>";
        echo '<p><a href="menu.php">🔙 Go back to menu</a></p>';
    } else {
        echo "<h2>Failed to save your order. Please try again later.</h2>";
        echo "Error: " . mysqli_stmt_error($stmt);
    }

    mysqli_stmt_close($stmt);
} else {
    header("Location: menu.php");
    exit;
}

mysqli_close($conn);
?>
