<?php
require "../conexaoMysql.php";
require "sessionVerification.php";

session_start();
exitWhenNotLoggedIn();
$pdo = mysqlConnect();
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Estilos/principal_restrita.css">
    <title>Página Principal Interna (restrita)</title>
</head>

<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <img src="..\images\Logo_branca.png" alt="logo da empresa" width="100" height="85">
            </div>
            <a href="controlador.php?acao=logout">Logout</a>
        </div>
    </header>
    <nav>
        <a href="../cadastro_anuncio/anuncio.html">Criar novo anúncio</a>
        <a href="../cadastro_anuncio/listagem_anuncios.php">Acessar anúncios criados</a>
    </nav>
    <main>

    </main>
    <footer>
        <address>
            Av. Princesa Isabel 786 , Fundinho Elite - Uberlândia MG
        </address>
    </footer>
</body>

</html>