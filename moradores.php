<?php
include 'php/database/conecte.php';
include 'php/crud/crudregistro.php';
include_once 'php/crud/crudmorador.php';

session_start();
if (!isset($_SESSION['email-logado'])) {
    header("Location: index.html");
}

$isAdmin = ($_SESSION['email-logado'] === 'admin');
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

            if (input.id === 'cpf' || input.id.startsWith('editCpf')) {
                if (input.value.length > 11) {
                    input.value = input.value.slice(0, 11);
                }
                input.value = input.value.replace(/(\d{3})(\d)/, '$1.$2');
                input.value = input.value.replace(/(\d{3})(\d)/, '$1.$2');
                input.value = input.value.replace(/(\d{3})(\d{2})$/, '$1-$2');
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
    <header>
        <div class="header">
            <h1>Bem-Vindo!</h1>
            <button id="logoutBtn"> <span class="material-symbols-outlined">logout</span></button>
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
                <div class="janela contatos">
                    <span class="text-window">Contatos</span>
                </div>
            </div>
        <?php else: ?>
            <div class="barra"></div>
            <div class="aba">
                <div class="janela despesas">
                    <span class="text-window">Despesas</span>
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

<body>
    <article>
        <div id="popup-morador" class="popup">
            <div class="popup-content">
                <span class="close">&times;</span>
                <h2>Adicionar Novo Morador</h2>
                <form id="addMoradorForm">
                    <input type="text" id="nome" placeholder="Nome" required autocomplete="off">
                    <input type="text" id="telefone" placeholder="Telefone" maxlength="15" required autocomplete="off"
                        oninput="formatInput(event, this)">
                    <input type="text" id="cpf" placeholder="CPF" maxlength="14" required autocomplete="off"
                        oninput="formatInput(event, this)">
                    <button type="submit">Adicionar</button>
                </form>
            </div>
        </div>

        <div id="popupEdita-morador" class="popupEdita">
            <div class="popup-content-Edita">
                <span class="close">&times;</span>
                <h2>Editar Morador</h2>
                <form id="editMoradorForm">
                    <input type="hidden" id="editIdMorador">
                    <input type="text" id="editNome" placeholder="Nome" required autocomplete="off">
                    <input type="text" id="editTelefone" placeholder="Telefone" maxlength="15" required
                        autocomplete="off" oninput="formatInput(event, this)">
                    <input type="text" id="editCpf" placeholder="CPF" maxlength="14" required autocomplete="off"
                        oninput="formatInput(event, this)">
                    <button type="submit">Salvar</button>
                </form>
            </div>
        </div>

        <div id="popupConfirma-morador" class="popupConfirma">
            <div class="popup-content-Confirma">
                <span class="close">&times;</span>
                <h2>Confirmar para apagar Morador</h2>
                <form id="confirmDeleteForm">
                    <input type="hidden" id="deleteIdMorador">
                    <p>Você tem certeza que deseja apagar este morador?</p>
                    <button type="submit">Confirmar</button>
                </form>
            </div>
        </div>

        <div class="content">
            <div class="title-container">
                <p class="title-table">TABELA DE MORADORES</p>
                <button class="morador-adicionar adicionar">
                    <span class="material-symbols-outlined">add</span>
                </button>
            </div>
            <table id="table">
                <tr>
                    <th>ID</th>
                    <th>NOME</th>
                    <th>CPF</th>
                    <th>TELEFONE</th>
                    <th>EDITAR</th>
                    <th>APAGAR</th>
                </tr>
                <?php
                $editar = '<button class="morador-editar editar"><span class="material-symbols-outlined">edit</span></button>';
                $apagar = '<button class="morador-apagar apagar"><span class="material-symbols-outlined">delete</span></button>';
                $rows = listMoradores($conn);
                foreach ($rows as $row) {
                    echo "<tr>
                    <td>{$row['id_morador']}</td>
                    <td>{$row['nome']}</td>
                    <td>{$row['cpf']}</td>
                    <td>{$row['telefone']}</td>
                    <td>{$editar}</td>
                    <td>{$apagar}</td>
                    </tr>";
                }
                ?>
            </table>
        </div>
    </article>
    <footer>sistema de gerenciamento de condominio&copy;</footer>
</body>
<script src="js/jquery/jquery-3.7.1.min.js"></script>
<script src="js/crud.js"></script>
<script src="js/botoes.js"></script>
<script>
    $(document).ready(function () {
        setupTableActions('morador');

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

</html>