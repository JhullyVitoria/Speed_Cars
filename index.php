<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Principal - Speed Cars</title>
    <link rel="stylesheet" href="Estilos/index.css">
</head>

<body>
    <header>
        <figure>
            <a href="index.php"><img src="images/Logo.png" alt="Logo da Empresa" width="100" height="85"></a>
        </figure>
    </header>
    <main>
        <nav>
            <a href="cadastro_anunciante/cadastro.html">Cadastrar</a>
            <a href="login_anunciante/login.html">Login</a>
        </nav>
        <hr>
	<section>
            <div class="back_img"></div>
        </section>
        <div class="filter">
            <div>
                <p>Busque seu carro:</p>
                <select name="busca_marca" id="busca_marca">
                    <option value="">Marca</option>
                </select>
                <select name="busca_modelo" id="busca_modelo" disabled>
                    <option value="">Modelo</option>
                </select>
                <select name="localizacao" id="localizacao" disabled>
                    <option value="">Cidade</option>
                </select>
            </div>
        </div>
        <section class="container" id="anuncios-container">
        </section>

    </main>
    <footer>
        <figure>
            <img src="images/Logo.png" alt="Logo" width="300">
        </figure>
        <address>
            <p>Av. Princesa Isabel 786 , Fundinho Elite - Uberlândia MG</p>
            <p>Copyright © 2025 Speed Cars Company - Todos os direitos reservados</p>
        </address>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectMarca = document.getElementById('busca_marca');
            const selectModelo = document.getElementById('busca_modelo');
            const selectCidade = document.getElementById('localizacao');
            const anunciosContainer = document.getElementById('anuncios-container');
            const controladorURL = 'controlador.php';

            // Função genérica para buscar dados da API
            async function buscarDados(acao, params = {}) {
                const urlParams = new URLSearchParams(params);
                try {
                    const response = await fetch(`${controladorURL}?acao=${acao}&${urlParams.toString()}`);
                    if (!response.ok) throw new Error('Erro de rede: ' + response.statusText);
                    return await response.json();
                } catch (error) {
                    console.error(`Falha ao buscar ${acao}:`, error);
                    return []; // Retorna um array vazio em caso de erro
                }
            }

            // Função para preencher um <select> com opções
            function preencherSelect(selectElement, dados, textoPadrao) {
                selectElement.innerHTML = `<option value="">${textoPadrao}</option>`; // Limpa e adiciona a opção padrão
                dados.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item;
                    option.textContent = item;
                    selectElement.appendChild(option);
                });
                selectElement.disabled = dados.length === 0;
            }

            // Função para carregar e exibir os anúncios
            async function carregarAnuncios() {
                const params = {
                    marca: selectMarca.value,
                    modelo: selectModelo.value,
                    cidade: selectCidade.value,
                };

                const anuncios = await buscarDados('filtrarAnuncios', params);
                anunciosContainer.innerHTML = ''; // Limpa os anúncios existentes

                if (anuncios.length === 0) {
                    anunciosContainer.innerHTML = "<p>Nenhum anúncio encontrado com os filtros selecionados.</p>";
                } else {
                    anuncios.forEach(anuncio => {
                        const valorFormatado = parseFloat(anuncio.Valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                        const cardLink = document.createElement('a');
                        cardLink.href = `pag_public/anuncio-publico.php?id=${anuncio.Id}`;
                        cardLink.innerHTML = `
                            <div class="card">
                                <h2>${anuncio.Marca} ${anuncio.Modelo}</h2>
                                <img src="cadastro_anuncio/fotos/${anuncio.Foto ?? 'sem-foto.png'}" alt="${anuncio.Modelo}">
                                <p>Marca: ${anuncio.Marca}</p>
                                <p>Modelo: ${anuncio.Modelo}</p>
                                <p>Ano: ${anuncio.Ano}</p>
                                <p>Cidade: ${anuncio.Cidade} - ${anuncio.Estado}</p>
                                <p><span>Valor: ${valorFormatado}</span></p>
                            </div>
                        `;
                        anunciosContainer.appendChild(cardLink);
                    });
                }
            }

            // Event Listeners para os filtros
            selectMarca.addEventListener('change', async function () {
                const marcaSelecionada = this.value;
                selectModelo.disabled = true;
                selectCidade.disabled = true;
                
                if (marcaSelecionada) {
                    const modelos = await buscarDados('listarModelos', { marca: marcaSelecionada });
                    preencherSelect(selectModelo, modelos, 'Modelo');
                } else {
                    preencherSelect(selectModelo, [], 'Modelo');
                }
                preencherSelect(selectCidade, [], 'Cidade');
                carregarAnuncios();
            });

            selectModelo.addEventListener('change', async function () {
                const marcaSelecionada = selectMarca.value;
                const modeloSelecionado = this.value;
                selectCidade.disabled = true;

                if (marcaSelecionada && modeloSelecionado) {
                    const cidades = await buscarDados('listarCidades', { marca: marcaSelecionada, modelo: modeloSelecionado });
                    preencherSelect(selectCidade, cidades, 'Cidade');
                } else {
                    preencherSelect(selectCidade, [], 'Cidade');
                }
                carregarAnuncios();
            });

            selectCidade.addEventListener('change', carregarAnuncios);

            // Carregamento inicial
            async function inicializar() {
                const marcas = await buscarDados('listarMarcas');
                preencherSelect(selectMarca, marcas, 'Marca');
                carregarAnuncios();
            }

            inicializar();
        });
    </script>
</body>
</html>