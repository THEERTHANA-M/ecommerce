<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';

$user_id = $_SESSION['user_id'];

// Fetch cart items with images for the user
$stmt = $conn->prepare("SELECT c.product_id, c.quantity, p.name, p.price, p.image 
                        FROM cart c 
                        INNER JOIN products p ON c.product_id = p.id 
                        WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_cost = 0;
foreach ($cart_items as $item) {
    $total_cost += $item['price'] * $item['quantity'];
}

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    // Insert order into the database
    $stmt = $conn->prepare("INSERT INTO orders (user_id, name, email, address, total_cost) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $name, $email, $address, $total_cost]);
    $order_id = $conn->lastInsertId();  // ✅ Correct


    // Insert each item into order_items table
    foreach ($cart_items as $item) {
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
    }

    // Clear the cart after successful order
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);

    header("Location: order_confirmation.php?order_id=$order_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout</title>
<style>
    body { font-family: Arial, sans-serif; background-color: #f8f9fa; padding: 20px; }
    .container { max-width: 700px; margin: auto; background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
    .order-summary { margin-bottom: 20px; }
    .order-summary h3 { margin-bottom: 10px; }
    .order-summary ul { list-style-type: none; padding: 0; }
    .order-summary li { display: flex; align-items: center; border-bottom: 1px solid #ddd; padding: 10px 0; }
    .order-summary img { width: 60px; height: 60px; object-fit: cover; margin-right: 15px; border-radius: 5px; }
    .product-details { flex-grow: 1; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; }
    .form-group input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
    .btn { background-color: #28a745; color: #fff; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; }
    .btn:hover { background-color: #218838; }
    .total-cost { font-weight: bold; margin-top: 10px; }
</style>
</head>

<body>

<div class="container">
    <h2>Checkout</h2>
    <div class="order-summary">
        <h3>Order Summary</h3>
        <?php if (!empty($cart_items)) { ?>
            <ul>
                <?php foreach ($cart_items as $item) { ?>
                    <li>
                        <img src="../images/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                        <div class="product-details">
                            <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                            $<?= number_format($item['price'], 2) ?> x <?= $item['quantity'] ?>
                        </div>
                    </li>
                <?php } ?>
            </ul>
            <p class="total-cost">Total: $<?= number_format($total_cost, 2) ?></p>
        <?php } else { ?>
            <p>Your cart is empty.</p>
        <?php } ?>
    </div>

    <form action="checkout.php" method="post">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="address">Shipping Address</label>
            <input type="text" id="address" name="address" required>
        </div>
        <button type="submit" class="btn">Place Order</button>
    </form>
</div>

</body>
</html>
