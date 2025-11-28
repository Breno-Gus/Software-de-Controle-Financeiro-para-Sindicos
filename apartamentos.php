<?php
include 'php/database/conecte.php';
include 'php/crud/crudregistro.php';
include_once 'php/crud/crudapartamentos.php';

session_start();
if (!isset($_SESSION['email-logado'])) {
    header("Location: index.html");
}

$isAdmin = ($_SESSION['email-logado'] === 'admin');
$apartamentos = getApartamentosWithDetails($conn);
$predios = fetchPrediosApartamentos($conn);
$moradores = fetchMoradores($conn);

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
                <div class="janela despesas"><span class="text-window">Despesas</span></div>
                <div class="janela sindico"><span class="text-window">Síndicos</span></div>
                <div class="janela contatos"><span class="text-window">Contatos</span></div>
            </div>
        <?php else: ?>
            <div class="barra"></div>
            <div class="aba">
                <div class="janela morador">
                    <span class="text-window">Moradores</span>
                </div>
                <div class="janela despesas">
                    <span class="text-window">Despesas</span>
                </div>
                <div class="janela contatos">
                    <span class="text-window">Contatos</span>
                </div>
            </div>
        <?php endif; ?>
    </header>

    <article>
        <div id="popup-apartamentos" class="popup">
            <div class="popup-content">
                <span class="close">&times;</span>
                <h2>Adicionar Apartamento</h2>
                <form id="addApartamentosForm">
                    <select id="idPredio" required>
                        <option value="">Selecione um Prédio</option>
                        <?php foreach ($predios as $predio): ?>
                            <option value="<?= htmlspecialchars($predio['id_predio']) ?>">
                                <?= htmlspecialchars($predio['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select id="idMorador" required>
                        <option value="">Selecione um Morador</option>
                        <?php foreach ($moradores as $morador): ?>
                            <option value="<?= htmlspecialchars($morador['id_morador']) ?>">
                                <?= htmlspecialchars($morador['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" id="numero" placeholder="Número do Apartamento" required>
                    <input type="text" id="andar" placeholder="Andar" required>
                    <button type="submit">Adicionar</button>
                </form>
            </div>
        </div>

        <div id="popupEdita-apartamentos" class="popup">
            <div class="popup-content-Edita">
                <span class="close">&times;</span>
                <h2>Adicionar Apartamento</h2>
                <form id="editApartamentosForm">
                    <input type="hidden" id="editIdApartamentos">
                    <select id="editIdPredio" required>
                        <option value="">Selecione um Prédio</option>
                        <?php foreach ($predios as $predio): ?>
                            <option value="<?= htmlspecialchars($predio['id_predio']) ?>">
                                <?= htmlspecialchars($predio['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select id="editIdMorador" required>
                        <option value="">Selecione um Morador</option>
                        <?php foreach ($moradores as $morador): ?>
                            <option value="<?= htmlspecialchars($morador['id_morador']) ?>">
                                <?= htmlspecialchars($morador['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" id="editNumero" placeholder="Número do Apartamento" required>
                    <input type="text" id="editAndar" placeholder="Andar" required>
                    <button type="submit">Adicionar</button>
                </form>
            </div>
        </div>

        <div class="content">
            <div class="title-container">
                <p class="title-table">TABELA DE APARTAMENTOS</p>
                <button class="apartamentos-adicionar adicionar"><span
                        class="material-symbols-outlined">add</span></button>
            </div>
            <table id="table">
                <tr>
                    <th class="sem-estilo">DETALHES</th>
                    <th>ID</th>
                    <th>NÚMERO</th>
                    <th>ANDAR</th>
                    <th>MORADOR</th>
                    <th>PRÉDIO</th>
                    <th>ATIVO</th>
                    <th>EDITAR</th>
                    <th>ATIVAR</th>
                    <th>DESATIVAR</th>
                    <th>APAGAR</th>
                </tr>
                <?php
                $expandir = '<button class="apartamentos-expandir expandir"><span class="material-symbols-outlined">expand_all</span></button>';
                $editar = '<button class="apartamentos-editar editar"><span class="material-symbols-outlined">edit</span></button>';
                $apagar = '<button class="apartamentos-apagar apagar"><span class="material-symbols-outlined">delete</span></button>';
                $ativar = '<button class="apartamentos-check checkApartamentos"><span class="material-symbols-outlined">check</span></button>';
                $desativar = '<button class="apartamentos-block desativarApartamentos"><span class="material-symbols-outlined">block</span></button>';
                $iconcheck = '<span class="material-symbols-outlined">check</span>';
                $iconblock = '<span class="material-symbols-outlined">block</span>';
                foreach ($apartamentos as $apartamento) {
                    $ativoIcon = $apartamento['ativo'] === 'sim' ? $iconcheck : $iconblock;
                    echo "
                    <tr>
                        <td>$expandir</td>
                        <td>{$apartamento['id_apartamento']}</td>
                        <td>{$apartamento['numero']}</td>
                        <td>{$apartamento['andar']}</td>
                        <td class='id-morador' data-id='{$apartamento['id_morador']}'>{$apartamento['nome_morador']}</td>
                        <td class='id-predio' data-id='{$apartamento['id_predio']}'>{$apartamento['nome_predio']}</td>
                        <td>{$ativoIcon}</td>
                        <td>{$editar}</td>
                        <td>{$ativar}</td>
                        <td>{$desativar}</td>
                        <td>{$apagar}</td>
                    </tr>
                    <tr class='details-row' style='display: none;'>
                        <td colspan='11' class='details-container'>
                        </td>
                    </tr>";
                }
                ?>
            </table>
        </div>

        <div id="popuDespesas-apartamentos" class="popup">
            <div class="popup-contentDespesas">
                <span class="close">&times;</span>
                <h2>Adicionar Despesa</h2>
                <form id="addDespesa_ApartamentoForm">
                    <input type="hidden" id="idDespesa_Apartamento">
                    <input type="hidden" id="idApartamento">
                    <input type="text" id='nome' placeholder="Nome/Descrição" required>
                    <input type="text" id="valor-total" placeholder="R$ 0,00" required autocomplete="off">
                    <button type="submit">Criar</button>
                </form>
            </div>
        </div>

        <div id="EditpopuDespesas-apartamentos" class="popup">
            <div class="popup-contentDespesas">
                <span class="close">&times;</span>
                <h2>Editar Despesa</h2>
                <form id="editDespesa_ApartamentoForm">
                    <input type="hidden" id="editIdDespesa_Apartamento">
                    <input type="hidden" id="editIdApartamento">
                    <input type="text" id='editNome' placeholder="Nome/Descrição" required>
                    <input type="text" id="editValor-total" placeholder="R$ 0,00" required autocomplete="off">
                    <button type="submit">Criar</button>
                </form>
            </div>
        </div>

    </article>
    <footer>sistema de gerenciamento de condominio&copy;</footer>
    <script src="js/jquery/jquery-3.7.1.min.js"></script>
    <script src="js/crud.js"></script>
    <script src="js/botoes.js"></script>
    <script>
        $(document).ready(function () {
            setupTableActions('apartamentos');

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

            $(".apartamentos-expandir").click(function () {
                event.preventDefault();
                const parentRow = $(this).closest('tr');
                const detailsRow = parentRow.next("tr.details-row");
                const idApartamento = parentRow.children("td").eq(1).text();
                $('#idApartamento').val(idApartamento);
                $('#editIdApartamento').val(idApartamento || '');
                if (detailsRow.is(":visible")) {
                    detailsRow.hide();
                } else {
                    $.ajax({
                        url: "php/crud/apartamento_despesas.php",
                        method: "POST",
                        data: { id_apartamento: idApartamento },
                        dataType: "json",
                        success: function (data) {
                            const detailsContainer = detailsRow.find(".details-container");
                            if (data.status === "success" && data.despesas && data.despesas.length > 0) {
                                $('#idDespesa_Apartamento').val(data.id_apartamento_despesas);
                                let despesasRows = data.despesas.map(despesa => `
                        <tr>
                            <td>${despesa.id_apartamento_despesas}</td>
                            <td>${despesa.nome}</td>
                            <td>R$${despesa.valor_singular}</td>
                            <td>${despesa.foi_pago}</td>
                            <td><button class="editar-apartamento-despesa editar" data-id="${despesa.id}"><span class="material-symbols-outlined">edit</span></button></td>
                            <td><button class="pagar-apartamento-despesa check" data-id="${despesa.id}"><span class="material-symbols-outlined">check</span></button></td>
                            <td><button class="despagar-apartamento-despesa block" data-id="${despesa.id}"><span class="material-symbols-outlined">block</span></button></td>
                            <td><button class="excluir-apartamento-despesa apagar" data-id="${despesa.id}"><span class="material-symbols-outlined">delete</span></button></td>
                        </tr>
                    `).join('');
                                detailsContainer.html(`
                        <table class="details-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Valor Singular</th>
                                    <th>Pago?</th>
                                    <th>Editar</th>
                                    <th>Pagar</th>
                                    <th>Retirar pago</th>
                                    <th>Excluir</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${despesasRows}
                            </tbody>
                        </table>
                        <button class="add-despesa adicionar2 adicionar" data-id="${idApartamento}"><span
                        class="material-symbols-outlined">add</span></button>
                    `);
                            } else {
                                detailsContainer.html(`
                        <p>Não há despesas cadastradas para este apartamento.</p>
                        <button class="add-despesa adicionar2 adicionar" data-id="${idApartamento}"><span
                        class="material-symbols-outlined">add</span></button>
                    `);
                            }
                            detailsRow.show();
                        },
                        error: function () {
                            console.error("Erro ao carregar despesas.");
                        }
                    });
                }
            });

            $('#addDespesa_ApartamentoForm, #editDespesa_ApartamentoForm').on('keydown', function (event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                }
            });

            $(document).on('click', '.editar-apartamento-despesa', function () {
                const parentRow = $(this).closest('tr');
                const detailsRow = parentRow.next("tr.details-row");
                const idApartamento = parentRow.children("td").eq(0).text();
                $('#editIdDespesa_Apartamento').val(idApartamento);
                const row = $(this).closest('tr');
                $("#editNome").val(row.find('td').eq(1).text());
                const valorTotal = row.find('td').eq(2).text().replace("R$ ", "").trim();
                $("#editValor-total").val(valorTotal);
                $('#EditpopuDespesas-apartamentos').show();
            });

            $(document).on('click', '.add-despesa', function () {
                $('#popuDespesas-apartamentos').show();
            });

            $(document).on('click', '.excluir-apartamento-despesa', function () {
                hideAllPopups();
                const id = $(this).closest('tr').find('td').eq(0).text();
                if (confirm("Deseja realmente deletar esta despesa?")) {
                    submitForm('despesa_apartamento', { id }, 'DELETE');
                }
            });

            $(document).on('click', '.pagar-apartamento-despesa', function () {
                hideAllPopups();
                const id = $(this).closest('tr').find('td').eq(0).text();
                if (confirm("Deseja realmente pagar esta despesa?")) {
                    submitForm('despesa_apartamento', { id }, 'CHECK');
                }
            });

            $(document).on('click', '.despagar-apartamento-despesa', function () {
                hideAllPopups();
                const id = $(this).closest('tr').find('td').eq(0).text();
                if (confirm("Deseja realmente retirar do pago esta despesa?")) {
                    submitForm('despesa_apartamento', { id }, 'DESATIVE');
                }
            });

            $(".close").click(function () {
                $(".popup").hide();
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

        });

    </script>
</body>

</html>