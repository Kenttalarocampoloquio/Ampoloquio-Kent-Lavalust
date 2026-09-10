<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Products</title>
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
            <h2>Inventory</h2>
            <a class="btn btn-primary" href="<?= site_url('products/create') ?>">Add New Product</a>
        </div>

        <div class="card">
            <table>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
                <?php if (empty($products)): ?>
                <tr>
                    <td colspan="6" class="empty-state">No products yet. Add your first one to get started.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?= html_escape($p['product_name']) ?></td>
                        <td><?= html_escape($p['description']) ?></td>
                        <td><?= html_escape($p['price']) ?></td>
                        <td><?= html_escape($p['quantity']) ?></td>
                        <td><?= html_escape($p['created_at']) ?></td>
                        <td class="actions">
                            <a class="btn btn-edit" href="<?= site_url('products/edit?id=' . $p['id']) ?>">Edit</a>
                            <a class="btn btn-delete" href="<?= site_url('products/delete?id=' . $p['id']) ?>" onclick="return confirm('Delete this product?');">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
        </div>
    </div>

</body>

</html>