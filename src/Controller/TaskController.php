
<?php

class TaskController
{
    private Task $taskModel;

    public function __construct(PDO $pdo)
    {
        $this->taskModel = new Task($pdo);
    }

    /**
     * Exibe a página principal com todas as tarefas.
     */
    public function index(string $error = ''): void
    {
        $tasks = $this->taskModel->getAll();

        require __DIR__ . '/../View/list.php';
    }

    /**
     * Recebe os dados do formulário e cadastra uma tarefa.
     */
    public function create(): void
    {
        try {
            $this->taskModel->save(
                $_POST['title'] ?? '',
                $_POST['description'] ?? '',
                $_POST['due_date'] ?? '',
                $_POST['responsible'] ?? ''
            );

            header('Location: index.php');
            exit;

        } catch (InvalidArgumentException $exception) {
            $this->index($exception->getMessage());
        }
    }

    /**
     * Marca uma tarefa como concluída.
     */
    public function complete(): void
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if ($id && $id > 0) {
            $this->taskModel->complete($id);
        }

        header('Location: index.php');
        exit;
    }

    /**
     * Exclui uma tarefa.
     */
    public function delete(): void
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if ($id && $id > 0) {
            $this->taskModel->delete($id);
        }

        header('Location: index.php');
        exit;
    }
}
