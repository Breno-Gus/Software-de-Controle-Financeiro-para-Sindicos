<?php
include 'php/database/conecte.php';
include 'php/crud/crudregistro.php';
include_once 'php/crud/crudpredio.php';

session_start();
if (!isset($_SESSION['email-logado'])) {
    header("Location: index.html");
}

$isAdmin = ($_SESSION['email-logado'] === 'admin');
$sindicos = fetchSindicos($conn);
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
                <div class="janela despesas"><span class="text-window">Despesas</span></div>
                <div class="janela apartamentos"><span class="text-window">Apartamentos</span></div>
                <div class="janela sindico"><span class="text-window">Síndicos</span></div>
                <div class="janela contatos"><span class="text-window">Contatos</span></div>
            </div>
        <?php endif; ?>
    </header>

    <article>
        <div id="popup-predios" class="popup">
            <div class="popup-content">
                <span class="close">&times;</span>
                <h2>Adicionar Novo Prédio</h2>
                <form id="addPrediosForm">
                    <input type="text" id="nome" placeholder="Nome" required autocomplete="off">
                    <input type="text" id="cep" placeholder="CEP" maxlength="9" required autocomplete="off"
                        oninput="formatInput(event, this)">
                    <input type="text" id="cidade" placeholder="Cidade" required autocomplete="off">
                    <input type="text" id="bairro" placeholder="Bairro" required autocomplete="off">
                    <input type="text" id="rua" placeholder="Rua" required autocomplete="off">
                    <input type="number" id="numero" placeholder="Número" required>
                    <select id="idSindico" required>
                        <option value="">Selecione um Síndico</option>
                        <?php foreach ($sindicos as $sindico): ?>
                            <option value="<?= htmlspecialchars($sindico['id_sindico']) ?>">
                                <?= htmlspecialchars($sindico['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Adicionar</button>
                </form>
            </div>
        </div>

        <div id="popupEdita-predios" class="popupEdita">
            <div class="popup-content-Edita">
                <span class="close">&times;</span>
                <h2>Editar Prédio</h2>
                <form id="editPrediosForm">
                    <input type="hidden" id="editIdPredios">
                    <input type="text" id="editNome" placeholder="Nome" required autocomplete="off">
                    <input type="text" id="editCep" placeholder="CEP" maxlength="9" required autocomplete="off"
                        oninput="formatInput(event, this)">
                    <input type="text" id="editCidade" placeholder="Cidade" required autocomplete="off">
                    <input type="text" id="editBairro" placeholder="Bairro" required autocomplete="off">
                    <input type="text" id="editRua" placeholder="Rua" required autocomplete="off">
                    <input type="number" id="editNumero" placeholder="Número" required>
                    <select id="editIdSindico" required>
                        <option value="">Selecione um Síndico</option>
                        <?php if (!empty($sindicos)): ?>
                            <?php foreach ($sindicos as $sindico): ?>
                                <option value="<?= htmlspecialchars($sindico['id_sindico']) ?>" <?= isset($predio['id_sindico']) && $sindico['id_sindico'] == $predio['id_sindico'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sindico['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">Nenhum síndico disponível</option>
                        <?php endif; ?>
                    </select>



                    <button type="submit">Salvar</button>
                </form>
            </div>
        </div>

        <div class="content">
            <div class="title-container">
                <p class="title-table">TABELA DE PRÉDIOS</p>
                <button class="predios-adicionar adicionar"><span class="material-symbols-outlined">add</span></button>
            </div>
            <table id="table">
                <tr>
                    <th>ID</th>
                    <th>PREDIO</th>
                    <th>ATIVO?</th>
                    <th>CEP</th>
                    <th>CIDADE</th>
                    <th>BAIRRO</th>
                    <th>RUA</th>
                    <th>NÚMERO</th>
                    <th>SINDICO</th>
                    <th>EDITAR</th>
                    <th>DESATIVAR</th>
                    <th>ATIVAR</th>
                    <th>APAGAR</th>
                </tr>
                <?php
                $editar = '<button class="predios-editar editar"><span class="material-symbols-outlined">edit</span></button>';
                $apagar = '<button class="predios-apagar apagar"><span class="material-symbols-outlined">delete</span></button>';
                $desativar = '<button class="predios-block desativarPredios"><span class="material-symbols-outlined">block</span></button>';
                $ativar = '<button class="predios-check checkPredios"><span class="material-symbols-outlined">check</span></button>';
                $iconcheck = '<span class="material-symbols-outlined">check</span>';
                $iconblock = '<span class="material-symbols-outlined">block</span>';
                $rows = getPrediosWithSindico($conn);
                foreach ($rows as $row) {
                    if ($row['ativo'] === 'sim') {
                        echo "<tr>
                        <td>{$row['id_predio']}</td>
                        <td>{$row['nome']}</td>
                        <td>{$iconcheck}</td>
                        <td>{$row['cep']}</td>
                        <td>{$row['cidade']}</td>
                        <td>{$row['bairro']}</td>
                        <td>{$row['rua']}</td>
                        <td>{$row['numero']}</td>
                        <td data-id='{$row['id_sindico']}'>{$row['nome_sindico']}</td>
                        <td>{$editar}</td>
                        <td>{$desativar}</td>
                        <td>{$ativar}</td>
                        <td>{$apagar}</td>
                    </tr>";
                    } else if ($row['ativo'] === 'não') {
                        echo "<tr>
                        <td>{$row['id_predio']}</td>
                        <td>{$row['nome']}</td>
                        <td>{$iconblock}</td>
                        <td>{$row['cep']}</td>
                        <td>{$row['cidade']}</td>
                        <td>{$row['bairro']}</td>
                        <td>{$row['rua']}</td>
                        <td>{$row['numero']}</td>
                        <td data-id='{$row['id_sindico']}'>{$row['nome_sindico']}</td>
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
    </article>
    <footer>sistema de gerenciamento de condominio&copy;</footer>
    <script src="js/jquery/jquery-3.7.1.min.js"></script>
    <script src="js/crud.js"></script>
    <script src="js/botoes.js"></script>
    <script>
        $(document).ready(function () {
            setupTableActions('predios');

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