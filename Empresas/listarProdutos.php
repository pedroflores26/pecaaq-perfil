<?php
header('Content-Type: application/json; charset=utf-8');

$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "pecaaq";

$conn = new mysqli($servidor, $usuario, $senha, $banco);
if ($conn->connect_error) {
    echo json_encode(['error' => "Erro de conexão: " . $conn->connect_error]);
    exit;
}

$sql = "SELECT id_produto, nome, sku_universal, marca, descricao_tecnica, foto_principal, preco, categoria, data_cadastro FROM produtos ORDER BY data_cadastro DESC";
$result = $conn->query($sql);

$produtos = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $produtos[] = $row;
    }
}

echo json_encode($produtos);
$conn->close();
?>
