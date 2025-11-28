<?php
include '../database/conecte.php';
function createApartamento_Despesas($conn, $id_apartamento, $nome, $valor_singular)
{
    $stmt = $conn->prepare("
        INSERT INTO apartamento_despesas (id_apartamento, nome, valor_singular) VALUES (?, ?, ?)
    ");
    $stmt->bind_param("isd", $id_apartamento, $nome, $valor_singular);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Despesa cadastrada com sucesso!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao criar despesa: " . $stmt->error]);
    }

    $stmt->close();
}

function updateApartamento_Despesas($conn, $id_despesa, $id_apartamento, $nome, $valor_singular)
{
    $stmt = $conn->prepare("
        UPDATE apartamento_despesas 
        SET id_apartamento = ?, nome = ?, valor_singular = ? 
        WHERE id_apartamento_despesas = ?
    ");
    $stmt->bind_param("isdi", $id_apartamento, $nome, $valor_singular, $id_despesa);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Despesa atualizada com sucesso!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao atualizar despesa: " . $stmt->error]);
    }

    $stmt->close();
}

function deleteApartamento_Despesas($conn, $id_apartamento_despesa)
{
    $stmt = $conn->prepare("DELETE FROM apartamento_despesas WHERE id_apartamento_despesas = ?");
    $stmt->bind_param("i", $id_apartamento_despesa);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Despesa deletada com sucesso!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao deletar contato: " . $stmt->error]);
    }

    $stmt->close();
}

function pagoApartamento_Despesas($conn, $id_apartamento_despesa)
{
    $stmt = $conn->prepare("UPDATE apartamento_despesas SET foi_pago = 'sim' WHERE id_apartamento_despesas = ?");
    $stmt->bind_param("i", $id_apartamento_despesa);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Despesa marcada como paga."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao marcar despesa como paga: " . $stmt->error]);
    }
    $stmt->close();
}
function despagoApartamento_Despesas($conn, $id_apartamento_despesa)
{
    $stmt = $conn->prepare("UPDATE apartamento_despesas SET foi_pago = 'não' WHERE id_apartamento_despesas = ?");
    $stmt->bind_param("i", $id_apartamento_despesa);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Despesa retirado do pago com sucesso!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao deletar contato: " . $stmt->error]);
    }

    $stmt->close();
}

function listApartamento_Despesas($conn, $id_apartamento)
{
    $stmt = $conn->prepare("SELECT * FROM apartamento_despesas WHERE id_apartamento = ?");
    $stmt->bind_param("i", $id_apartamento);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $despesas = [];
        while ($row = $result->fetch_assoc()) {
            $despesas[] = $row;
        }
        echo json_encode(["status" => "success", "despesas" => $despesas]);
    } else {
        echo json_encode(["status" => "success", "despesas" => []]);
    }

    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_apartamento'])) {
    $id_apartamento = $_POST['id_apartamento'];
    listApartamento_Despesas($conn, $id_apartamento);
}

?>