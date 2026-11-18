
<?php

// ==========================================
// 1. CARREGAMENTO AUTOMÁTICO DAS CLASSES
// ==========================================

spl_autoload_register(function ($class) {

    $dirs = ['Model', 'Presenter', 'View'];

    foreach ($dirs as $dir) {

        $file = __DIR__ . "/src/$dir/$class.php";

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});


// ==========================================
// 2. CONEXÃO COM O BANCO SQLITE
// ==========================================

try {

    $databasePath = __DIR__ . '/tasks.sqlite';

    $pdo = new PDO('sqlite:' . $databasePath);

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    // Criação automática da tabela
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tasks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            description TEXT,
            due_date TEXT NOT NULL,
            responsible TEXT NOT NULL,
            done INTEGER DEFAULT 0
        )
    ");

} catch (PDOException $exception) {

    http_response_code(500);
    exit('Erro ao conectar ao banco de dados.');

}


// ==========================================
// 3. INSTANCIAÇÃO DO MVP
// ==========================================

$model = new Task($pdo);

$view = new TaskHtmlView();

$presenter = new TaskPresenter($model, $view);


// ==========================================
// 4. ROTEAMENTO
// ==========================================

$action = $_GET['action'] ?? 'index';

$routes = ['index', 'create', 'complete', 'delete'];

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
// 6. EXECUÇÃO DAS AÇÕES PELO PRESENTER
// ==========================================

if ($action === 'create') {

    $presenter->create(
        $_POST['title'] ?? '',
        $_POST['description'] ?? '',
        $_POST['responsible'] ?? '',
        $_POST['due_date'] ?? ''
    );

} elseif ($action === 'complete') {

    $presenter->complete($_GET['id'] ?? 0);

} elseif ($action === 'delete') {

    $presenter->delete($_GET['id'] ?? 0);

} else {

    $presenter->index();

}

?>
