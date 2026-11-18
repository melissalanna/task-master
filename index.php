
<?php

spl_autoload_register(function ($class) {
    $dirs = ['Model', 'Presenter', 'View'];

    foreach ($dirs as $dir) {
        $file = __DIR__ . "/src/$dir/$class.php";

        if (file_exists($file)) {
            require_once $file;
        }
    }
});

// Conexão com o banco SQLite
$databasePath = __DIR__ . '/tasks.sqlite';

$pdo = new PDO('sqlite:' . $databasePath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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

// Instanciação do MVP
$model = new Task($pdo);
$view = new TaskHtmlView();
$presenter = new TaskPresenter($model, $view);

// Roteamento
$action = $_GET['action'] ?? 'index';

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
