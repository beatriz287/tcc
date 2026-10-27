<?php
session_start();

unset(
    $_SESSION['admin_id'],
    $_SESSION['admin_usuario'],
    $_SESSION['admin_nome']
);

session_regenerate_id(true);

header('Location: login-admin.php?logout=1');
exit;
