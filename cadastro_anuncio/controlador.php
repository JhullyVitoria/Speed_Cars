<?php
session_start();

require "../conexaoMysql.php";
require "anuncio.php";

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    http_response_code(401);
    echo json_encode(["erro" => "Acesso não autorizado. Por favor, faça login."]);
    exit();
}

$pdo = mysqlConnect();
$idAnuncianteLogado = $_SESSION['id_anunciante'];

function processaFotos(array $files): array
{
    $nomesFinais = [];
    $pastaDestino = "fotos/";

    if (!is_dir($pastaDestino)) {
        mkdir($pastaDestino, 0777, true);
    }

    $fotosReorganizadas = [];
    if (isset($files['name'])) {
        foreach ($files['name'] as $key => $name) {
            if ($files['error'][$key] === UPLOAD_ERR_OK) {
                $fotosReorganizadas[] = [
                    'name' => $name, 'type' => $files['type'][$key],
                    'tmp_name' => $files['tmp_name'][$key], 'size' => $files['size'][$key]
                ];
            }
        }
    }

    if (empty($fotosReorganizadas)) {
        throw new Exception("Nenhuma imagem válida foi enviada.");
    }

    foreach ($fotosReorganizadas as $foto) {
        if (!is_uploaded_file($foto['tmp_name'])) continue;
        $info = getimagesize($foto['tmp_name']);
        if ($info === false) throw new Exception("Arquivo '{$foto['name']}' não é uma imagem válida.");
        $allowedTypes = ['image/jpeg', 'image/png'];
        if (!in_array($info['mime'], $allowedTypes)) throw new Exception("Imagem '{$foto['name']}' deve ser JPEG ou PNG.");
        if ($foto['size'] > 5 * 1024 * 1024) throw new Exception("Imagem '{$foto['name']}' excede o limite de 5MB.");

        $extensao = pathinfo($foto['name'], PATHINFO_EXTENSION);
        $nomeUnico = uniqid('veiculo_', true) . '.' . strtolower($extensao);
        $caminhoCompleto = $pastaDestino . $nomeUnico;

        if (!move_uploaded_file($foto['tmp_name'], $caminhoCompleto)) {
            throw new Exception("Falha ao salvar a imagem '{$foto['name']}'.");
        }
        $nomesFinais[] = $nomeUnico;
    }
    return $nomesFinais;
}

header("Content-Type: application/json; charset=UTF-8");
$acao = $_REQUEST["acao"] ?? "";

try {
    switch ($acao) {
        case "cadastrar":
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(["erro" => "Método não permitido."]);
                exit();
            }

            $dadosAnuncio = [
                'marca' => trim($_POST['marca'] ?? ''), 'modelo' => trim($_POST['modelo'] ?? ''),
                'ano' => filter_input(INPUT_POST, 'ano', FILTER_VALIDATE_INT),
                'cor' => trim($_POST['cor'] ?? ''),
                'quilometragem' => filter_input(INPUT_POST, 'quilometragem', FILTER_VALIDATE_INT),
                'descricao' => trim($_POST['descricao'] ?? ''),
                'valor' => filter_input(INPUT_POST, 'valor', FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'estado' => trim($_POST['estado'] ?? ''), 'cidade' => trim($_POST['cidade'] ?? '')
            ];
            $nomesDasFotos = processaFotos($_FILES['foto_veiculo'] ?? []);
                        
            $idNovoAnuncio = Anuncio::Create($pdo, $idAnuncianteLogado, $dadosAnuncio, $nomesDasFotos);
            
            echo json_encode(["status" => "ok", "mensagem" => "Anúncio cadastrado com sucesso!", "idAnuncio" => $idNovoAnuncio]);
            break;

        case "listarAnuncios":
            $anuncios = Anuncio::GetByUser($pdo, $idAnuncianteLogado);
            echo json_encode($anuncios);
            break;

        case "buscarDetalhes":
            $idAnuncio = $_GET['id'] ?? '';
            
            $anuncio = Anuncio::GetDetails($pdo, $idAnuncio, $idAnuncianteLogado);

            if ($anuncio) {
                echo json_encode($anuncio);
            } else {
                http_response_code(404); 
                echo json_encode(["erro" => "Anúncio não encontrado."]);
            }
            break;

        case 'buscarInteresses':
            $idAnuncio = $_GET['id'] ?? '';

            if (!$idAnuncio) {
                http_response_code(400);
                throw new Exception("ID do anúncio não fornecido.");
            }

            $resultado = Anuncio::GetInteresses($pdo, $idAnuncio, $idAnuncianteLogado);

            if ($resultado) {
                echo json_encode($resultado);
            } else {
                http_response_code(404);
                echo json_encode(["erro" => "Anúncio não encontrado ou não pertence a você."]);
            }
            break;

        case "excluir":
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(["erro" => "Use o método POST para excluir."]);
                exit();
            }
            $idAnuncioParaExcluir = $_POST["id"] ?? '';

            $sucesso = Anuncio::Remove($pdo, $idAnuncioParaExcluir, $idAnuncianteLogado);

            if ($sucesso) {
                echo json_encode(["status" => "ok", "msg" => "Anúncio removido com sucesso."]);
            } else {
                http_response_code(403);
                echo json_encode(["erro" => "Ação não permitida. O anúncio não existe."]);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(["erro" => "Ação inválida ou não especificada."]);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(["erro" => "Ocorreu uma falha inesperada no servidor. Por favor, tente mais tarde."]);
}