<?php
session_start();
require "../login_anunciante/sessionVerification.php";
exitWhenNotLoggedIn();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listagem de Anúncios - Restrita</title>
    <link rel="Stylesheet" href="../Estilos/listagem_anuncios.css">
</head>
<body>
    <header>
        <img src="..\images\Logo_branca.png" alt="logo da empresa" width="100" height="85">
    </header>

    <nav>
        <a href="../login_anunciante/principal_restrita.php">Voltar</a>
    </nav>

    <main>
        <h1>Anúncios Criados</h1>
        <section class="container">
        </section>
    </main>

    <footer>
        <address>
            Av. Princesa Isabel 786 , Fundinho Elite - Uberlândia MG
        </address>
        Copyright © 2025 Speed Cars Company - Todos os direitos reservados
    </footer>

    <script>
        async function carregaAnuncios() {
            const container = document.querySelector(".container");
            try {
                const response = await fetch("controlador.php?acao=listarAnuncios");
                if (!response.ok) throw new Error("Falha ao carregar anúncios: " + response.statusText);
                
                const anuncios = await response.json();

                if (anuncios.length === 0) {
                    container.innerHTML = "<p>Você ainda não criou nenhum anúncio.</p>";
                    return;
                }

                for (const anuncio of anuncios) {
                    const card = document.createElement("div");
                    card.className = "card";
                    card.innerHTML = `
                        <h2>${anuncio.Modelo}</h2>
                        <img src="fotos/${anuncio.Foto ?? 'sem-foto.png'}" alt="${anuncio.Modelo}">
                        <p><strong>Marca:</strong> ${anuncio.Marca}</p>
                        <p><strong>Modelo:</strong> ${anuncio.Modelo}</p>
                        <p><strong>Ano:</strong> ${anuncio.Ano}</p>
                        <div>
                            <button onclick="window.location.href='detalhes.php?id=${anuncio.Id}'">Descrição detalhada</button>
                            <button onclick="window.location.href='lista_interesses.php?id=${anuncio.Id}'">Visualizações</button>
                            <button onclick="excluirAnuncio(${anuncio.Id})">Excluir</button>
                        </div>
                    `;
                    container.appendChild(card);
                }
            } catch (error) {
                console.error("Erro ao carregar anúncios:", error);
                container.innerHTML = "<p>Ocorreu um erro ao carregar seus anúncios.</p>";
            }
        }

        async function excluirAnuncio(id) {
            if (!confirm("Deseja realmente excluir este anúncio?")) return;
            
            try {
                const formData = new FormData();
                formData.append('id', id);

                const response = await fetch("controlador.php?acao=excluir", {
                    method: 'POST',
                    body: formData
                });

                const resultado = await response.json();
                if (!response.ok) throw new Error(resultado.erro);

                carregaAnuncios(); 
            } catch (error) {
                alert("Falha ao excluir: " + error.message);
            }
        }

        carregaAnuncios();
    </script>
</body>
</html>