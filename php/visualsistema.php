<?php
header('Content-Type: application/json');
include 'database/conecte.php';
include 'crud/crudregistro.php';

error_log(print_r($_POST, true));

if (isset($_POST['nome'], $_POST['cpf'], $_POST['email'], $_POST['senha'])) {
    create($conn, $_POST['nome'], $_POST['cpf'], $_POST['email'], $_POST['senha']);
} else {
    echo json_encode(["status" => "error", "message" => "Dados incompletos"]);
}

?>
