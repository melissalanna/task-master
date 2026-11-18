
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

            foreach ($tasks as &$task) {
                $task['title'] = strtoupper($task['title']);
            }
            unset($task);

            $this->view->displayTasks($tasks);

        } catch (Exception $e) {
            $this->view->showError($e->getMessage());
        }
    }

    public function create($title, $description, $responsible, $dueDate) {
        try {
            $this->model->save($title, $description, $responsible, $dueDate);
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
