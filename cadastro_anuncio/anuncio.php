<?php
class Anuncio
{
  static function Create(
    $pdo,
    $idAnunciante,
    $dadosAnuncio,
    $nomesFotos
  ) {
    if (empty($nomesFotos)) {
        throw new Exception("É necessário enviar pelo menos uma foto do veículo.");
    }
      
    try {
      $pdo->beginTransaction();

      $sqlAnuncio = <<<SQL
        INSERT INTO Anuncio (IdAnunciante, Marca, Modelo, Ano, Cor, Quilometragem, Descricao, Valor, Estado, Cidade) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
      SQL;
      
      $stmtAnuncio = $pdo->prepare($sqlAnuncio);
      $stmtAnuncio->execute([
        $idAnunciante,
        $dadosAnuncio['marca'],
        $dadosAnuncio['modelo'],
        $dadosAnuncio['ano'],
        $dadosAnuncio['cor'],
        $dadosAnuncio['quilometragem'],
        $dadosAnuncio['descricao'],
        $dadosAnuncio['valor'],
        $dadosAnuncio['estado'],
        $dadosAnuncio['cidade']
      ]);

      $idAnuncio = $pdo->lastInsertId();

      $sqlFoto = "INSERT INTO Foto (IdAnuncio, NomeArqFoto) VALUES (?, ?)";
      $stmtFoto = $pdo->prepare($sqlFoto);

      foreach ($nomesFotos as $nomeFoto) {
        $stmtFoto->execute([$idAnuncio, $nomeFoto]);
      }

      $pdo->commit();

      return $idAnuncio;

    } catch (Exception $e) {
      $pdo->rollBack();
      throw $e;
    }
  }
  
  static function GetByUser($pdo, $idAnunciante): array
  {
    $sql = <<<SQL
      SELECT 
        a.Id, a.Modelo, a.Marca, a.Ano, a.Cor, a.Quilometragem, a.Valor, a.Cidade, a.Estado,
        (SELECT f.NomeArqFoto FROM Foto f WHERE f.IdAnuncio = a.Id LIMIT 1) AS Foto
      FROM Anuncio a
      WHERE a.IdAnunciante = ?
      ORDER BY a.DataHora DESC
    SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idAnunciante]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  static function Remove($pdo, $id, $idAnunciante): bool
  {
    $sql = "DELETE FROM Anuncio WHERE Id = ? AND IdAnunciante = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id, $idAnunciante]);
    return $stmt->rowCount() > 0;
  }

  static function GetDetails($pdo, $id, $idAnunciante): array
  {
    $sqlAnuncio = "SELECT * FROM Anuncio WHERE Id = ? AND IdAnunciante = ?";
    $stmtAnuncio = $pdo->prepare($sqlAnuncio);
    $stmtAnuncio->execute([$id, $idAnunciante]);
    
    $anuncio = $stmtAnuncio->fetch(PDO::FETCH_ASSOC);
    if ($anuncio === false) {
      return null;
    }

    $sqlFotos = "SELECT NomeArqFoto FROM Foto WHERE IdAnuncio = ?";
    $stmtFotos = $pdo->prepare($sqlFotos);
    $stmtFotos->execute([$id]);

    $anuncio['fotos'] = $stmtFotos->fetchAll(PDO::FETCH_COLUMN);

    return $anuncio;
  }

  static function GetInteresses($pdo, $id, $idAnunciante): array
    {
        $sqlAnuncio = <<<SQL
            SELECT Id, Marca, Modelo, Ano, Cor, Valor, 
                   (SELECT F.NomeArqFoto FROM Foto F WHERE F.IdAnuncio = A.Id LIMIT 1) AS Foto
            FROM Anuncio AS A
            WHERE A.Id = ? AND A.IdAnunciante = ?
        SQL;

        $stmtAnuncio = $pdo->prepare($sqlAnuncio);
        $stmtAnuncio->execute([$id, $idAnunciante]);
        $anuncio = $stmtAnuncio->fetch(PDO::FETCH_ASSOC);

        if ($anuncio === false) {
            return null;
        }

        $sqlInteresses = "SELECT Nome, Telefone, Mensagem FROM Interesse WHERE IdAnuncio = ?";
        $stmtInteresses = $pdo->prepare($sqlInteresses);
        $stmtInteresses->execute([$id]);
        $interessados = $stmtInteresses->fetchAll(PDO::FETCH_ASSOC);

        return ['anuncio' => $anuncio, 'interessados' => $interessados
        ];
    }
}