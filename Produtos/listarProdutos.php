<?php
// Produtos/listarProdutos.php
header('Content-Type: application/json; charset=utf-8');

// Config DB
$host = "localhost";
$user = "root";
$pass = "";
$db   = "pecaaq";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Falha na conexão com o banco de dados"]);
    exit;
}

// Ajuste da query conforme sua tabela 'produtos'
$sql = "SELECT id_produto, id_categoria, nome, sku_universal, marca, descricao_tecnica, foto_principal, preco, categoria, data_cadastro
        FROM produtos
        ORDER BY data_cadastro DESC";

$result = $conn->query($sql);
$produtos = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Normaliza preço (string -> número) — mantém original caso precise
        $row['preco'] = isset($row['preco']) ? $row['preco'] : '0.00';

        // Monta caminho absoluto relativo ao host para evitar 404 por caminhos relativos
        // Ex.: /PECAAQteste-main/Produtos/uploads/nome.jpg
        $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        if (!empty($row['foto_principal'])) {
            $row['foto_principal'] = $scriptDir . '/uploads/' . ltrim($row['foto_principal'], '/\\');
        } else {
            // imagem padrão caso não exista
            $row['foto_principal'] = $scriptDir . '/img/sem-imagem.png';
        }

        $produtos[] = $row;
    }
}

// retorna apenas o array (seu front aceita array ou {produtos: [...]})
echo json_encode($produtos, JSON_UNESCAPED_UNICODE);
$conn->close();
