<?php
// Este ficheiro não precisa de verificação de sessão, pois é público
$idAnuncio = $_GET['id'] ?? '';
if (!$idAnuncio) {
    // Se não houver ID na URL, redireciona para a página inicial
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Veículo</title>
    <link rel="stylesheet" href="../Estilos/pag_interesse.css"> 
    <style>
        .galeria {
            display: flex; 
            gap: 1rem;
            padding: 10px;
            margin: 5px 0px 5px 0px;
            color: rgb(70, 67, 67);
            border-radius: 20px;
            overflow-x: auto;
        }

        .galeria img {
            padding: 20px;
            border-radius: 5px;
            box-shadow: 3px 3px 3px 3px rgb(210, 84, 84);
            margin: 5px 5px 5px 5px;
            width: 200px;
            height: 150px;
        }
    </style>
</head>
<body>
    <header>
        <figure>
            <img src="../images/Logo_branca.png" alt="Logo da Empresa" width="100" height="85">
        </figure>
    </header>
    <nav>
        <a href="../index.php">Início</a>
    </nav>

    <main>
        <section id="detalhes-anuncio">
            <p>A carregar dados do veículo...</p>
        </section>

        <hr>

        <form id="form-interesse">
            <h1>Registar Interesse no Veículo</h1>
            <div id="form-mensagem"></div>
            <p><label>Nome: <input type="text" name="nome" required /></label></p>
            <p><label>Telefone: <input type="tel" name="telefone" required /></label></p>
            <p><label>Mensagem: <textarea name="mensagem" rows="5" required></textarea></label></p>
            <button type="submit">Enviar Interesse</button>
        </form>
    </main>

    <footer>
        <address>
            Av. Princesa Isabel 786 , Fundinho Elite - Uberlândia MG
        </address>
        Copyright © 2025 Speed Cars Company - Todos os direitos reservados
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", async function() {
            const detalhesContainer = document.getElementById('detalhes-anuncio');
            const formInteresse = document.getElementById('form-interesse');
            const divMensagem = document.getElementById('form-mensagem');

            const params = new URLSearchParams(window.location.search);
            const idAnuncio = params.get('id');

            async function carregaDetalhes() {
                try {
                    const response = await fetch(`controlador.php?acao=buscarDetalhesPublico&id=${idAnuncio}`);
                    const anuncio = await response.json();
                    if (!response.ok) throw new Error(anuncio.erro);

                    document.title = `${anuncio.Marca} ${anuncio.Modelo}`;
                    let fotosHtml = anuncio.fotos.map(foto => `<img src="../cadastro_anuncio/fotos/${foto}" alt="Foto de ${anuncio.Modelo}">`).join('');
                    
                    detalhesContainer.innerHTML = `
                        <h1>${anuncio.Marca} ${anuncio.Modelo}</h1>
                        <div class="galeria">${fotosHtml}</div>
                        <p><strong>Ano:</strong> ${anuncio.Ano}</p>
                        <p><strong>Valor:</strong> R$ ${parseFloat(anuncio.Valor).toFixed(2)}</p>
                        <p><strong>Descrição:</strong> ${anuncio.Descricao}</p>
                    `;
                } catch (e) {
                    detalhesContainer.innerHTML = `<p style="color:red;">${e.message}</p>`;
                }
            }

            function exibeMensagem(texto, tipo) {
                divMensagem.className = `mensagem ${tipo}`; 
                divMensagem.textContent = texto;
            }

            formInteresse.addEventListener("submit", async function (e) {
                e.preventDefault();
                const formData = new FormData(formInteresse);
                formData.append('idAnuncio', idAnuncio); 

                try {
                    const response = await fetch('controlador.php?acao=registrarInteresse', {
                        method: 'POST',
                        body: formData
                    });
                    const resultado = await response.json();
                    if (!response.ok) throw new Error(resultado.erro);
                    
                    exibeMensagem(resultado.mensagem, 'sucesso');
                    formInteresse.reset();
                } catch (e) {
                    exibeMensagem(e.message, 'erro');
                }
            });

            carregaDetalhes();
        });
    </script>
</body>
</html>