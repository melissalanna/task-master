
<?php

// Autoload: carrega as classes automaticamente
spl_autoload_register(function ($class) {
    $dirs = ['Model', 'Controller'];

    foreach ($dirs as $dir) {
        $file = __DIR__ . "/src/$dir/$class.php";

        if (file_exists($file)) {
            require_once $file;
        }
    }
});

// Conexão com o banco de dados
$pdo = new PDO('sqlite:' . __DIR__ . '/tasks.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Criação da tabela, caso ainda não exista
$pdo->exec("
    CREATE TABLE IF NOT EXISTS tasks (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        description TEXT,
        due_date TEXT,
        responsible TEXT,
        done INTEGER DEFAULT 0
    )
");

// Verifica se existem todas as colunas necessárias
$columns = $pdo->query("PRAGMA table_info(tasks)")
               ->fetchAll(PDO::FETCH_COLUMN, 1);

$requiredColumns = [
    'description' => 'TEXT',
    'due_date' => 'TEXT',
    'responsible' => 'TEXT'
];

foreach ($requiredColumns as $column => $type) {
    if (!in_array($column, $columns, true)) {
        $pdo->exec("ALTER TABLE tasks ADD COLUMN $column $type");
    }
}

// Inicializa o Controller
$controller = new TaskController($pdo);

// Identifica a ação solicitada
$action = $_GET['action'] ?? 'index';

// Executa somente as ações permitidas
$allowedActions = ['index', 'create', 'complete', 'delete'];

if (in_array($action, $allowedActions, true)) {
    $controller->$action();
} else {
    http_response_code(404);
    echo "Página não encontrada - 404";
}
