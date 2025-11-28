<?php
include '../database/conecte.php';
include '../crud/crudregistro.php';
include '../crud/crudmorador.php';
include '../crud/crudpredio.php';
include '../crud/cruddespesa.php';
include '../crud/crudcontatos.php';
include '../crud/crudapartamentos.php';
include '../crud/apartamento_despesas.php';

header('Content-Type: application/json');

$requestMethod = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao processar os dados.']);
    http_response_code(400);
    exit();
}

$tableType = isset($_GET['table']) ? $_GET['table'] : '';

function formatarCPF($cpf)
{
    $cpf = preg_replace('/\D/', '', $cpf);
    $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);

    if (strlen($cpf) === 11) {
        return substr($cpf, 0, 3) . '.' .
            substr($cpf, 3, 3) . '.' .
            substr($cpf, 6, 3) . '-' .
            substr($cpf, 9, 2);
    }

    return $cpf;
}

function formatarTelefone($telefone)
{
    $telefone = preg_replace('/\D/', '', $telefone);
    $telefone = str_pad($telefone, 11, '0', STR_PAD_LEFT);

    if (strlen($telefone) === 11) {
        return '(' . substr($telefone, 0, 2) . ') ' .
            substr($telefone, 2, 5) . '-' .
            substr($telefone, 7);
    }

    return $telefone;
}

if ($requestMethod === 'POST') {

    if ($tableType === 'sindico') {
        if (!$data || !isset($data['nome'], $data['cpf'], $data['email'], $data['senha'])) {
            echo json_encode(['status' => 'error', 'message' => 'Dados inválidos.']);
            http_response_code(400);
            exit();
        }

        $nome = $data['nome'];
        $cpf = formatarCPF($data['cpf']);
        $email = $data['email'];
        $senha = $data['senha'];

        if (!preg_match('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', $cpf)) {
            echo json_encode(['status' => 'error', 'message' => 'Erro ao processar o CPF.']);
            http_response_code(400);
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Erro ao processar o e-mail.']);
            http_response_code(400);
            exit();
        }

        $stmt = $conn->prepare("SELECT * FROM sindico WHERE cpf = ? OR email = ?");
        $stmt->bind_param("ss", $cpf, $email);
        $stmt->execute();
        $checkResult = $stmt->get_result();

        if ($checkResult->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'CPF ou e-mail já cadastrados.']);
            http_response_code(409);
        } else {
            create($conn, $nome, $cpf, $email, $senha);
        }
    } elseif ($tableType === 'morador') {
        if (!$data || !isset($data['nome'], $data['cpf'], $data['telefone'])) {
            echo json_encode(['status' => 'error', 'message' => 'Dados inválidos.']);
            http_response_code(400);
            exit();
        }

        $nome = $data['nome'];
        $cpf = formatarCPF($data['cpf']);
        $telefone = formatarTelefone($data['telefone']);

        if (!preg_match('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', $cpf)) {
            echo json_encode(['status' => 'error', 'message' => 'Erro ao processar o CPF.']);
            http_response_code(400);
            exit();
        }

        $stmt = $conn->prepare("SELECT * FROM morador WHERE cpf = ?");
        $stmt->bind_param("s", $cpf);
        $stmt->execute();
        $checkResult = $stmt->get_result();

        if ($checkResult->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Morador com este CPF já cadastrado.']);
            http_response_code(409);
        } else {
            createMorador($conn, $nome, $telefone, $cpf);
        }
    } else if ($tableType === 'predios') {
        if (!$data || !isset($data['id_sindico'], $data['nome'], $data['cep'], $data['cidade'], $data['bairro'], $data['rua'], $data['numero'])) {
            echo json_encode(['status' => 'error', 'message' => 'Dados inválidos.']);
            http_response_code(400);
            exit();
        }

        $id_sindico = $data['id_sindico'] ? $data['id_sindico'] : null;
        $nome = $data['nome'];
        $cep = $data['cep'];
        $cidade = $data['cidade'];
        $bairro = $data['bairro'];
        $rua = $data['rua'];
        $numero = $data['numero'];

        $stmt = $conn->prepare("SELECT * FROM predios WHERE nome = ?");
        $stmt->bind_param("s", $nome);
        $stmt->execute();
        $checkResult = $stmt->get_result();

        if ($checkResult->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Já existe um prédio com este nome.']);
            http_response_code(409);
        } else {
            createPredio($conn, $id_sindico, $nome, $cep, $cidade, $bairro, $rua, $numero);
        }
    } else if ($tableType === 'despesas') {
        if (!isset($data['id_predio'], $data['valor_total'], $data['nome'], $data['vencimento'])) {
            $missingFields = [];
            if (!isset($data['id_predio']))
                $missingFields[] = 'id_predio';
            if (!isset($data['valor_total']))
                $missingFields[] = 'valor_total';
            if (!isset($data['nome']))
                $missingFields[] = 'nome';
            if (!isset($data['vencimento']))
                $missingFields[] = 'vencimento';

            echo json_encode([
                'status' => 'error',
                'message' => 'Dados inválidos. Campos ausentes: ' . implode(', ', $missingFields),
            ]);
            http_response_code(400);
            exit();
        }

        $vencimento = $data['vencimento'];
        $id_predio = !empty($data['id_predio']) ? $data['id_predio'] : null;
        $id_contato = isset($data['id_contato']) && !empty($data['id_contato']) ? $data['id_contato'] : null;
        $nome = $data['nome'];
        $valor_total = $data['valor_total'];
        createDespesa($conn, $id_predio, $id_contato, $nome, $valor_total, $vencimento);
        exit();
    } else if ($tableType === 'contatos') {
        if (!$data || !isset($data['nome'], $data['telefone'], $data['cep'], $data['cidade'], $data['bairro'], $data['rua'], $data['numero'])) {
            echo json_encode(['status' => 'error', 'message' => 'Dados inválidos.']);
            http_response_code(400);
            exit();
        }

        $nome = $data['nome'];
        $id_predio = !empty($data['id_predio']) ? $data['id_predio'] : null;
        $telefone = formatarTelefone($data['telefone']);
        $cep = $data['cep'];
        $cidade = $data['cidade'];
        $bairro = $data['bairro'];
        $rua = $data['rua'];
        $numero = $data['numero'];

        $stmt = $conn->prepare("SELECT * FROM contatos WHERE telefone = ?");
        $stmt->bind_param("s", $telefone);
        $stmt->execute();
        $checkResult = $stmt->get_result();

        if ($checkResult->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Contato com este telefone já existe.']);
            http_response_code(409);
        } else {
            createContato($conn, $nome, $id_predio, $telefone, $cep, $cidade, $bairro, $rua, $numero);
        }
    } else if ($tableType === 'apartamentos') {
        if (!$data || !isset($data['id_predio'], $data['id_morador'], $data['numero'], $data['andar'])) {
            echo json_encode(['status' => 'error', 'message' => 'Dados inválidos.']);
            http_response_code(400);
            exit();
        }
        $numero = $data['numero'];
        $id_predio = !empty($data['id_predio']) ? $data['id_predio'] : null;
        $id_morador = !empty($data['id_morador']) ? $data['id_morador'] : null;
        $andar = $data['andar'];
        ;

        $stmt = $conn->prepare("SELECT * FROM apartamentos WHERE id_morador = ?");
        $stmt->bind_param("i", $id_morador);
        $stmt->execute();
        $checkResult = $stmt->get_result();

        if ($checkResult->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Um morador já está cadastrado nesse apartamento!.']);
            http_response_code(409);
        } else {
            createApartamento($conn, $id_predio, $id_morador, $numero, $andar);
        }
    } else if ($tableType === 'despesa_apartamento') {
        if (!$data || !isset($data['id_apartamento'], $data['nome'], $data['valor_total'])) {
            echo json_encode(['status' => 'error', 'message' => 'Dados inválidos.']);
            http_response_code(400);
            exit();
        }
        $nome = $data['nome'];
        $id_apartamento = !empty($data['id_apartamento']) ? $data['id_apartamento'] : null;
        $valor_total = $data['valor_total'];

        createApartamento_Despesas($conn, $id_apartamento, $nome, $valor_total);
    }

} elseif ($requestMethod === 'DELETE') {
    if ($tableType === 'sindico' && isset($data['id'])) {
        $id_sindico = $data['id'];
        deleteSindico($conn, $id_sindico);
    } elseif ($tableType === 'morador' && isset($data['id'])) {
        $id_morador = $data['id'];
        deleteMorador($conn, $id_morador);
    } elseif ($tableType === 'predios' && isset($data['id'])) {
        $id_predio = $data['id'];
        deletePredio($conn, $id_predio);
    } else if ($tableType === 'despesas' && isset($data['id'])) {
        $id_despesas = $data['id'];
        deleteDespesa($conn, $id_despesas);
    } else if ($tableType === 'contatos' && isset($data['id'])) {
        $id_contatos = $data['id'];
        deleteContato($conn, $id_contatos);
    } else if ($tableType === 'apartamentos' && isset($data['id'])) {
        $id_apartamentos = $data['id'];
        deleteApartamento($conn, $id_apartamentos);
    } else if ($tableType === 'despesa_apartamento') {
        $id_apartamento = $data['id'];
        deleteApartamento_Despesas($conn, $id_apartamento);
    }
} elseif ($requestMethod === 'CHECK') {
    if ($tableType === 'predios' && isset($data['id'])) {
        $id_sindico = $data['id'];
        ativarPredio($conn, $id_sindico);
    } else if ($tableType === 'despesas' && isset($data['id'])) {
        $id_despesa = $data['id'];
        marcarDespesaComoPaga($conn, $id_despesa);
    } else if ($tableType === 'apartamentos' && isset($data['id'])) {
        $id_apartamento = $data['id'];
        marcarApartamentoComoAtivo($conn, $id_apartamento);
    } else if ($tableType === 'despesa_apartamento' && isset($data['id'])) {
        $id_apartamento = $data['id'];
        pagoApartamento_Despesas($conn, $id_apartamento);
    }
} elseif ($requestMethod === 'DESATIVE') {
    if ($tableType === 'predios' && isset($data['id'])) {
        $id_sindico = $data['id'];
        desativarPredio($conn, $id_sindico);
    } else if ($tableType === 'despesas' && isset($data['id'])) {
        $id_despesa = $data['id'];
        desmarcarDespesaComoPaga($conn, $id_despesa);
    } else if ($tableType === 'apartamentos' && isset($data['id'])) {
        $id_apartamento = $data['id'];
        marcarApartamentoComoDesativado($conn, $id_apartamento);
    } else if ($tableType === 'despesa_apartamento' && isset($data['id'])) {
        $id_apartamento = $data['id'];
        despagoApartamento_Despesas($conn, $id_apartamento);
    }
} elseif ($requestMethod === 'PUT') {
    if ($tableType === 'sindico' && (!$data || !isset($data['id'], $data['nome'], $data['cpf'], $data['email']))) {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao processar os dados de Sindico.']);
        http_response_code(400);
        exit();
    }

    if ($tableType === 'morador' && (!$data || !isset($data['id'], $data['nome'], $data['cpf'], $data['telefone']))) {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao processar os dados de Morador.']);
        http_response_code(400);
        exit();
    }

    if ($tableType === 'predios' && (!$data || !isset($data['id'], $data['id_sindico'], $data['nome'], $data['cep'], $data['cidade'], $data['bairro'], $data['rua'], $data['numero']))) {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao processar os dados do Prédio.']);
        http_response_code(400);
        exit();
    }

    if ($tableType === 'despesas' && (!$data || !isset($data['id_predio'], $data['nome'], $data['valor_total'], $data['vencimento']))) {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao processar os dados do Prédio.']);
        http_response_code(400);
        exit();
    }
    if ($tableType === 'contatos' && (!$data || !isset($data['id'], $data['id_predio'], $data['nome'], $data['telefone'], $data['cep'], $data['cidade'], $data['bairro'], $data['rua'], $data['numero']))) {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao processar os dados dos Contatos.']);
        http_response_code(400);
        exit();
    }

    if ($tableType === 'apartamentos' && (!$data || !isset($data['id_predio'], $data['id_morador'], $data['numero'], $data['andar']))) {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao processar os dados dos Apartamentos.']);
        http_response_code(400);
        exit();
    }

    if ($tableType === 'despesa_apartamento' && (!$data || !isset($data['id_apartamento'], $data['nome'], $data['valor_total']))) {
        echo json_encode(['status' => 'error', 'message' => 'Dados inválidos.']);
        http_response_code(400);
        exit();
    }

    if ($tableType === 'sindico') {
        $id_sindico = $data['id'];
        $nome = $data['nome'];
        $cpf = formatarCPF($data['cpf']);
        $email = $data['email'];
        $senha = $data['senha'];

        $stmt = $conn->prepare("SELECT * FROM sindico WHERE (cpf = ? OR email = ?) AND id_sindico != ?");
        $stmt->bind_param("ssi", $cpf, $email, $id_sindico);
        $stmt->execute();
        $checkResult = $stmt->get_result();

        if ($checkResult->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'CPF ou e-mail já cadastrados.']);
            http_response_code(409);
        } else {
            editarSindico($conn, $id_sindico, $nome, $cpf, $email, $senha);
        }
    } elseif ($tableType === 'morador') {
        $id_morador = $data['id'];
        $nome = $data['nome'];
        $cpf = formatarCPF($data['cpf']);
        $telefone = formatarTelefone($data['telefone']);

        $stmt = $conn->prepare("SELECT * FROM morador WHERE cpf = ? AND id_morador != ?");
        $stmt->bind_param("si", $cpf, $id_morador);
        $stmt->execute();
        $checkResult = $stmt->get_result();

        if ($checkResult->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'CPF já cadastrado para outro morador.']);
            http_response_code(409);
        } else {
            editarMorador($conn, $id_morador, $nome, $telefone, $cpf);
        }
    } elseif ($tableType === 'predios') {
        $id_predio = $data['id'];
        $id_sindico = $data['id_sindico'];
        $nome = $data['nome'];
        $cep = $data['cep'];
        $cidade = $data['cidade'];
        $bairro = $data['bairro'];
        $rua = $data['rua'];
        $numero = $data['numero'];

        updatePredio($conn, $id_predio, $id_sindico, $nome, $cep, $cidade, $bairro, $rua, $numero);
    } elseif ($tableType === 'despesas') {

        $id_despesas = $data['id'];
        $id_predio = $data['id_predio'];
        $id_contato = $data['id_contato'];
        $nome = $data['nome'];
        $valor_total = $data['valor_total'];
        $vencimento = $data['vencimento'];
        updateDespesa($conn, $id_despesas, $id_predio, $id_contato, $nome, $valor_total, $vencimento);
    } elseif ($tableType === 'contatos') {
        if (!$data || !isset($data['id'], $data['id_predio'], $data['nome'], $data['telefone'], $data['cep'], $data['cidade'], $data['bairro'], $data['rua'], $data['numero'])) {
            echo json_encode(['status' => 'error', 'message' => 'Dados inválidos.']);
            http_response_code(400);
            exit();
        }

        $id_contato = $data['id'];
        $nome = $data['nome'];
        $id_predio = $data['id_predio'];
        $telefone = formatarTelefone($data['telefone']);
        $cep = $data['cep'];
        $cidade = $data['cidade'];
        $bairro = $data['bairro'];
        $rua = $data['rua'];
        $numero = $data['numero'];

        $stmt = $conn->prepare("SELECT * FROM contatos WHERE telefone = ? AND id_contato != ?");
        $stmt->bind_param("si", $telefone, $id_contato);
        $stmt->execute();
        $checkResult = $stmt->get_result();

        if ($checkResult->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Contato com este telefone já existe.']);
            http_response_code(409);
        } else {
            editarContato($conn, $id_contato, $id_predio, $nome, $telefone, $cep, $cidade, $bairro, $rua, $numero);
        }
    } elseif ($tableType === 'apartamentos') {

        $id_apartamento = $data['id'];
        $id_predio = $data['id_predio'];
        $id_morador = $data['id_morador'];
        $numero = $data['numero'];
        $andar = $data['andar'];
        updateApartamento($conn, $id_apartamento, $id_predio, $id_morador, $numero, $andar);
    } elseif ($tableType === 'despesa_apartamento') {
        $id_apartamento_despesa = $data['id'];
        $id_apartamento = $data['id_apartamento'];
        $nome = $data['nome'];
        $valor_total = $data['valor_total'];
        updateApartamento_Despesas($conn, $id_apartamento_despesa, $id_apartamento, $nome, $valor_total);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido.']);
    http_response_code(405);
}
?>