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
    <title>Lista de Interessados</title>
    <link rel="stylesheet" href="../Estilos/lista_interesses.css">
</head>
<body>
    <header>
        <figure>
            <a href="../index.php"><img src="../images/Logo_branca.png" alt="Logo da Empresa" width="100" height="85"></a>
        </figure>
    </header>
    <nav>
        <a href="listagem_anuncios.php">Voltar para a Listagem</a>
    </nav>
    <main>
        <h1>Meus Anuncios</h1>
        <section id="anuncio-info" class="container2">
            </section>

        <h1>Lista de Interessados</h1>
        <section id="interessados-lista" class="container2">
            
        </section>
    </main>
    <footer>
        <address>
            Av. Princesa Isabel 786 , Fundinho Elite - Uberlândia MG
        </address>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", async function() {
            const anuncioContainer = document.getElementById('anuncio-info');
            const interessadosContainer = document.getElementById('interessados-lista');

            const params = new URLSearchParams(window.location.search);
            const idAnuncio = params.get('id');

            try {
                const response = await fetch(`controlador.php?acao=buscarInteresses&id=${idAnuncio}`);
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.erro || 'Falha ao buscar os dados.');
                }

                const anuncio = data.anuncio;
                document.title = `Interessados em ${anuncio.Modelo}`;
                anuncioContainer.innerHTML = `
                    <div class="card">
                        <div>
                            <h2>${anuncio.Marca} ${anuncio.Modelo}</h2>
                            <img src="fotos/${anuncio.Foto ?? 'sem-foto.png'}" alt="${anuncio.Modelo}" width="250px" height="150px">
                        </div>
                        <div>
                            <p><strong>Ano:</strong> ${anuncio.Ano}</p>
                            <p><strong>Cor:</strong> ${anuncio.Cor}</p>
                            <p><strong>Valor:</strong> R$ ${parseFloat(anuncio.Valor).toFixed(2)}</p>
                        </div>
                    </div>
                `;

                const interessados = data.interessados;

                if (interessados.length > 0) {
                    interessados.forEach(interessado => {
                        const cardInteressado = document.createElement('div');
                        cardInteressado.className = 'card2';
                        cardInteressado.innerHTML = `
                            <div>
                                <h2>${interessado.Nome}</h2>
                                <p><strong>Telefone:</strong> ${interessado.Telefone}</p>
                                <p><strong>Mensagem:</strong> ${interessado.Mensagem}</p>
                            </div>
                        `;
                        interessadosContainer.appendChild(cardInteressado);
                    });
                } else {
                    interessadosContainer.innerHTML = '<p>Ainda nao ha interessados neste anuncio.</p>';
                }

            } catch (error) {
                console.error('Erro:', error);
                interessadosContainer.innerHTML = `<p style="color: red;">${error.message}</p>`;
            }
        });
    </script>
</body>
</html>