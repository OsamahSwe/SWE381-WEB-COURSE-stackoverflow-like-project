<?php
// header.php — included by every page right after config.php
require __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SO Clone</title>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Main stylesheet (only once) -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Optional main JS -->
    <script src="assets/js/scripts.js"></script>
</head>

<body>
    <header class="top-bar">
        <a href="index.php" class="logo">
            <h1>stack overflow</h1>
        </a>

        <nav class="navigation">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php">
                    <i class="fa fa-user"></i>
                    <!-- safe fallback prevents “undefined array key” notice -->
                    <?= htmlspecialchars($_SESSION['username'] ?? 'Profile') ?>
                </a>
                <a href="ask_question.php"><i class="fa fa-edit"></i> Ask Question</a>
                <a href="my_questions.php"><i class="fa fa-question-circle"></i> My Questions</a>
                <a href="my_answers.php"><i class="fa fa-reply"></i> My Answers</a>
                <a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="register.php"><i class="fa fa-user-plus"></i> Register</a>
                <a href="login.php"><i class="fa fa-sign-in-alt"></i> Login</a>
            <?php endif; ?>
        </nav>

        <form class="search-bar" method="get" action="search.php">
            <i class="fa fa-search"></i>
            <input type="text"
                name="q"
                required
                placeholder="Search questions…"
                value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            <button type="submit" style="display:none;">Search</button>
        </form>
    </header>

    <main class="container">
        <aside class="left-sidebar">
            <ul class="nav-links">
                <li class="<?= basename($_SERVER['SCRIPT_NAME']) === 'index.php' ? 'active' : '' ?>">
                    <a href="index.php"><i class="fa fa-home"></i> Home</a>
                </li>

                <li class="<?= in_array(basename($_SERVER['SCRIPT_NAME']), ['questions.php', 'view_question.php']) ? 'active' : '' ?>">
                    <a href="questions.php"><i class="fa fa-question"></i> Questions</a>
                </li>

                <li class="<?= basename($_SERVER['SCRIPT_NAME']) === 'tags.php' ? 'active' : '' ?>">
                    <a href="tags.php"><i class="fa fa-tag"></i> Tags</a>
                </li>

                <li class="<?= basename($_SERVER['SCRIPT_NAME']) === 'discussions.php' ? 'active' : '' ?>">
                    <a href="discussions.php"><i class="fa fa-comments"></i> Discussions</a>
                </li>

                <li class="<?= basename($_SERVER['SCRIPT_NAME']) === 'users.php' ? 'active' : '' ?>">
                    <a href="users.php"><i class="fa fa-user"></i> Users</a>
                </li>
            </ul>
        </aside>

        <!-- page‑specific content starts here -->
        <section class="main-content">