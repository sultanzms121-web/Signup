<?php
// --- Database connection ---
$dsn = 'mysql:host=localhost;port=3306;dbname=myapp;charset=utf8mb4';
$pdo = new PDO($dsn, 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$errors  = [];
$welcome = null;   // holds ['name' => ..., 'city' => ...] after a good sign up

// keep what the user typed if the form fails validation
$name = '';
$age  = '';
$city = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $age  = trim($_POST['age']  ?? '');
    $city = trim($_POST['city'] ?? '');

    // TODO 1: Validate name and city. Messages go into $errors.
    if ($name === '') {
        $errors[] = 'Name is required';
    }
    if ($city === '') {
        $errors[] = 'City is required';
    }

    // TODO 2: If there are no errors, INSERT using a prepared statement.
    if (empty($errors)) {
        $stmt = $pdo->prepare(
            'INSERT INTO users (name, age, city) VALUES (:name, :age, :city)'
        );
        $stmt->execute([
            ':name' => $name,
            ':age'  => ($age === '' ? null : (int) $age),
            ':city' => $city,
        ]);

        // remember these for the welcome message
        $welcome = ['name' => $name, 'city' => $city];

        // clear the form after success
        $name = $age = $city = '';
    }
}

// Bonus: read everyone back so we can list them
$users = $pdo
    ->query('SELECT name, age, city FROM users ORDER BY id DESC')
    ->fetchAll(PDO::FETCH_ASSOC);

// tiny helper so we don't repeat htmlspecialchars() every time
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Sign Up</title>
</head>
<body>
    <h1>Sign Up</h1>

    <!-- TODO 3: Show the errors. -->
    <?php foreach ($errors as $error): ?>
        <p style="color:red;"><?= e($error) ?></p>
    <?php endforeach; ?>

    <form method="POST">
        <p><input name="name" placeholder="Name" value="<?= e($name) ?>"></p>
        <p><input name="age"  placeholder="Age"  value="<?= e($age) ?>"></p>
        <p><input name="city" placeholder="City" value="<?= e($city) ?>"></p>
        <button>Sign Up</button>
    </form>

    <!-- TODO 4: Welcome message after a successful sign up. -->
    <?php if ($welcome !== null): ?>
        <p><strong>Welcome <?= e($welcome['name']) ?> from <?= e($welcome['city']) ?>!</strong></p>
    <?php endif; ?>

    <h2>All Users</h2>
    <?php if (empty($users)): ?>
        <p>No users yet.</p>
    <?php else: ?>
        <table border="1" cellpadding="6">
            <tr><th>Name</th><th>Age</th><th>City</th></tr>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= e($user['name']) ?></td>
                    <td><?= e($user['age']) ?></td>
                    <td><?= e($user['city']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>
