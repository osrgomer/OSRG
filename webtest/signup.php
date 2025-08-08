<?php
$usersFile = __DIR__ . '/users.json';
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    if (isset($users[$username])) {
        $error = 'Username already exists';
    } else {
        $users[$username] = [
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ];
        file_put_contents($usersFile, json_encode($users));
        header('Location: login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HotGram Signup</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-sm">
        <h2 class="text-2xl font-bold mb-6 text-center">Sign Up for HotGram</h2>
        <?php if (isset($error)) { echo '<p class="text-red-500 text-center mb-4">' . $error . '</p>'; } ?>
        <form method="POST" action="signup.php">
            <input type="text" name="username" placeholder="Username" class="w-full p-2 mb-4 border rounded" required>
            <input type="password" name="password" placeholder="Password" class="w-full p-2 mb-4 border rounded" required>
            <button type="submit" class="w-full bg-green-500 text-white p-2 rounded hover:bg-green-600">Sign Up</button>
        </form>
        <p class="mt-4 text-center text-sm">Already have an account? <a href="login.php" class="text-blue-500 hover:underline">Login</a></p>
    </div>
</body>
</html>
