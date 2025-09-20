<?php// --- Atualizar google_email ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['google_token'])) {

    // Decodifica o JWT manualmente
    $tokenParts = explode('.', $_POST['google_token']);
    if (count($tokenParts) === 3) {
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1])), true);

        if ($payload && isset($payload['email'])) {
            $googleEmail = $payload['email'];

            // Atualiza google_email no banco
            $stmt = $pdo->prepare("UPDATE Usuarios_TBL SET google_email = :google_email WHERE id_usuario = :id");
            $success = $stmt->execute([
                'google_email' => $googleEmail,
                'id' => $usuario['id']
            ]);

            // Atualiza sessão
            $_SESSION['usuario']['google_email'] = $googleEmail;
            $_SESSION['google_status'] = $success ? 'ok' : 'erro';
            header("Location: index.php");
            exit;
        }
    }

    $_SESSION['google_status'] = 'erro';
    header("Location: index.php");
    exit;
}

<?php
// Mostra alerta de sucesso ou erro
if (isset($_SESSION['google_status'])) {
    echo "<script>alert('" . ($_SESSION['google_status'] === 'ok' ? 'Conta Google conectada com sucesso!' : 'Erro ao conectar conta Google.') . "');</script>";
    unset($_SESSION['google_status']);
}
?>

<script>
function parseJwt(token) {
    const base64Url = token.split('.')[1];
    const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    return JSON.parse(decodeURIComponent(
        atob(base64).split('').map(c => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)).join('')
    ));
}

function handleAddGoogle(response) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';

    const tokenInput = document.createElement('input');
    tokenInput.name = 'google_token';
    tokenInput.value = response.credential;
    form.appendChild(tokenInput);

    document.body.appendChild(form);
    form.submit();
}
</script>
?>