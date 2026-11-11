
<?php

interface TaskViewInterface {

    // A View deve saber exibir uma lista de tarefas
    public function displayTasks(array $tasks);

    // A View deve saber exibir uma mensagem de erro
    public function showError(string $message);

}
