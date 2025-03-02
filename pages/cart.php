<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';

$user_id = $_SESSION['user_id'];

if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cart_item) {
        $new_quantity = $cart_item['quantity'] + $quantity;
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$new_quantity, $user_id, $product_id]);
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity]);
    }
    header("Location: cart.php");
    exit();
}

if (isset($_POST['remove_from_cart'])) {
    $product_id = $_POST['product_id'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    header("Location: cart.php");
    exit();
}

if (isset($_POST['update_quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$quantity, $user_id, $product_id]);
    header("Location: cart.php");
    exit();
}

$stmt = $conn->prepare("SELECT cart.*, products.name, products.image, products.price 
                        FROM cart 
                        INNER JOIN products ON cart.product_id = products.id 
                        WHERE cart.user_id = ?");

$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_cost = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }
        .cart-item img {
            width: 80px;
            border-radius: 5px;
        }
        .cart-actions {
            display: flex;
            gap: 10px;
        }
        .cart-actions form {
            display: inline-block;
        }
        .btn {
            padding: 8px 12px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .btn-blue { background: #007bff; color: white; }
        .btn-red { background: #007bff; color: white; }
        .btn-green { background: #28a745; color: white; text-decoration: none; display: inline-block; text-align: center; }
    </style>
</head>
<body>
<div class="container">
    <h2>Your Cart</h2>
    <?php if (!empty($cart_items)) { ?>
        <?php foreach ($cart_items as $item) { ?>
            <div class="cart-item">
            <img src="../images/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">

                <div>
                    <strong>Product <?= $item['product_id'] ?></strong><br>
                    $<?= number_format($item['quantity'] * 100, 2) ?> x <?= $item['quantity'] ?>
                </div>
                <div class="cart-actions">
                    <form method="POST">
                        <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1">
                        <button type="submit" name="update_quantity" class="btn btn-blue">Update</button>
                    </form>
                    <form method="POST">
                        <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                        <button type="submit" name="remove_from_cart" class="btn btn-red">Remove</button>
                    </form>
                </div>
            </div>
        <?php } ?>
        <h3>Total: $<?= number_format($total_cost, 2) ?></h3>
    <?php } else { ?>
        <p>Your cart is empty.</p>
    <?php } ?>
    <a href="../index.php" class="btn btn-green">Back to Shop</a>
    <a href="checkout.php" class="btn btn-green">Proceed to Checkout</a>
</div>
</body>
</html>