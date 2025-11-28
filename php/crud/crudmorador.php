<?php
function createMorador($conn, $nome, $telefone, $cpf)
{
    $stmt = $conn->prepare("INSERT INTO morador (nome, cpf, telefone) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nome, $cpf, $telefone);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Morador cadastrado com sucesso!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao cadastrar: " . $stmt->error]);
    }

    $stmt->close();
}

function deleteMorador($conn, $id_morador)
{
    $stmt = $conn->prepare("DELETE FROM morador WHERE id_morador = ?");
    $stmt->bind_param("i", $id_morador);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Morador deletado com sucesso!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao deletar: " . $stmt->error]);
    }

    $stmt->close();
}

function listMoradores($conn)
{
    $sql = "SELECT id_morador, nome, cpf, telefone FROM morador";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function editarMorador($conn, $id_morador, $nome, $telefone, $cpf)
{
    $stmt = $conn->prepare("SELECT * FROM morador WHERE id_morador = ?");
    $stmt->bind_param("i", $id_morador);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Morador não encontrado."]);
        return;
    }

    $stmt = $conn->prepare("UPDATE morador SET nome = ?, cpf = ?, telefone = ? WHERE id_morador = ?");
    $stmt->bind_param("sssi", $nome, $cpf, $telefone, $id_morador);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Morador atualizado com sucesso!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao atualizar: " . $stmt->error]);
    }

    $stmt->close();
}
?>
