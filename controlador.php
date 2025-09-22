<?php
header('Content-Type: application/json; charset=UTF-8');
require_once("conexaoMysql.php");

$pdo = mysqlConnect();
$acao = $_GET['acao'] ?? '';

try {
    switch ($acao) {
        case 'listarMarcas':
            // Lógica de busca_marcas.php
            $sql = "SELECT DISTINCT Marca FROM Anuncio ORDER BY Marca";
            $stmt = $pdo->query($sql);
            $marcas = $stmt->fetchAll(PDO::FETCH_COLUMN, 0); // FETCH_COLUMN é mais direto
            echo json_encode($marcas);
            break;

        case 'listarModelos':
            // Lógica de busca_modelos.php
            $marca = $_GET['marca'] ?? '';
            $sql = "SELECT DISTINCT Modelo FROM Anuncio WHERE Marca = ? ORDER BY Modelo";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$marca]);
            $modelos = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
            echo json_encode($modelos);
            break;
            
        case 'listarCidades':
            // Lógica de busca_localizacoes.php
            $marca = $_GET['marca'] ?? '';
            $modelo = $_GET['modelo'] ?? '';
            // A sua lógica original não usava o modelo, mas esta versão sim
            $sql = "SELECT DISTINCT Cidade FROM Anuncio WHERE 1=1";
            $params = [];
            
            if ($marca) {
                $sql .= " AND Marca = ?";
                $params[] = $marca;
            }
            if ($modelo) {
                $sql .= " AND Modelo = ?";
                $params[] = $modelo;
            }
            $sql .= " ORDER BY Cidade";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $cidades = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
            echo json_encode($cidades);
            break;

        case 'filtrarAnuncios':
            // Lógica de busca_anuncios.php
            $marca = $_GET['marca'] ?? '';
            $modelo = $_GET['modelo'] ?? '';
            $cidade = $_GET['cidade'] ?? '';

            $where = [];
            $params = [];

            if ($marca) {
                $where[] = "A.Marca = ?";
                $params[] = $marca;
            }
            if ($modelo) {
                $where[] = "A.Modelo = ?";
                $params[] = $modelo;
            }
            if ($cidade) {
                $where[] = "A.Cidade = ?";
                $params[] = $cidade;
            }

            $sql = "SELECT 
                        A.Id, A.Marca, A.Modelo, A.Ano, A.Cidade, A.Estado, A.Valor,
                        (SELECT F.NomeArqFoto FROM Foto F WHERE F.IdAnuncio = A.Id LIMIT 1) AS Foto
                    FROM Anuncio AS A";

            if ($where) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }
            $sql .= " ORDER BY A.DataHora DESC LIMIT 20";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $anuncios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($anuncios);
            break;

        default:
            http_response_code(400); // Bad Request
            echo json_encode(['erro' => 'Ação não especificada ou inválida.']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['erro' => $e->getMessage()]);
}