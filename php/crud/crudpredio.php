<?php
function getPrediosWithSindico($conn)
{
    $query = "
        SELECT predios.id_predio, predios.nome, predios.cep, predios.cidade, 
               predios.bairro, predios.rua, predios.numero, predios.ativo, 
               predios.id_sindico, sindico.nome AS nome_sindico
        FROM predios
        LEFT JOIN sindico ON predios.id_sindico = sindico.id_sindico
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    $predios = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $predios[] = $row;
        }
    }
    return $predios;
}


function fetchSindicos($conn)
{
    $stmt = $conn->prepare("SELECT id_sindico, nome FROM sindico");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}


function createPredio($conn, $id_sindico, $nome, $cep, $cidade, $bairro, $rua, $numero)
{
    $stmt = $conn->prepare("INSERT INTO predios (nome, cep, cidade, bairro, rua, numero, id_sindico) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssi", $nome, $cep, $cidade, $bairro, $rua, $numero, $id_sindico);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Prédio adicionado com sucesso.']);
        http_response_code(200);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao adicionar prédio.']);
        http_response_code(500);
    }
}




function readPredios($conn)
{
    $sql = "SELECT * FROM predios";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $predios = [];
        while ($row = $result->fetch_assoc()) {
            $predios[] = $row;
        }
        return $predios;
    } else {
        return [];
    }
}


function updatePredio($conn, $id_predio, $id_sindico, $nome, $cep, $cidade, $bairro, $rua, $numero)
{
    $stmt = $conn->prepare("UPDATE predios SET id_sindico = ?, nome = ?, cep = ?, cidade = ?, bairro = ?, rua = ?, numero = ? WHERE id_predio = ?");
    $stmt->bind_param("isssssii", $id_sindico, $nome, $cep, $cidade, $bairro, $rua, $numero, $id_predio);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Prédio alterado com sucesso.']);
        http_response_code(200);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Erro ao alterar prédio.',
            'error' => $stmt->error
        ]);
        http_response_code(500);
    }
    $stmt->close();
}



function ativarPredio($conn, $id_predio)
{
    $sql = "UPDATE predios SET ativo = 'sim' WHERE id_predio = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_predio);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Prédio ativado com sucesso!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao desativar prédio: " . $stmt->error]);
    }

    $stmt->close();
}

function desativarPredio($conn, $id_predio)
{
    $sql = "UPDATE predios SET ativo = 'não' WHERE id_predio = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_predio);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Prédio desativado com sucesso!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao desativar prédio: " . $stmt->error]);
    }

    $stmt->close();
}

function deletePredio($conn, $id_predio)
{
    $sql = "DELETE FROM predios WHERE id_predio = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_predio);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Predio apagado com sucesso!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao apagar: " . $stmt->error]);
    }
    $stmt->close();
}



?>