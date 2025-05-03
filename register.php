<?php
// register.php  – create an account, then redirect to login
require __DIR__ . '/config.php';   // starts session + provides $pdo
include __DIR__ . '/header.php';   // <head> + nav + opens <main>

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* -----------------------------------------------------------
       1. Collect & trim form input
    ----------------------------------------------------------- */
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password =       $_POST['password']        ?? '';
    $confirm  =       $_POST['confirm_password'] ?? '';

    /* -----------------------------------------------------------
       2. Validate
    ----------------------------------------------------------- */
    if ($username === '')                       $errors[] = 'Username is required.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = 'A valid email is required.';
    if (strlen($password) < 6)                  $errors[] = 'Password must be ≥ 6 characters.';
    if ($password !== $confirm)                 $errors[] = 'Passwords do not match.';

    /* -----------------------------------------------------------
       3. Ensure username / email are unique
    ----------------------------------------------------------- */
    if (!$errors) {
        $dup = $pdo->prepare(
            'SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1'
        );
        $dup->execute([$username, $email]);
        if ($dup->fetch()) {
            $errors[] = 'Username or e-mail already taken.';
        }
    }

    /* -----------------------------------------------------------
       4. Insert + redirect to login
    ----------------------------------------------------------- */
    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $ins = $pdo->prepare(
            'INSERT INTO users (username, email, password)
             VALUES (?,?,?)'
        );
        $ins->execute([$username, $email, $hash]);

        // Account created — send user to login page
        header('Location: login.php?registered=1');
        exit;
    }
}
?>

<h2>Create an account</h2>

<?php if ($errors): ?>
    <ul style="color:#c00; margin-bottom:1em;">
        <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="register.php" style="max-width:400px">
    <label>
        Username<br>
        <input type="text" name="username"
            value="<?= htmlspecialchars($username ?? '') ?>"
            required style="width:100%">
    </label><br><br>

    <label>
        E-mail<br>
        <input type="email" name="email"
            value="<?= htmlspecialchars($email ?? '') ?>"
            required style="width:100%">
    </label><br><br>

    <label>
        Password<br>
        <input type="password" name="password"
            required style="width:100%">
    </label><br><br>

    <label>
        Confirm password<br>
        <input type="password" name="confirm_password"
            required style="width:100%">
    </label><br><br>

    <button type="submit">Register</button>
</form>

<?php
include __DIR__ . '/footer.php';   // closes <main> + page
