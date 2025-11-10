<?php
// Produtos/processaProduto.php
header('Content-Type: application/json; charset=utf-8');

$host = "localhost";
$user = "root";
$pass = "";
$db   = "pecaaq";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Falha na conexão com o banco']);
    exit;
}

// Recebe dados do form (normaliza)
$nome       = trim($_POST['nome'] ?? '');
$sku        = trim($_POST['sku'] ?? '');
$marca      = trim($_POST['marca'] ?? '');
$descricao  = trim($_POST['descricao'] ?? '');
$preco_raw  = trim($_POST['preco'] ?? '');
$categoria  = trim($_POST['categoria'] ?? '');
$id_categoria = isset($_POST['id_categoria']) && $_POST['id_categoria'] !== '' ? intval($_POST['id_categoria']) : null;

// arquivo
$foto_field = 'foto';
if (!isset($_FILES[$foto_field]) || $_FILES[$foto_field]['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status'=>'error','message'=>'Imagem não enviada ou ocorreu erro no upload.']);
    $conn->close();
    exit;
}

// validações mínimas
if ($nome === '' || $preco_raw === '') {
    echo json_encode(['status'=>'error','message'=>'Campos obrigatórios faltando: nome ou preço.']);
    $conn->close();
    exit;
}

// converte preço (aceita "1.234,56" ou "1234.56")
$preco_normalizado = str_replace(['.',','], ['','.'], $preco_raw); // transforma 1.234,56 -> 1234.56 (atenção: isso também muda 1.234 -> 1234)
$preco_normalizado = preg_replace('/[^\d\.]/','', $preco_normalizado);
$preco = (float) $preco_normalizado;

// pasta de uploads (garante que esteja dentro da pasta Produtos)
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        echo json_encode(['status'=>'error','message'=>'Não foi possível criar pasta de uploads. Verifique permissões.']);
        $conn->close();
        exit;
    }
}

$originalName = basename($_FILES[$foto_field]['name']);
$ext = pathinfo($originalName, PATHINFO_EXTENSION);
$ext = $ext ? '.' . strtolower($ext) : '';
$allowed = ['jpg','jpeg','png','webp','gif'];
if ($ext) {
    $checkExt = ltrim($ext, '.');
    if (!in_array($checkExt, $allowed)) {
        echo json_encode(['status'=>'error','message'=>'Tipo de imagem não permitido. Use jpg/png/webp/gif.']);
        $conn->close();
        exit;
    }
}

$uniqueName = uniqid('prod_') . $ext;
$targetPath = $uploadDir . $uniqueName;

if (!move_uploaded_file($_FILES[$foto_field]['tmp_name'], $targetPath)) {
    echo json_encode(['status'=>'error','message'=>'Erro ao mover arquivo para uploads. Verifique permissões.']);
    $conn->close();
    exit;
}

// grava apenas o nome do arquivo no DB (listarProdutos.php monta o caminho com /Produtos/uploads/)
$foto_db = $uniqueName;

// Insere no banco (prepared)
$sql = "INSERT INTO produtos (id_categoria, nome, sku_universal, marca, descricao_tecnica, foto_principal, preco, categoria, data_cadastro)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    // remove arquivo caso erro no DB
    @unlink($targetPath);
    echo json_encode(['status'=>'error','message'=>"Erro ao preparar consulta: " . $conn->error]);
    $conn->close();
    exit;
}

// Para bind: se id_categoria for null, passamos null (mysqli trata null como SQL NULL)
$tipo_bind = "issssds";
/*
 tipos:
 i -> id_categoria (int) (aceita null)
 s -> nome
 s -> sku_universal
 s -> marca
 s -> descricao_tecnica
 s -> foto_principal
 d -> preco (double)
 s -> categoria
*/

$stmt->bind_param(
    "isssssds",
    $id_categoria,
    $nome,
    $sku,
    $marca,
    $descricao,
    $foto_db,
    $preco,
    $categoria
);

// OBS: alguns ambientes mysqli têm problemas com bind_param passando NULL para i. Se você encontrar erro,
// substitua o bloco acima por uma versão que monta a query dinamicamente definindo id_categoria = NULL quando $id_categoria === null.

if (!$stmt->execute()) {
    // remove arquivo se falhar insert
    @unlink($targetPath);
    echo json_encode(['status'=>'error','message'=>'Erro ao inserir produto: ' . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit;
}

$id_produto = $stmt->insert_id;
$stmt->close();
$conn->close();

// Resposta JSON (frontend espera JSON no cadastro via AJAX)
echo json_encode([
    'status' => 'ok',
    'message' => 'Produto cadastrado com sucesso!',
    'produto' => [
        'id_produto' => $id_produto,
        'nome' => $nome,
        'foto_principal' => $foto_db,
        'preco' => number_format($preco, 2, '.', '')
    ]
]);
exit;
