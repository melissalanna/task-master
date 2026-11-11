
<?php

class TaskController {
    private $model;

    public function __construct(PDO $pdo) {
        $this->model = new Task($pdo);
    }

    // Exibe a lista de tarefas
    public function index($error = null) {
        $tasks = $this->model->getAll();

        require __DIR__ . '/../View/list.php';
    }

    // Cria uma tarefa
    public function create() {
        try {
            $this->model->save(
                $_POST['title'] ?? '',
                $_POST['description'] ?? '',
                $_POST['due_date'] ?? '',
                $_POST['responsible'] ?? ''
            );

            header("Location: index.php");
            exit;

        } catch (Exception $e) {
            $this->index($e->getMessage());
        }
    }

    // Conclui uma tarefa
    public function complete() {
        $this->model->complete($_GET['id'] ?? 0);

        header("Location: index.php");
        exit;
    }

    // Exclui uma tarefa
    public function delete() {
        $this->model->delete($_GET['id'] ?? 0);

        header("Location: index.php");
        exit;
    }
}
