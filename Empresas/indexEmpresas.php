<?php
$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "pecaaq";

$conn = new mysqli($servidor, $usuario, $senha, $banco);
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

$sql = "SELECT * FROM produtos ORDER BY id_produto DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>PeçaAq - Produtos</title>
  <link rel="stylesheet" href="styleEmpresas.css">
  <style>
    /* ====== HEADER ====== */
    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background-color: #0A0A23;
      color: #fff;
      padding: 15px 40px;
      position: sticky;
      top: 0;
      z-index: 1000;
    }
    header .logo {
      font-size: 1.5em;
      font-weight: bold;
      letter-spacing: 1px;
    }
    header nav {
      display: flex;
      gap: 15px;
    }
    header a {
      color: white;
      text-decoration: none;
      background: #1E90FF;
      padding: 8px 14px;
      border-radius: 8px;
      font-weight: 500;
      transition: 0.3s;
    }
    header a:hover {
      background: #63B3ED;
    }

    /* ====== BODY ====== */
    body {
      font-family: 'Inter', Arial, sans-serif;
      background: #f4f4f9;
      margin: 0;
      padding: 0;
    }

    h1 {
      text-align: center;
      margin: 40px 0 10px;
      color: #333;
    }

    /* ====== GRID ====== */
    .grid {
      display: flex;
      flex-wrap: wrap;
      gap: 25px;
      justify-content: center;
      padding: 30px;
    }

    /* ====== CARD ====== */
    .card {
      width: 230px;
      background: #fff;
      border-radius: 15px;
      padding: 15px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 15px rgba(0,0,0,0.2);
    }
    .card img {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-radius: 10px;
    }
    .card h3 {
      font-size: 1.1em;
      margin: 10px 0 5px;
      color: #222;
    }
    .card p {
      font-size: 14px;
      color: #444;
      margin: 3px 0;
    }
  </style>
</head>
<body>

<header>
  <div class="logo">PEÇAAQ</div>
  <nav>
    <a href="cadastroProduto.php">Cadastrar Produto</a>
    <a href="#produtos">Ver Produtos</a>
  </nav>
</header>

<h1 id="produtos">Produtos Cadastrados</h1>

<div class="grid">
<?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='card'>";
        echo "<img src='uploads/" . htmlspecialchars($row['foto_principal']) . "' alt='Imagem do Produto'>";
        echo "<h3>" . htmlspecialchars($row['nome']) . "</h3>";
        echo "<p><strong>Marca:</strong> " . htmlspecialchars($row['marca']) . "</p>";
        echo "<p><strong>Descrição:</strong> " . htmlspecialchars($row['descricao_tecnica']) . "</p>";
        echo "<p><strong>Preço:</strong> R$ " . htmlspecialchars($row['preco']) . "</p>";
echo "<p><strong>Categoria:</strong> " . htmlspecialchars($row['categoria']) . "</p>";
        // Aqui adicionamos o input escondido com o ID do anúncio
        echo "<input type='hidden' class='id_anuncio' value='" . $row['id_produto'] . "'>";

        // Botão de adicionar ao carrinho
        echo "<button class='addCarrinho'>Adicionar ao Carrinho</button>";
        echo "</div>";
    }
} else {
    echo "<p>Nenhum produto cadastrado ainda.</p>";
}
$conn->close();
?>
</div>

</body>
</html>
