<?php
session_start();

// Config DB
$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "pecaaq"; // sem acento

$conn = new mysqli($servidor, $usuario, $senha, $banco);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Recebe dados
$tipo = $_POST['tipo'] ?? '';
$login = trim($_POST['login'] ?? '');
$senha = $_POST['senha'] ?? '';

if (empty($tipo) || empty($login) || empty($senha)) {
    die("⚠ Todos os campos são obrigatórios!");
}

// Normaliza login
$loginLimpo = preg_replace('/\D/', '', $login);

// Mapeia tipo
$tipo_map = strtolower($tipo) === 'empresa' ? 'Fornecedor' : ucfirst(strtolower($tipo));
if (!in_array($tipo_map, ['Cliente', 'Fornecedor'])) {
    die("Tipo de login inválido!");
}

// Consulta
$sql = "SELECT id_usuario, nome_razao_social, email, senha_hash, tipo, documento 
        FROM usuarios 
        WHERE tipo = ? AND (email = ? OR documento = ?) 
        LIMIT 1";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Erro na query: " . $conn->error);
}

$stmt->bind_param("sss", $tipo_map, $login, $loginLimpo);
$stmt->execute();

$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    die("❌ Usuário não encontrado!");
}

$usuario = $result->fetch_assoc();
$stmt->close();

// Verifica senha
if (!isset($usuario['senha_hash']) || !password_verify($senha, $usuario['senha_hash'])) {
    $conn->close();
    die("❌ Senha incorreta!");
}

// Cria sessão
$_SESSION['id_usuario'] = $usuario['id_usuario'];
$_SESSION['nome_razao_social'] = $usuario['nome_razao_social'];
$_SESSION['tipo_usuario'] = $usuario['tipo'];

// Dados seguros para localStorage
$usuarioParaLocal = [
    'id_usuario' => (int)$usuario['id_usuario'],
    'nome_razao_social' => $usuario['nome_razao_social'],
    'email' => $usuario['email'] ?? '',
    'tipo' => $usuario['tipo'] ?? 'Cliente'
];

// 🔁 Define destino conforme tipo
// Redireciona conforme o tipo de usuário
if ($usuario['tipo'] === 'Fornecedor') {
    // Empresa -> dashboard
    $destino = '../LaningPage/indexLandingPage.html';
} else {
    // Cliente -> perfil de cliente
    $destino = '../LaningPage/indexLandingPage.html';
}


?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Login realizado</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body { font-family: Arial, Helvetica, sans-serif; padding:30px; text-align:center; }
    .msg { margin-top:20px; }
  </style>
</head>
<body>
  <h2>Login realizado com sucesso!</h2>
  <p class="msg">Você será redirecionado em instantes...</p>

  <script>
    (function(){
      try {
        var usuario = <?php echo json_encode($usuarioParaLocal, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;
        localStorage.setItem('usuarioLogado', JSON.stringify(usuario));
      } catch (e) {
        console.warn('Não foi possível gravar localStorage:', e);
      }
      // Redireciona para a página conforme o tipo
      setTimeout(function(){
        window.location.href = '<?php echo addslashes($destino); ?>';
      }, 600);
    })();
  </script>
</body>
</html>

<?php
$conn->close();
exit;
?>
