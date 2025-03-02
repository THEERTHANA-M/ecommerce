<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : null;

if (!$order_id) {
    header("Location: ../index.php");
    exit();
}

// Fetch order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "Invalid order.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Confirmation</title>
<style>
    body { font-family: Arial, sans-serif; background-color: #f8f9fa; padding: 20px; }
    .container { max-width: 600px; margin: auto; background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); text-align: center; }
    .btn { background-color: #28a745; color: #fff; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
    .btn:hover { background-color: #218838; }
</style>
</head>
<body>

<div class="container">
    <h2>Congratulations! Your order has been placed.</h2>
    <p>Thank you for your purchase, <?= htmlspecialchars($order['name']) ?>!</p>

    <a href="../index.php" class="btn">Continue Shopping</a>
</div>

</body>
</html>
