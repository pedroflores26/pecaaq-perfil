<?php
// listarProdutos.php - retorna JSON
header('Content-Type: application/json; charset=utf-8');

$servidor = "localhost";
$usuario  = "root";
$senha    = "";
$banco    = "pecaaq";

$conn = new mysqli($servidor, $usuario, $senha, $banco);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Erro de conexão']);
    exit;
}

$sql = "SELECT id_produto, nome, preco, foto_principal FROM produtos ORDER BY id_produto DESC";
$result = $conn->query($sql);
$produtos = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // garante caminho relativo para front (assumindo 'uploads/' na mesma pasta)
        $row['foto_principal'] = '../Dashboard/uploads/' . ($row['foto_principal'] ?? '');
        $produtos[] = $row;
    }
}

echo json_encode(['status'=>'ok','produtos'=>$produtos]);
$conn->close();
