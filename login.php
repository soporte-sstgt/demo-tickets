<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    if ($password === 'Soporte2025') {
        $_SESSION['loggedin'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Contraseña incorrecta.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <title>Login</title>
</head>
<body class="flex items-center justify-center h-screen bg-gray-200">
    <div class="bg-white p-6 rounded shadow-md w-80">
        <h2 class="text-xl font-bold mb-4">Iniciar Sesión</h2>
        <?php if (isset($error)) echo "<p class='text-red-500'>$error</p>"; ?>
        <form method="POST">
            <input type="password" name="password" class="border border-gray-300 p-2 w-full rounded mb-4" placeholder="Contraseña" required>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Entrar</button>
        </form>
    </div>
</body>
</html>