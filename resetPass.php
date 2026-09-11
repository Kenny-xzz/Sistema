<?php
require 'config/db.php';

$token = $_GET['token'] ?? $_POST['token'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM reset_tokens WHERE token = ? AND expira_em > NOW()");
$stmt->execute([$token]);
$resetToken = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resetToken) {
    die("Link inválido ou expirado. <a href='esqueci-senha.html'>Pedir novo link</a>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novaPassword = $_POST['password'];
    $hash = password_hash($novaPassword, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE utilizadores SET password = ? WHERE id = ?");
    $stmt->execute([$hash, $resetToken['utilizador_id']]);

    $stmt = $pdo->prepare("DELETE FROM reset_tokens WHERE id = ?");
    $stmt->execute([$resetToken['id']]);

    echo "<script>alert('Password atualizada com sucesso!'); window.location.href='Login.html';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Nova Password - ITC</title>
    <link rel="stylesheet" href="public/css/login.css">
</head>
<body>
    <video autoplay muted loop playsinline class="bg-video">
        <source src="Icones_Fotos/clouds.mp4" type="video/mp4">
    </video>

    <div class="login-container" style="grid-template-columns: 1fr;">
        <div class="login-form-area">
            <div class="login-content">
                <div class="logo-area">
                    <h1>Definir Nova Password</h1>
                </div>
                <form method="POST">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <div class="form-group">
                        <label>Nova Password</label>
                        <div class="input-container">
                            <input type="password" name="password" required minlength="6" placeholder="••••••••">
                        </div>
                    </div>
                    <button type="submit" class="login-button">Guardar Nova Password</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>