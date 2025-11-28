<?php
function getApartamentosWithDetails($conn)
{
    $query = "
        SELECT 
            apartamentos.id_apartamento, apartamentos.numero, apartamentos.andar, apartamentos.ativo,
            predios.id_predio, predios.nome AS nome_predio, 
            morador.id_morador, morador.nome AS nome_morador
        FROM apartamentos
        INNER JOIN predios ON apartamentos.id_predio = predios.id_predio
        INNER JOIN morador ON apartamentos.id_morador = morador.id_morador
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    $apartamentos = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $apartamentos[] = $row;
        }
    }
    return $apartamentos;
}


function fetchPrediosApartamentos($conn)
{
    $stmt = $conn->prepare("SELECT id_predio, nome FROM predios WHERE ativo = 'sim'");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

function fetchMoradores($conn)
{
    $stmt = $conn->prepare("
        SELECT id_morador, nome
        FROM morador
        WHERE id_morador NOT IN (SELECT id_morador FROM apartamentos)
    ");
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    return [];
}


function createApartamento($conn, $id_predio, $id_morador, $numero, $andar)
{
    $sql = "INSERT INTO apartamentos (id_predio, id_morador, numero, andar) 
            VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", $id_predio, $id_morador, $numero, $andar);

    if (!$stmt->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao criar apartamento.']);
        http_response_code(500);
        exit();
    }

    echo json_encode(['status' => 'success', 'message' => 'Apartamento criado com sucesso.']);
}

function updateApartamento($conn, $id_apartamento, $id_predio, $id_morador, $numero, $andar)
{
    $stmt = $conn->prepare("
        UPDATE apartamentos 
        SET id_predio = ?, id_morador = ?, numero = ?, andar = ?
        WHERE id_apartamento = ?
    ");
    $stmt->bind_param("iiisi", $id_predio, $id_morador, $numero, $andar, $id_apartamento);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Apartamento atualizado com sucesso.']);
        http_response_code(200);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar apartamento.', 'error' => $stmt->error]);
        http_response_code(500);
    }
    $stmt->close();
}

function deleteApartamento($conn, $id_apartamento)
{
    $stmt = $conn->prepare("DELETE FROM apartamentos WHERE id_apartamento = ?");
    $stmts = $conn->prepare("DELETE FROM apartamento_despesas WHERE id_apartamento_despesas = ? ");
    $stmt->bind_param("i", $id_apartamento);
    $stmts->bind_param("i", $id_apartamento);
    if ($stmts->execute()) {
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Apartamento deletado com sucesso.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao deletar apartamento.', 'error' => $stmt->error]);
    }
    $stmt->close();
}

function marcarApartamentoComoAtivo($conn, $id_apartamento)
{
    $stmt = $conn->prepare("UPDATE apartamentos SET ativo = 'sim' WHERE id_apartamento = ?");
    $stmt->bind_param("i", $id_apartamento);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Apartamento marcada como ativo!."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao marcar apartamento como ativo: " . $stmt->error]);
    }
    $stmt->close();
}

function marcarApartamentoComoDesativado($conn, $id_apartamento)
{
    $stmt = $conn->prepare("UPDATE apartamentos SET ativo = 'não' WHERE id_apartamento = ?");
    $stmt->bind_param("i", $id_apartamento);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Apartamento desativado!."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao desmarcar apartamento como ativo: " . $stmt->error]);
    }
    $stmt->close();
}

?>