<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gerenciamento_de_condominio";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexao falhou:" . $conn->connect_error);
}
?>