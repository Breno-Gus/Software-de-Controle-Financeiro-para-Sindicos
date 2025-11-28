<?php
function getDespesasWithDetails($conn)
{
    $query = "
        SELECT 
            despesas.id_despesa, despesas.nome, despesas.valor_total, 
            despesas.vencimento, despesas.foi_pago, despesas.id_predio, 
            despesas.id_contato, predios.nome AS nome_predio, 
            contatos.nome AS nome_contato
        FROM despesas
        LEFT JOIN predios ON despesas.id_predio = predios.id_predio
        LEFT JOIN contatos ON despesas.id_contato = contatos.id_contato
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    $despesas = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $despesas[] = $row;
        }
    }
    return $despesas;
}

function getDespesasAno($conn, $mes, $ano)
{
    $query = "
        SELECT 
            despesas.id_despesa, despesas.nome, despesas.valor_total, 
            despesas.vencimento, despesas.foi_pago, despesas.id_predio, 
            despesas.id_contato, predios.nome AS nome_predio, 
            contatos.nome AS nome_contato
        FROM despesas
        LEFT JOIN predios ON despesas.id_predio = predios.id_predio
        LEFT JOIN contatos ON despesas.id_contato = contatos.id_contato
        WHERE YEAR(STR_TO_DATE(vencimento, '%d/%m/%Y')) = ?";

    if (!empty($mes)) {
        $query .= " AND MONTH(STR_TO_DATE(vencimento, '%d/%m/%Y')) = ?";
    }

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Erro na preparação da consulta: " . $conn->error);
    }

    if (!empty($mes)) {
        $stmt->bind_param("ss", $ano, $mes);
    } else {
        $stmt->bind_param("s", $ano);
    }

    if (!$stmt->execute()) {
        die("Erro na execução da consulta: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $despesas = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $despesas[] = $row;
        }
    }

    $stmt->close();
    return $despesas;
}



function fetchPrediosDespesas($conn)
{
    $stmt = $conn->prepare("SELECT id_predio, nome FROM predios WHERE ativo = 'sim'");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

function fetchContatos($conn)
{
    $stmt = $conn->prepare("SELECT id_contato, nome FROM contatos");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

function createDespesa($conn, $id_predio, $id_contato, $nome, $valor_total, $vencimento)
{
    $sql = "INSERT INTO despesas (id_predio, id_contato, nome, valor_total, vencimento) 
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisss", $id_predio, $id_contato, $nome, $valor_total, $vencimento);

    if (!$stmt->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao criar despesa.']);
        http_response_code(500);
        exit();
    }

    echo json_encode(['status' => 'success', 'message' => 'Despesa criada com sucesso.']);
}



function updateDespesa($conn, $id_despesa, $id_predio, $id_contato, $nome, $valor_total, $vencimento)
{
    $stmt = $conn->prepare("
        UPDATE despesas 
        SET id_predio = ?, id_contato = ?, nome = ?, valor_total = ?, vencimento = ?
        WHERE id_despesa = ?
    ");
    $stmt->bind_param("iisdsi", $id_predio, $id_contato, $nome, $valor_total, $vencimento, $id_despesa);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Despesa atualizada com sucesso.']);
        http_response_code(200);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar despesa.', 'error' => $stmt->error]);
        http_response_code(500);
    }
    $stmt->close();
}

function deleteDespesa($conn, $id_despesa)
{
    $stmt = $conn->prepare("DELETE FROM despesas WHERE id_despesa = ?");
    $stmt->bind_param("i", $id_despesa);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Despesa deletada com sucesso.']);
        http_response_code(200);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao deletar despesa.', 'error' => $stmt->error]);
        http_response_code(500);
    }
    $stmt->close();
}

function marcarDespesaComoPaga($conn, $id_despesa)
{
    $stmt = $conn->prepare("UPDATE despesas SET foi_pago = 'sim' WHERE id_despesa = ?");
    $stmt->bind_param("i", $id_despesa);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Despesa marcada como paga."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao marcar despesa como paga: " . $stmt->error]);
    }
    $stmt->close();
}

function desmarcarDespesaComoPaga($conn, $id_despesa)
{
    $stmt = $conn->prepare("UPDATE despesas SET foi_pago = 'não' WHERE id_despesa = ?");
    $stmt->bind_param("i", $id_despesa);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Despesa desmarcada como paga."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao desmarcar despesa como paga: " . $stmt->error]);
    }
    $stmt->close();
}

function listarDespesasNaoPagas($conn)
{
    $stmt = $conn->prepare("SELECT * FROM despesas WHERE foi_pago = 'não'");
    $stmt->execute();
    $result = $stmt->get_result();

    $despesas = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $despesas[] = $row;
        }
    }
    return $despesas;
}

function consultarValorTotalDespesasPredio($conn, $id_predio, $ano, $mes = null)
{
    $query = "SELECT SUM(valor_total) AS total_despesas FROM despesas WHERE id_predio = ? AND SUBSTRING(vencimento, 7, 4) = ?";

    if (!empty($mes)) {
        $query .= " AND SUBSTRING(vencimento, 4, 2) = ?";
    }

    $stmt = $conn->prepare($query);

    if (!empty($mes)) {
        $stmt->bind_param("iii", $id_predio, $ano, $mes);
    } else {
        $stmt->bind_param("ii", $id_predio, $ano);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $total_despesas = 0.00;
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $total_despesas = $row['total_despesas'] !== null ? $row['total_despesas'] : 0.00;
    }

    $stmt->close();
    return $total_despesas;
}


function contarUnidadesPorDespesas($conn, $id_predio, $ano, $mes = null)
{
    $query = "SELECT COUNT(*) AS total_unidades FROM apartamentos WHERE id_predio = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_predio);
    $stmt->execute();
    $result = $stmt->get_result();

    $total_unidades = 0;
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $total_unidades = $row['total_unidades'];
    }

    $stmt->close();
    return $total_unidades;
}


?>