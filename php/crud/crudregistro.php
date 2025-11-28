<?php
function create($conn, $nome, $cpf, $email, $senha) {
    $senha = password_hash($senha, PASSWORD_DEFAULT);

    $sql = "INSERT INTO sindico (cpf, nome, senha, email) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $cpf, $nome, $senha, $email);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Síndico cadastrado com sucesso!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao cadastrar: " . $conn->error]);
    }
    $stmt->close();
}

function listSindico($conn) {
    $sql = "SELECT id_sindico, nome, cpf, email FROM sindico";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function editarSindico($conn, $id_sindico, $nome, $cpf, $email, $senha) {
    $sql = "UPDATE sindico SET nome = ?, cpf = ?, email = ?";
    $params = [$nome, $cpf, $email];
    $types = "sss";

    if (!empty($senha)) {
        $senha = password_hash($senha, PASSWORD_DEFAULT);
        $sql .= ", senha = ?";
        $params[] = $senha;
        $types .= "s";
    }

    $sql .= " WHERE id_sindico = ?";
    $params[] = $id_sindico;
    $types .= "i";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Síndico editado com sucesso!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao editar: " . $stmt->error]);
    }
    $stmt->close();
}

function deleteSindico($conn, $id_sindico) {
    $sql = "DELETE FROM sindico WHERE id_sindico = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_sindico);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Síndico apagado com sucesso!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao apagar: " . $stmt->error]);
    }
    $stmt->close();
}
?>
