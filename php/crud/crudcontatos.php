<?php

function fetchPredios($conn)
{
    $stmt = $conn->prepare("SELECT id_predio, nome FROM predios");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}
function createContato($conn, $nome, $id_predio, $telefone, $cep, $cidade, $bairro, $rua, $numero)
{
    $stmt = $conn->prepare("
        INSERT INTO contatos (nome, id_predio, telefone, cep, cidade, bairro, rua, numero) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sisssssd", $nome, $id_predio, $telefone, $cep, $cidade, $bairro, $rua, $numero);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Contato cadastrado com sucesso!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao cadastrar contato: " . $stmt->error]);
    }

    $stmt->close();
}

function deleteContato($conn, $id_contato)
{
    $stmt = $conn->prepare("DELETE FROM contatos WHERE id_contato = ?");
    $stmt->bind_param("i", $id_contato);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Contato deletado com sucesso!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao deletar contato: " . $stmt->error]);
    }

    $stmt->close();
}


function listContatos($conn)
{
    $sql = "
        SELECT 
            contatos.id_contato, 
            contatos.id_predio,
            contatos.nome, 
            contatos.telefone, 
            contatos.cep, 
            contatos.cidade, 
            contatos.bairro, 
            contatos.rua, 
            contatos.numero, 
            predios.nome AS nome_predio
        FROM contatos
        LEFT JOIN predios ON contatos.id_predio = predios.id_predio
    ";

    $result = $conn->query($sql);

    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    return [];
}


function editarContato($conn, $id_contato, $id_predio, $nome, $telefone, $cep, $cidade, $bairro, $rua, $numero)
{
    $stmt = $conn->prepare("SELECT * FROM contatos WHERE id_contato = ?");
    $stmt->bind_param("i", $id_contato);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Contato não encontrado."]);
        return;
    }

    $stmt = $conn->prepare("
        UPDATE contatos 
        SET id_predio = ?, nome = ?, telefone = ?, cep = ?, cidade = ?, bairro = ?, rua = ?, numero = ? 
        WHERE id_contato = ?
    ");
    $stmt->bind_param("issssssdi", $id_predio, $nome, $telefone, $cep, $cidade, $bairro, $rua, $numero, $id_contato);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Contato atualizado com sucesso!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao atualizar contato: " . $stmt->error]);
    }

    $stmt->close();
}


?>
