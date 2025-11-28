<?php
header('Content-Type: application/json');
include 'database/conecte.php';
include 'crud/crudregistro.php';

$cpf = $_POST['cpf'];

$sql = "SELECT * FROM sindico WHERE cpf = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $cpf);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['status' => 'existe']);
} else {
    echo json_encode(['status' => 'nao_existe']);
}

$stmt->close();
$conn->close();
?>