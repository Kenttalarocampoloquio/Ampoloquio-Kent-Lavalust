<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <link rel="stylesheet" href="/styles.css">
</head>

<body>

    <div class="topbar">
        <h1>Products</h1>
        <div class="session">
            Logged in as <?= html_escape($_SESSION['username'] ?? '') ?>
            <a href="<?= site_url('logout') ?>">Logout</a>
        </div>
    </div>

    <div class="page">
        <div class="page-header">
            <h2>Edit Product</h2>
        </div>

        <div class="form-card">
            <form method="POST" action="<?= site_url('products/edit?id=' . $product['id']) ?>">
                <div class="field">
                    <label>Product Name</label>
                    <input type="text" name="product_name" value="<?= html_escape($product['product_name']) ?>" required>
                </div>
                <div class="field">
                    <label>Description</label>
                    <textarea name="description"><?= html_escape($product['description']) ?></textarea>
                </div>
                <div class="field">
                    <label>Price</label>
                    <input type="number" step="0.01" name="price" value="<?= html_escape($product['price']) ?>" required>
                </div>
                <div class="field">
                    <label>Quantity</label>
                    <input type="number" name="quantity" value="<?= html_escape($product['quantity']) ?>" required>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">Update</button>
                    <a href="<?= site_url('products') ?>">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</body>

</html>