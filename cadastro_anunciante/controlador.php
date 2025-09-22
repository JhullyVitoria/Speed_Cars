<?php

require "../conexaoMysql.php";
require "anunciante.php";

$acao = $_GET['acao'];

$pdo = mysqlConnect();

switch ($acao){

    case "adicionarAnunciante":
        $nome = $_POST["nome"] ?? "";
        $cpf = $_POST["cpf"] ?? "";
        $email = $_POST["email"] ?? "";
        $senha = $_POST["senha"] ?? "";
        $telefone = $_POST["telefone"] ?? "";

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        try {
            Anunciante::Create($pdo, $nome, $cpf, $email, $senhaHash, $telefone);
            header("location:../login_anunciante/login.html");
        } catch (Exception $e){
            throw new Exception($e->getMessage());
        }
        break;

    default:
    exit("Ação não disponível");
}

