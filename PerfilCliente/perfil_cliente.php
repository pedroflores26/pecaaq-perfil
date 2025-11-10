<?php
session_start();

// --- Configurações do banco ---
$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "pecaaq";

// --- Verifica sessão ---
$id_usuario = $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? null;
$nomeSessao = $_SESSION['nome_razao_social'] ?? $_SESSION['nome'] ?? '';
$tipoSessao = $_SESSION['tipo_usuario'] ?? $_SESSION['tipo'] ?? '';

if (!$id_usuario) {
    // Não logado
    header("Location: ../Login/indexLogin.html");
    exit;
}

// Normaliza tipo e exige que seja cliente
if (strtolower($tipoSessao) !== 'cliente') {
    // Caso não seja cliente, manda pro login ou onde preferir
    header("Location: ../Login/indexLogin.html");
    exit;
}

// --- Conecta ao banco ---
$conn = new mysqli($servidor, $usuario, $senha, $banco);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// --- Puxa nome do banco (se existir) ---
$nomeCliente = $nomeSessao; // fallback para sessão
$sql = "SELECT nome_razao_social FROM usuarios WHERE id_usuario = ? LIMIT 1";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $stmt->bind_result($nomeFromDb);
    if ($stmt->fetch() && !empty($nomeFromDb)) {
        $nomeCliente = $nomeFromDb;
    }
    $stmt->close();
}

// --- Itens fictícios no carrinho (substituir por consulta real depois) ---
$itensCarrinho = [
    ["produto" => "Mouse Gamer RGB", "quantidade" => 1, "preco" => 149.90],
    ["produto" => "Teclado Mecânico", "quantidade" => 1, "preco" => 299.00],
    ["produto" => "Parafuso M6 x 20 (pacote 50)", "quantidade" => 2, "preco" => 24.50],
];

// Fecha conexão
$conn->close();

// --- Caminho de logout e landing (ajuste se necessário) ---
$logoutEndpoint = "../Login/logout.php";
$landingPage = "../LaningPage/indexLandingPage.html"; // keep your project's path (corrija caso seja diferente)
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8" />
  <title>Perfil do Cliente</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <style>
    /* Layout inspirado no dashboard da empresa (cores e sidebar) */
    :root{
      --bg:#e6e6e6;
      --sidebar:#111;
      --card:#fff;
      --accent:#00bcd4;
      --text:#111;
    }

    *{box-sizing:border-box}
    body{
      margin:0;
      font-family: "Inter", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      background: var(--bg);
      color:var(--text);
      display:flex;
      min-height:100vh;
    }

    /* Sidebar */
    .sidebar{
      width:240px;
      background:var(--sidebar);
      color:#fff;
      display:flex;
      flex-direction:column;
      justify-content:space-between;
      padding:24px 16px;
    }
    .brand{
      display:flex;
      gap:12px;
      align-items:center;
      margin-bottom:18px;
    }
    .brand h2{margin:0;font-size:18px;color:var(--accent);letter-spacing:0.6px}
    .nav{
      margin-top:8px;
      display:flex;
      flex-direction:column;
      gap:6px;
    }
    .nav a{
      color:#ddd;
      text-decoration:none;
      padding:10px 12px;
      border-radius:8px;
      display:flex;
      align-items:center;
      gap:10px;
    }
    .nav a:hover{background:rgba(255,255,255,0.06); color:#fff}

    .logout{
      display:block;
      text-align:center;
      background:#e74c3c;
      color:#fff;
      padding:10px 12px;
      border-radius:8px;
      text-decoration:none;
      margin-top:12px;
    }

    /* Main content */
    .main{
      flex:1;
      padding:28px;
      overflow:auto;
    }
    .header{
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:12px;
      margin-bottom:18px;
    }
    .header h1{margin:0;font-size:28px;color:var(--text)}
    .card-row{
      display:grid;
      grid-template-columns:repeat(2,1fr);
      gap:18px;
      margin-bottom:18px;
    }

    .card{
      background:var(--card);
      padding:18px;
      border-radius:10px;
      box-shadow:0 4px 12px rgba(0,0,0,0.05);
    }

    .cart-grid{
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
      gap:16px;
    }
    .cart-item{
      background:var(--card);
      padding:14px;
      border-radius:8px;
      box-shadow:0 6px 18px rgba(0,0,0,0.04);
    }
    .cart-item h3{margin:0 0 8px 0;color:var(--accent)}
    .cart-item p{margin:6px 0;color:#444}

    /* Responsividade */
    @media (max-width:900px){
      .card-row{grid-template-columns:1fr}
      .sidebar{display:none}
      body{flex-direction:column}
    }

    /* small helper */
    .muted{color:#666;font-size:0.95rem}
  </style>
</head>
<body>
  <aside class="sidebar">
    <div>
      <div class="brand">
        <img src="../DashBoard/img/logo-peçaaq.png" alt="logo" style="width:36px;height:36px;border-radius:6px;background:#fff;padding:4px" onerror="this.style.display='none'">
        <h2>PEÇAAQ</h2>
      </div>

      <nav class="nav">
        <a href="#">🏠 Início</a>
        <a href="#">🛒 Meus Pedidos</a>
        <a href="#">⚙️ Configurações</a>
      </nav>
    </div>

    <a href="#" id="btnLogout" class="logout">🔓 Sair</a>
  </aside>

  <main class="main">
    <div class="header">
      <h1>Bem-vindo, <?php echo htmlspecialchars($nomeCliente); ?></h1>
      <div class="muted">Logado como Cliente</div>
    </div>

    <div class="card-row">
      <div class="card">
        <h3>Resumo do Carrinho</h3>
        <p class="muted">Itens adicionados — valores fictícios.</p>
        <hr>
        <?php
          $total = 0;
          foreach ($itensCarrinho as $it) {
            $total += $it['quantidade'] * $it['preco'];
          }
        ?>
        <p><strong>Quantidade de itens:</strong> <?php echo count($itensCarrinho); ?></p>
        <p><strong>Valor total:</strong> R$ <?php echo number_format($total,2,',','.'); ?></p>
      </div>

      <div class="card">
        <h3>Informações</h3>
        <p class="muted">Dados vindos da sessão / banco.</p>
        <p><strong>Nome:</strong> <?php echo htmlspecialchars($nomeCliente); ?></p>
        <p><strong>ID usuário:</strong> <?php echo htmlspecialchars($id_usuario); ?></p>
      </div>
    </div>

    <section class="card">
      <h3>Itens no Carrinho</h3>
      <div class="cart-grid" style="margin-top:12px">
        <?php foreach ($itensCarrinho as $it): ?>
          <div class="cart-item">
            <h3><?php echo htmlspecialchars($it['produto']); ?></h3>
            <p>Quantidade: <?php echo (int)$it['quantidade']; ?></p>
            <p>Preço unit.: R$ <?php echo number_format($it['preco'],2,',','.'); ?></p>
            <p><strong>Total:</strong> R$ <?php echo number_format($it['quantidade'] * $it['preco'],2,',','.'); ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </main>

 <script>
  // "Logout" que só redireciona, sem remover sessão nem localStorage
  document.getElementById('btnLogout').addEventListener('click', function(e){
    e.preventDefault();
    // Apenas redireciona para a landing page
    window.location.href = '<?php echo addslashes($landingPage); ?>';
  });
</script>

</body>
</html>
