<?php
require __DIR__ . '/vendor/autoload.php';
ProjetoIntegrador\Helpers\RegistradorErros::registrar('warning', 'Teste controlado de catálogo de erros.', 'config', 0, null);
echo 'Registro criado.';
