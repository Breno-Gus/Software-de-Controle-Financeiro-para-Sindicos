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
</head>

<body>
    <header>
        <div class="header">
            <h1>Bem-Vindo!</h1>
            <button id="logoutBtn"> <span class="material-symbols-outlined">logout</span></button>
        </div>
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
            <div class="janela morador">
                <span class="text-window">Moradores</span>
            </div>
            <div class="janela contatos">
                <span class="text-window">Contatos</span>
            </div>
        </div>
    </header>
    <?php if ($isAdmin): ?>
        <article>
            <div id="popup-sindico" class="popup">
                <div class="popup-content">
                    <span class="close">&times;</span>
                    <h2>Adicionar Novo Síndico</h2>
                    <form id="addSindicoForm">
                        <input type="text" id="nome" placeholder="Nome" required autocomplete="off">
                        <input type="text" id="cpf" placeholder="CPF" maxlength="14" required autocomplete="off"
                            oninput="formatInput(event, this)">
                        <input type="email" id="email" placeholder="Email" required autocomplete="off">
                        <input type="password" id="senha" placeholder="Senha" required autocomplete="off">
                        <button type="submit">Adicionar</button>
                    </form>
                </div>
            </div>

            <div id="popupEdita-sindico" class="popupEdita">
                <div class="popup-content-Edita">
                    <span class="close">&times;</span>
                    <h2>Editar Síndico</h2>
                    <form id="editSindicoForm">
                        <input type="hidden" id="editIdSindico">
                        <input type="text" id="editNome" placeholder="Nome" required autocomplete="off">
                        <input type="text" id="editCpf" placeholder="CPF" maxlength="14" required autocomplete="off"
                            oninput="formatInput(event, this)">
                        <input type="email" id="editEmail" placeholder="Email" required autocomplete="off">
                        <label><span class="red">AVISO:</span> ao mudar a senha, a pessoa que tem o cadastro deixará de ter,
                            SUBSTITUIRÁ a senha antiga!</label>
                        <input type="password" id="editSenha" name="senha"
                            placeholder="Senha (deixe vazio para não alterar)" autocomplete="off">
                        <button type="submit">Salvar</button>
                    </form>
                </div>
            </div>

            <div id="popupConfirma-sindico" class="popupConfirma">
                <div class="popup-content-Confirma">
                    <span class="close">&times;</span>
                    <h2>Confirmar para apagar Síndico</h2>
                    <form id="confirmDeleteForm">
                        <input type="hidden" id="deleteIdSindico">
                        <p>Você tem certeza que deseja apagar este síndico?</p>
                        <button type="submit">Confirmar</button>
                    </form>
                </div>
            </div>

            <div class="content">
                <div class="title-container">
                    <p class="title-table">TABELA DE SÍNDICOS</p>
                    <button class="sindico-adicionar adicionar">
                        <span class="material-symbols-outlined">add</span>
                    </button>
                </div>
                <table id="table">
                    <tr>
                        <th>ID</th>
                        <th>NOME</th>
                        <th>CPF</th>
                        <th>EMAIL</th>
                        <th>SENHA</th>
                        <th>EDITAR</th>
                        <th>APAGAR</th>
                    </tr>
                    <?php
                    $editar = '<button class="sindico-editar editar"><span class="material-symbols-outlined">edit</span></button>';
                    $apagar = '<button class="sindico-apagar apagar"><span class="material-symbols-outlined">delete</span></button>';
                    $rows = listSindico($conn);
                    foreach ($rows as $row) {
                        echo "<tr>
                    <td>{$row['id_sindico']}</td>
                    <td>{$row['nome']}</td>
                    <td>{$row['cpf']}</td>
                    <td>{$row['email']}</td>
                    <td>-</td>
                    <td>{$editar}</td>
                    <td>{$apagar}</td>
                  </tr>";
                    }
                    ?>
                </table>
            </div>
        </article>
    <?php else:
        header("Location: moradores.php");
    endif; ?>
    <footer>sistema de gerenciamento de condominio&copy;</footer>
    <script>
        document.getElementById("logoutBtn").addEventListener("click", function () {
            if (confirm("Tem certeza que deseja encerrar a sessão?")) {
                fetch('logout.php', {
                    method: 'POST',
                })
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
    </script>
</body>
<script src="js/jquery/jquery-3.7.1.min.js"></script>
<script src="js/crud.js"></script>
<script src="js/botoes.js"></script>
<script>
    $(document).ready(function () {
        setupTableActions('sindico');
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