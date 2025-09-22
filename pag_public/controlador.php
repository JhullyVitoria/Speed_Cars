<?php
require "../conexaoMysql.php"; 
header("Content-Type: application/json; charset=UTF-8");

$pdo = mysqlConnect();
$acao = $_GET['acao'] ?? '';

try {
    switch ($acao) {
        case 'buscarDetalhesPublico':
            $idAnuncio = $_GET['id'] ?? '';
            if (!$idAnuncio) {
                http_response_code(400); 
                throw new Exception("ID do anúncio não fornecido.");
            }

            $sqlAnuncio = "SELECT * FROM Anuncio WHERE Id = ?";
            $stmtAnuncio = $pdo->prepare($sqlAnuncio);
            $stmtAnuncio->execute([$idAnuncio]);
            $anuncio = $stmtAnuncio->fetch(PDO::FETCH_ASSOC);

            if (!$anuncio) {
                http_response_code(404); 
                throw new Exception("Anúncio não encontrado.");
            }

            $sqlFotos = "SELECT NomeArqFoto FROM Foto WHERE IdAnuncio = ?";
            $stmtFotos = $pdo->prepare($sqlFotos);
            $stmtFotos->execute([$idAnuncio]);
            $anuncio['fotos'] = $stmtFotos->fetchAll(PDO::FETCH_COLUMN);

            echo json_encode($anuncio);
            break;

        case 'registrarInteresse':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                throw new Exception("Método não permitido.");
            }

            $nome = $_POST['nome'] ?? '';
            $telefone = $_POST['telefone'] ?? '';
            $mensagem = $_POST['mensagem'] ?? '';
            $idAnuncio = $_POST['idAnuncio'] ?? 0;

            if (empty($nome) || empty($telefone) || empty($mensagem) || empty($idAnuncio)) {
                http_response_code(400);
                throw new Exception("Todos os campos são obrigatórios.");
            }

            $sql = <<<SQL
                INSERT INTO Interesse (Nome, Telefone, Mensagem, IdAnuncio)
                VALUES (?, ?, ?, ?)
            SQL;

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome, $telefone, $mensagem, $idAnuncio]);

            echo json_encode(["status" => "ok", "mensagem" => "Interesse registado com sucesso! Entraremos em contacto em breve."]);
            break;

        default:
            http_response_code(400);
            throw new Exception("Ação inválida.");
    }
} catch (Exception $e) {
    if (http_response_code() === 200) {
        http_response_code(500);
    }
    echo json_encode(["erro" => $e->getMessage()]);
}