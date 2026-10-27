<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = getenv('DB_HOST') ?: 'localhost';
$usuario = getenv('DB_USER') ?: 'root';
$senha = getenv('DB_PASS') ?: '';
$banco = getenv('DB_NAME') ?: 'petshop_admin';
$porta = (int)(getenv('DB_PORT') ?: 3306);

try {
    $conexao = new mysqli($host, $usuario, $senha, $banco, $porta);
    $conexao->set_charset('utf8mb4');
} catch (mysqli_sql_exception $erro) {
    http_response_code(500);
    exit('Não foi possível conectar ao banco de dados. Verifique as configurações em conexao.php.');
}
