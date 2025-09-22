<?php

session_start();

require "../login_anunciante/sessionVerification.php";

exitWhenNotLoggedIn();



$idAnuncio = $_GET['id'] ?? '';

if (!$idAnuncio) {

    header("Location: listagem_anuncios.php");

    exit();

}

?>

<!DOCTYPE html>

<html lang="pt-BR">



<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detalhes do Veículo</title>

    <style>

        body {

            display: flex;

            flex-direction: column;

            min-height: 100vh;

            margin: 0;

            background-color: #ebe9e9;

            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;

        }



        header {

            background-color: #2B305C;

            display: flex;

            flex-direction: column;

            align-items: center;

            position: relative;

            padding: 10px;

        }



        nav {

            background-color: #2B305C;

            border-style: outset;

            display: flex;

            padding: 10px;

            justify-content: left;

            position: sticky;

        }



        nav>a {

            color: #ebe9e9;

            text-decoration: none;

        }



        main {

            flex: 1;

            display: flex;

            justify-content: center;

            align-items: center;

            margin: 20px 0;

        }



        h1 {

            color: rgb(168, 166, 166);

            text-align: center;

        }



        #detalhes-anuncio {

            display: flex;

            flex-direction: column;

            align-items: left;

            gap: 10px;

            background-color: rgba(20, 20, 20, 0.915);

            padding: 20px;

            border-radius: 20px;

            color: rgb(168, 166, 166);

            max-width: 700px;

            width: 100%;

        }



        .galeria {

            display: flex;

            gap: 1rem;

            padding: 10px;

            border-radius: 20px;

            overflow-x: auto;

            justify-content: center;

        }



        .galeria img {

            width: 200px;

            height: 150px;

            border-radius: 5px;

            box-shadow: 3px 3px 3px rgb(168, 166, 166);

        }



        p {

            margin: 5px 0;

        }



        footer {

            background-color: #2B305C;

            text-align: center;

            color: #f1ece2;

            padding: 1rem;

        }

    </style>

</head>



<body>

    <header>

        <img src="../images/Logo_branca.png" alt="Logo da Empresa" width="100" height="85">

    </header>

    <nav>

        <a href="listagem_anuncios.php">Voltar à Listagem</a>

    </nav>

    <main>

        <section id="detalhes-anuncio">

            <p>A carregar dados do veículo...</p>

        </section>

    </main>

    <footer>

        <address>

            Av. Princesa Isabel 786 , Fundinho Elite - Uberlândia MG

        </address>

        Copyright © 2025 Speed Cars Company - Todos os direitos reservados

    </footer>



    <script>

        document.addEventListener("DOMContentLoaded", async () => {

            const container = document.getElementById('detalhes-anuncio');

            const params = new URLSearchParams(window.location.search);

            const idAnuncio = params.get('id');



            if (!idAnuncio) {

                container.innerHTML = '<p>ID do anúncio não fornecido.</p>';

                return;

            }



            try {

                const response = await fetch(`controlador.php?acao=buscarDetalhes&id=${idAnuncio}`);

                const text = await response.text();

                let anuncio;



                try {

                    anuncio = JSON.parse(text);

                } catch (e) {

                    throw new Error('Resposta inválida do servidor:\n' + text);

                }



                if (!response.ok) {

                    throw new Error(anuncio.erro || 'Falha ao buscar detalhes do anúncio.');

                }



                document.title = `${anuncio.Marca} ${anuncio.Modelo}`;



                let fotosHtml = '<p>Sem fotos disponíveis.</p>';

                if (anuncio.fotos && anuncio.fotos.length > 0) {

                    fotosHtml = anuncio.fotos.map(foto =>

                        `<img src="../cadastro_anuncio/fotos/${foto}" alt="Foto de ${anuncio.Modelo}">`

                    ).join('');

                }



                container.innerHTML = `

                <h1>${anuncio.Marca} ${anuncio.Modelo}</h1>

                <div class="galeria">${fotosHtml}</div>

                <p><strong>Ano:</strong> ${anuncio.Ano}</p>

                <p><strong>Cor:</strong> ${anuncio.Cor || 'Não informado'}</p>

                <p><strong>Quilometragem:</strong> ${anuncio.Quilometragem ? anuncio.Quilometragem + ' km' : 'Não informado'}</p>

                <p><strong>Cidade/Estado:</strong> ${anuncio.Cidade} - ${anuncio.Estado}</p>

                <p><strong>Valor:</strong> R$ ${parseFloat(anuncio.Valor).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</p>

                <p><strong>Descrição:</strong> ${anuncio.Descricao || 'Sem descrição'}</p>

                `;

            } catch (err) {

                container.innerHTML = `<p style="color:red;">Erro ao carregar anúncio: ${err.message}</p>`;

                console.error(err);

            }

        });

    </script>

</body>



</html>