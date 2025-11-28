<?php
include 'php/database/conecte.php';
include 'php/crud/crudregistro.php';
include_once 'php/crud/crudcontatos.php';

session_start();
if (!isset($_SESSION['email-logado'])) {
    header("Location: index.html");
}

$isAdmin = ($_SESSION['email-logado'] === 'admin');
$contatos = listContatos($conn);
$predios = fetchPredios($conn);
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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,1,0"/>

    <script>
        function formatInput(event, input) {
            input.value = input.value.replace(/\D/g, '');

            if (input.id === 'cep' || input.id.startsWith('editCep')) {
                if (input.value.length > 8) {
                    input.value = input.value.slice(0, 8);
                }
                input.value = input.value.replace(/(\d{5})(\d)/, '$1-$2');
                input.value = input.value.replace(/(\d{3})(\d)/, '$1$2');
            }

            if (input.id === 'telefone' || input.id.startsWith('editTelefone')) {
                if (input.value.length > 11) {
                    input.value = input.value.slice(0, 11);
                }
                input.value = input.value.replace(/(\d{2})(\d)/, '($1) $2');
                input.value = input.value.replace(/(\d{5})(\d)/, '$1-$2');
            }
        }
    </script>

<body>
    <header>
        <div class="header">
            <h1>Bem-Vindo!</h1>
            <button id="logoutBtn"><span class="material-symbols-outlined">logout</span></button>
        </div>
        <?php if ($isAdmin): ?>
            <div class="barra"></div>
            <div class="aba">
                <div class="janela predios">
                    <span class="text-window">Prédios</span>
                </div>
                <div class="janela despesas">
                    <span class="text-window">Despesas</span>
                </div>
                <div class="janela apartamentos">
                    <span class="text-window">Apartamentos</span>
                </div>
                <div class="janela sindico">
                    <span class="text-window">Sindicos</span>
                </div>
                <div class="janela morador">
                    <span class="text-window">Moradores</span>
                </div>
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
                <div class="janela despesas">
                    <span class="text-window">Despesas</span>
                </div>
            </div>
        <?php endif; ?>
    </header>

    <article>
        <div id="popup-contatos" class="popup">
            <div class="popup-content Medio">
                <span class="close">&times;</span>
                <h2>Adicionar Novo Contato</h2>
                <form id="addContatosForm">
                    <input type="text" id="nome" placeholder="Primeiro Nome" maxlength="21" required autocomplete="off">
                    <select id="idPredio" required>
                        <option value="">Selecione um Prédio</option>
                        <?php foreach ($predios as $predio): ?>
                            <option value="<?= htmlspecialchars($predio['id_predio']) ?>">
                                <?= htmlspecialchars($predio['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" id="telefone" placeholder="Telefone" maxlength="15" required autocomplete="off"
                        oninput="formatInput(event, this)">
                    <input type="text" id="cep" placeholder="CEP" maxlength="9" required autocomplete="off"
                        oninput="formatInput(event, this)">
                    <input type="text" id="cidade" placeholder="Cidade" required autocomplete="off">
                    <input type="text" id="bairro" placeholder="Bairro" autocomplete="off">
                    <input type="text" id="rua" placeholder="Rua" autocomplete="off">
                    <input type="number" id="numero" placeholder="Número">
                    <button type="submit">Adicionar</button>
                </form>
            </div>
        </div>

        <div id="popupEdita-contatos" class="popupEdita">
            <div class="popup-content-Edita Medio">
                <span class="close">&times;</span>
                <h2>Editar Contato</h2>
                <form id="editContatosForm">
                    <input type="hidden" id="editIdContatos">
                    <input type="text" id="editNome" placeholder="Primeiro Nome" maxlength="21" required autocomplete="off">
                    <select id="editIdPredio" required>
                        <option value="">Selecione um Prédio</option>
                        <?php foreach ($predios as $predio): ?>
                            <option value="<?= htmlspecialchars($predio['id_predio']) ?>">
                                <?= htmlspecialchars($predio['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" id="editTelefone" placeholder="Telefone" maxlength="15" required
                        autocomplete="off" oninput="formatInput(event, this)">
                    <input type="text" id="editCep" placeholder="CEP" maxlength="9" required autocomplete="off"
                        oninput="formatInput(event, this)">
                    <input type="text" id="editCidade" placeholder="Cidade" autocomplete="off">
                    <input type="text" id="editBairro" placeholder="Bairro" autocomplete="off">
                    <input type="text" id="editRua" placeholder="Rua" autocomplete="off">
                    <input type="number" id="editNumero" placeholder="Número">
                    <button type="submit">Salvar</button>
                </form>
            </div>
        </div>

        <div class="content">
            <div class="title-container">
                <p class="title-table">TABELA DE CONTATOS</p>
                <button class="contatos-adicionar adicionar"><span class="material-symbols-outlined">add</span></button>
            </div>
            <table id="table">
                <tr>
                    <th>ID</th>
                    <th>CONTATO</th>
                    <th>PRÉDIO</th>
                    <th>TELEFONE</th>
                    <th>CEP</th>
                    <th>CIDADE</th>
                    <th>BAIRRO</th>
                    <th>RUA</th>
                    <th>NÚMERO</th>
                    <th>EDITAR</th>
                    <th>APAGAR</th>
                </tr>
                <?php

                $editar = '<button class="contatos-editar editar"><span class="material-symbols-outlined">edit</span></button>';
                $apagar = '<button class="contatos-apagar apagar"><span class="material-symbols-outlined">delete</span></button>';
                $rows = listContatos($conn);
                foreach ($rows as $row) {
                    echo "<tr>
                        <td>{$row['id_contato']}</td>
                        <td>{$row['nome']}</td>
                        <td class='id-predio' data-id='{$row['id_predio']}'>{$row['nome_predio']}</td>
                        <td>{$row['telefone']}</td>
                        <td>{$row['cep']}</td>
                        <td>{$row['cidade']}</td>
                        <td>{$row['bairro']}</td>
                        <td>{$row['rua']}</td>
                        <td>{$row['numero']}</td>
                        <td>{$editar}</td>
                        <td>{$apagar}</td>
                    </tr>";
                }
                ?>
            </table>
        </div>
    </article>
    <footer>sistema de gerenciamento de condominio&copy;</footer>
    <script src="js/jquery/jquery-3.7.1.min.js"></script>
    <script src="js/crud.js"></script>
    <script src="js/botoes.js"></script>
    <script>
        $(document).ready(function () {
            setupTableActions('contatos');

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
        });
    </script>
</body>

</html>