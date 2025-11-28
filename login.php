<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('php/database/conecte.php');

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        echo json_encode(['status' => 'error', 'message' => 'Email e senha são obrigatórios.']);
        exit();
    }

    $stmt = $conn->prepare("SELECT senha FROM sindico WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($email === 'admin' && $senha === '123') {
        session_start();
        $_SESSION['email-logado'] = $email;
        echo json_encode(['email' => 'correto', 'senha' => 'correto']);
    } else {
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $senha_hash = $row['senha'];

            if (password_verify($senha, $senha_hash)) {
                session_start();
                $_SESSION['email-logado'] = $email;
                echo json_encode(['email' => 'correto', 'senha' => 'correto']);
            } else {
                echo json_encode(['email' => 'correto', 'senha' => 'incorreto']);
            }
        } else {
            echo json_encode(['email' => 'incorreto', 'senha' => 'incorreto']);
        }
    }

    $stmt->close();
}

$conn->close();