<?php
// Реальные данные подключения хранятся в config/db-local.php.
// Этот файл в .gitignore и в репозиторий не попадает.
// Скопируйте config/db-local.example.php в config/db-local.php и впишите свои значения.
$local = __DIR__ . '/db-local.php';

return file_exists($local) ? require $local : require __DIR__ . '/db-local.example.php';
