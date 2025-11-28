<?php
include 'php/database/conecte.php';
include 'php/crud/crudregistro.php';
include_once 'php/crud/cruddespesa.php';

session_start();
if (!isset($_SESSION['email-logado'])) {
    header("Location: index.html");
}

$isAdmin = ($_SESSION['email-logado'] === 'admin');
$predios = fetchPrediosDespesas($conn);
$contatos = fetchContatos($conn);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-Vindo!</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/system.css">
    <link rel="stylesheet" href="css/popup.css">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,1,0" />
    <script>
        function formatInput(event, input) {
            input.value = input.value.replace(/\D/g, '');
            if (input.id === 'cep' || input.id.startsWith('editCep')) {
                if (input.value.length > 8) {
                    input.value = input.value.slice(0, 8);
                }
                input.value = input.value.replace(/(\d{5})(\d)/, '$1-$2');
            }
        }
    </script>
</head>

<body>
    <header>
        <div class="header">
            <h1>Bem-Vindo!</h1>
            <button id="logoutBtn"><span class="material-symbols-outlined">logout</span></button>
        </div>
        <?php if ($isAdmin): ?>
            <div class="barra"></div>
            <div class="aba">
                <div class="janela morador"><span class="text-window">Moradores</span></div>
                <div class="janela predios"><span class="text-window">Prédios</span></div>
                <div class="janela apartamentos"><span class="text-window">Apartamentos</span></div>
                <div class="janela sindico"><span class="text-window">Síndicos</span></div>
                <div class="janela contatos"><span class="text-window">Contatos</span></div>
            </div>
        <?php else: ?>
            <div class="barra"></div>
            <div class="aba">
                <div class="janela morador">
                    <span class="text-window">Moradores</span>
                </div>
                <div class="janela apartamentos">
                    <span class="text-window">Apartamentos</span>
                </div>
                <div class="janela contatos">
                    <span class="text-window">Contatos</span>
                </div>
            </div>
        <?php endif; ?>
    </header>

    <article>
        <div id="popup-despesas" class="popup">
            <div class="popup-content Pequeno">
                <span class="close">&times;</span>
                <h2>Adicionar uma nova despesa</h2>
                <form id="addDespesasForm">
                    <select id="idPredio" required>
                        <option value="">Selecione um Prédio</option>
                        <?php foreach ($predios as $predio): ?>
                            <option value="<?= htmlspecialchars($predio['id_predio']) ?>">
                                <?= htmlspecialchars($predio['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select id="idContato">
                        <option value="">Selecione um Contato</option>
                        <?php foreach ($contatos as $contato): ?>
                            <option value="<?= htmlspecialchars($contato['id_contato']) ?>">
                                <?= htmlspecialchars($contato['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" id="nome" placeholder="Nome" maxlength="21" required autocomplete="off">
                    <input type="text" id="valor-total" placeholder="R$ 0,00" required autocomplete="off">
                    <input type="date" id="vencimento" placeholder="Data de Vencimento" required autocomplete="off"
                        min="2023-01-01" max="2034-12-31">
                    <button type="submit">Adicionar</button>
                </form>
            </div>
        </div>

        <div id="popupEdita-despesas" class="popupEdita">
            <div class="popup-content-Edita Pequeno">
                <span class="close">&times;</span>
                <h2>Editar Despesas</h2>
                <form id="editDespesasForm">
                    <input type="hidden" id="editIdDespesas">
                    <select id="editIdPredio" required>
                        <option value="">Selecione um Prédio</option>
                        <?php foreach ($predios as $predio): ?>
                            <option value="<?= htmlspecialchars($predio['id_predio']) ?>">
                                <?= htmlspecialchars($predio['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select id="editIdContato">
                        <option value="">Selecione um Contato</option>
                        <?php foreach ($contatos as $contato): ?>
                            <option value="<?= htmlspecialchars($contato['id_contato']) ?>">
                                <?= htmlspecialchars($contato['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" id="editNome" placeholder="Nome" maxlength="21" required autocomplete="off">
                    <input type="text" id="editValor-total" placeholder="R$ 0,00" required autocomplete="off">
                    <input type="date" id="editVencimento" placeholder="Data de Vencimento" required autocomplete="off"
                        min="2023-01-01" max="2034-12-31">
                    <button type="submit">Adicionar</button>
                </form>
            </div>
        </div>

        <div class="content">
            <div class="title-container">
                <button class="apartamentos-relatorio relatorio"><span
                        class="material-symbols-outlined">docs</span></button>
                <p class="title-table">TABELA DE DESPESAS</p>
                <button class="despesas-adicionar adicionar"><span class="material-symbols-outlined">add</span></button>
            </div>
            <table id="table">
                <tr>
                    <th>ID</th>
                    <th>DESPESA</th>
                    <th>FOI PAGO?</th>
                    <th>PRÉDIO</th>
                    <th>CONTATO</th>
                    <th>VALOR-TOTAL</th>
                    <th>VENCIMENTO</th>
                    <th>EDITAR</th>
                    <th>RETIRAR PAGO</th>
                    <th>PAGAR</th>
                    <th>APAGAR</th>
                </tr>
                <?php
                $editar = '<button class="despesas-editar editar"><span class="material-symbols-outlined">edit</span></button>';
                $apagar = '<button class="despesas-apagar apagar"><span class="material-symbols-outlined">delete</span></button>';
                $desativar = '<button class="despesas-block desativarDespesas"><span class="material-symbols-outlined">block</span></button>';
                $ativar = '<button class="despesas-check checkDespesas"><span class="material-symbols-outlined">check</span></button>';
                $iconcheck = '<span class="material-symbols-outlined">check</span>';
                $iconblock = '<span class="material-symbols-outlined">block</span>';
                $rows = getDespesasWithDetails($conn);
                foreach ($rows as $row) {
                    if ($row['foi_pago'] === 'sim') {
                        echo "<tr>
                        <td>{$row['id_despesa']}</td>
                        <td>{$row['nome']}</td>
                        <td>{$iconcheck}</td>
                        <td class='id-predio' data-id='{$row['id_predio']}'>{$row['nome_predio']}</td>
                        <td class='id-contato' data-id='{$row['id_contato']}'>{$row['nome_contato']}</td>
                        <td>R$ {$row['valor_total']}</td>
                        <td>{$row['vencimento']}</td>
                        <td>{$editar}</td>
                        <td>{$desativar}</td>
                        <td>{$ativar}</td>
                        <td>{$apagar}</td>
                    </tr>";
                    } else if ($row['foi_pago'] === 'não') {
                        echo "<tr>
                        <td>{$row['id_despesa']}</td>
                        <td>{$row['nome']}</td>
                        <td>{$iconblock}</td>
                        <td class='id-predio' data-id='{$row['id_predio']}'>{$row['nome_predio']}</td>
                        <td class='id-contato' data-id='{$row['id_contato']}'>{$row['nome_contato']}</td>
                        <td>R$ {$row['valor_total']}</td>
                        <td>{$row['vencimento']}</td>
                        <td>{$editar}</td>
                        <td>{$desativar}</td>
                        <td>{$ativar}</td>
                        <td>{$apagar}</td>
                    </tr>";
                    }
                }
                ?>
            </table>
        </div>
        <div id="relatorio" class="popup">
            <div class="popup-content-relatorio Pequeninho">
                <span class="close">&times;</span>
                <h2>Gerar PDF</h2>
                <form id="formRelatorio">
                    <input type="text" placeholder="Inflação" id="inflacao">
                    <select id="predio" required>
                        <option value="">Selecione um Prédio</option>
                        <?php foreach ($predios as $predio): ?>
                            <option value="<?= htmlspecialchars($predio['id_predio']) ?>">
                                <?= htmlspecialchars($predio['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select id="anoPesquisa" required>
                        <option value="">Selecione um Ano</option>
                        <?php for ($e = 2023; $e <= 2035; $e++): ?>
                            <option value="<?= htmlspecialchars($e) ?>">
                                <?= htmlspecialchars($e) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <select id="mesPesquisa">
                        <option value="">Selecione um Mês (Opicional)</option>
                        <option value="1">Janeiro</option>
                        <option value="2">Fevereiro</option>
                        <option value="3">Março</option>
                        <option value="4">Abril</option>
                        <option value="5">Maio</option>
                        <option value="6">Junho</option>
                        <option value="7">Julho</option>
                        <option value="8">Agosto</option>
                        <option value="9">Setembro</option>
                        <option value="10">Outubro</option>
                        <option value="11">Novembro</option>
                        <option value="12">Dezembro</option>
                    </select>
                    <button type="submit">Criar</button>
                </form>
            </div>
    </article>
    <footer>sistema de gerenciamento de condominio&copy;</footer>
    <script src="js/jquery/jquery-3.7.1.min.js"></script>
    <script src="js/crud.js"></script>
    <script src="js/botoes.js"></script>
    <script src="js/relatorio.js"></script>
    <script>
        $(document).ready(function () {
            setupTableActions('despesas');

            $('#logoutBtn').on('click', function () {
                if (confirm("Tem certeza que deseja encerrar a sessão?")) {
                    fetch('logout.php', { method: 'POST' })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === "success") {
                                alert(data.message);
                                window.location.href = "index.html";
                            } else {
                                alert("Erro ao encerrar sessão.");
                            }
                        })
                        .catch(error => console.error("Erro:", error));
                }
            });

            function formatCurrency(value) {
                value = value.replace(/\D/g, '');

                value = value.slice(0, -2) + ',' + value.slice(-2);
                return value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
            }

            $('#editValor-total, #valor-total').on('input', function () {
                const input = $(this);
                let rawValue = input.val().replace(/\D/g, '');
                input.val(rawValue ? formatCurrency(rawValue) : '');
            });

            $('#editValor-total, #valor-total').on('blur', function () {
                const input = $(this);
                let rawValue = input.val().replace(/\D/g, '');
                if (rawValue === '') {
                    input.val('');
                } else {
                    input.val('R$ ' + formatCurrency(rawValue));
                }
            });

            $('#inflacao').on('input', function () {
                const input = $(this);
                let rawValue = input.val()
                    .replace('%', '')
                    .replace(/[^0-9.,]/g, '')
                    .replace(',', '.');

                if (parseFloat(rawValue) > 100) {
                    rawValue = 100;
                }

                input.val(rawValue);
            });

            $('#inflacao').on('blur', function () {
                const input = $(this);
                let rawValue = input.val()
                    .replace('%', '')
                    .replace(',', '.');

                if (!rawValue || isNaN(rawValue)) {
                    rawValue = '0.00';
                }

                if (parseFloat(rawValue) > 100) {
                    rawValue = 100;
                }

                input.val(parseFloat(rawValue).toFixed(2) + '%');
            });

        });

    </script>
</body>

</html>