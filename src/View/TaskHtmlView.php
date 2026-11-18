
<?php

class TaskHtmlView implements TaskViewInterface {

    public function displayTasks(array $tasks) {
        require __DIR__ . '/list.php';
    }

    public function showError(string $message) {
        $error = $message;
        $tasks = [];

        require __DIR__ . '/list.php';
    }

}
