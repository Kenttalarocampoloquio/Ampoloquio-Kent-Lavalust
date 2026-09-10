<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Product</title>
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
            <h2>Add Product</h2>
        </div>

        <div class="form-card">
            <form method="POST" action="<?= site_url('products/create') ?>">
                <div class="field">
                    <label>Product Name</label>
                    <input type="text" name="product_name" required>
                </div>
                <div class="field">
                    <label>Description</label>
                    <textarea name="description"></textarea>
                </div>
                <div class="field">
                    <label>Price</label>
                    <input type="number" step="0.01" name="price" required>
                </div>
                <div class="field">
                    <label>Quantity</label>
                    <input type="number" name="quantity" required>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">Save</button>
                    <a href="<?= site_url('products') ?>">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</body>

</html>