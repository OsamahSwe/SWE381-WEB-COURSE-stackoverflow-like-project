<?php
// login.php — authenticate an existing user
require __DIR__ . '/config.php';   // session + $pdo
include __DIR__ . '/header.php';   // HTML head, nav, <main>

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Grab & sanitize input
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // 2. Basic validation
    if ($username === '' || $password === '') {
        $errors[] = "Both fields are required.";
    }

    // 3. Fetch user and verify password
    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "SELECT id, password FROM users WHERE username = ?"
        );
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // 4. Success: set session and redirect
            $_SESSION['user_id']   = (int)$user['id'];   // ← cast to int
            $_SESSION['username']  = $username;
            header('Location: index.php');
            exit;
        } else {
            $errors[] = "Invalid username or password.";
        }
    }
}
?>

<h2>Login</h2>

<?php
// show registration-success message if redirected
if (isset($_GET['registered'])): ?>
  <p style="color:green;">Account created! Please log in below.</p>
<?php endif; ?>

<?php if ($errors): ?>
    <ul style="color: red;">
        <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="login.php">
    <label>
        Username:<br>
        <input
          type="text"
          name="username"
          value="<?= htmlspecialchars($username ?? '') ?>"
          required>
    </label>
    <br><br>

    <label>
        Password:<br>
        <input type="password" name="password" required>
    </label>
    <br><br>

    <button type="submit">Login</button>
</form>

<?php include __DIR__ . '/footer.php'; ?>
