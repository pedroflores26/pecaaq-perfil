<?php
$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "pecaaq";

$conn = new mysqli($servidor, $usuario, $senha, $banco);
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Função para validar campos
function validar($campo) {
    return isset($_POST[$campo]) ? trim($_POST[$campo]) : null;
}

// Dados do formulário
$nome = validar('nome');
$sku = validar('sku');
$marca = validar('marca');
$descricao = validar('descricao');
$preco = validar('preco');
$categoria = validar('categoria') ?? 'Peças';

// Verifica campos obrigatórios
if (!$nome || !$preco) {
    die("❌ Nome e preço são obrigatórios.");
}

// Upload da imagem
if (!isset($_FILES['foto']) || $_FILES['foto']['error'] != 0) {
    die("❌ Erro no upload da imagem.");
}

$foto = $_FILES['foto']['name'];
$pasta = "uploads/";

if (!is_dir($pasta)) {
    mkdir($pasta, 0777, true);
}

$caminho_final = $pasta . basename($foto);

// Validação de tipo de arquivo (jpg, png, gif)
$tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($_FILES['foto']['type'], $tipos_permitidos)) {
    die("❌ Tipo de arquivo não permitido. Apenas JPG, PNG ou GIF.");
}

// Validação de tamanho (máx 5MB)
if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
    die("❌ Arquivo muito grande. Máx 5MB.");
}

if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminho_final)) {
    $sql = "INSERT INTO produtos (nome, sku_universal, marca, descricao_tecnica, foto_principal, preco, categoria) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("sssssss", $nome, $sku, $marca, $descricao, $foto, $preco, $categoria);

        if ($stmt->execute()) {
            echo "<script>alert('✅ Produto cadastrado com sucesso!'); window.location.href='indexEmpresas.html';</script>";
        } else {
            echo "Erro ao salvar produto: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Erro na preparação da query: " . $conn->error;
    }
} else {
    echo "❌ Erro ao mover o arquivo para a pasta uploads.";
}

$conn->close();
?>
