<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Users</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'Segoe UI', sans-serif;
    background: #0a0a0a;
    color: #fff;
    min-height: 100vh;
}
header {
    padding: 20px 40px;
    background: #0a0a0a;
    border-bottom: 1px solid #1a1a1a;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.logo { font-weight: 800; font-size: 1.2rem; }
.logo span { color: #ff6b35; }
nav { display: flex; gap: 24px; align-items: center; }
nav a { color: #666; text-decoration: none; font-size: 0.9rem; }
nav a:hover { color: #ff6b35; }
main {
    padding: 60px 40px;
}
h1 {
    font-size: 1.8rem;
    margin-bottom: 30px;
}
h1 span { color: #ff6b35; }
table {
    width: 100%;
    border-collapse: collapse;
    background: #111;
    border: 1px solid #1a1a1a;
}
th, td {
    padding: 14px 20px;
    text-align: left;
    border-bottom: 1px solid #1a1a1a;
}
th {
    background: #1a1a1a;
    color: #ff6b35;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.05em;
}
tr:last-child td { border-bottom: none; }
tr:hover td { background: #161616; }
</style>
</head>
<body>
<header>
    <div class="logo">Ang aking <span>Users</span></div>
    <nav>
        <a href="student">Student</a>
        <a href="student/profile">Profile</a>
        <a href="users">Users</a>
    </nav>
</header>
<main>
<h1>Users <span>Management</span></h1>
<table>
<tr>
<th>ID</th>
<th>First Name</th>
<th>Last Name</th>
<th>Email</th>
<th>Username</th>
</tr>
<?php foreach ($users as $user): ?>
<tr>
<td><?= $user['id'] ?></td>
<td><?= $user['firstname'] ?></td>
<td><?= $user['lastname'] ?></td>
<td><?= $user['email'] ?></td>
<td><?= $user['username'] ?></td>
</tr>
<?php endforeach; ?>
</table>
</main>
</body>
</html>