
<?php

// ==========================================
// 1. CARREGAMENTO AUTOMÁTICO DAS CLASSES
// ==========================================

spl_autoload_register(function (string $className): void {

    $directories = [
        __DIR__ . '/src/Model/',
        __DIR__ . '/src/Controller/'
    ];

    foreach ($directories as $directory) {

        $file = $directory . $className . '.php';

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});


// ==========================================
// 2. CONEXÃO COM O BANCO DE DADOS
// ==========================================

try {

    $dbFile = __DIR__ . '/tasks.sqlite';

    $pdo = new PDO('sqlite:' . $dbFile);

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (PDOException $exception) {

    http_response_code(500);

    exit('Erro ao conectar ao banco de dados.');

}


// ==========================================
// 3. INSTANCIAÇÃO DO CONTROLLER
// ==========================================

$controller = new TaskController($pdo);


// ==========================================
// 4. ROTEAMENTO
// ==========================================

$action = $_GET['action'] ?? 'index';

$routes = [
    'index',
    'create',
    'complete',
    'delete'
];

if (!in_array($action, $routes, true)) {

    http_response_code(404);

    exit('Página não encontrada.');

}


// ==========================================
// 5. VERIFICAÇÃO DO MÉTODO HTTP
// ==========================================

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    exit('Método não permitido.');

}


// ==========================================
// 6. EXECUÇÃO DA AÇÃO
// ==========================================

$controller->$action();
