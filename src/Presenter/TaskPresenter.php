
<?php

class TaskPresenter {

    private $model;
    private $view;

    public function __construct(Task $model, TaskViewInterface $view) {
        $this->model = $model;
        $this->view = $view;
    }

    public function index() {
        try {
            $tasks = $this->model->getAll();

            // Transforma os títulos em maiúsculo
            foreach ($tasks as &$task) {
                $task['title'] = strtoupper($task['title']);
            }
            unset($task);

            // Envia as tarefas para a View
            $this->view->displayTasks($tasks);

        } catch (Exception $e) {
            $this->view->showError($e->getMessage());
        }
    }

    public function create($title, $description, $dueDate) {
        try {
            $this->model->save($title, $description, $dueDate);
            header("Location: index.php");
        } catch (Exception $e) {
            $this->view->showError($e->getMessage());
        }
    }

    public function complete($id) {
        $this->model->complete($id);
        header("Location: index.php");
    }

    public function delete($id) {
        $this->model->delete($id);
        header("Location: index.php");
    }

}
