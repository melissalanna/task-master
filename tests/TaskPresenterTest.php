
<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Model/Task.php';
require_once __DIR__ . '/../src/View/TaskViewInterface.php';
require_once __DIR__ . '/../src/Presenter/TaskPresenter.php';

class TaskPresenterTest extends TestCase {

    public function testIndexFormataTitulosParaMaiusculo() {

        // Mock do Model
        $modelMock = $this->createStub(Task::class);

        $modelMock->method('getAll')->willReturn([
            [
                'id' => 1,
                'title' => 'estudar php',
                'description' => '',
                'due_date' => '',
                'done' => 0
            ]
        ]);

        // Mock da View
        $viewMock = $this->createMock(TaskViewInterface::class);

        // Verifica se o título foi convertido
        $viewMock->expects($this->once())
            ->method('displayTasks')
            ->with($this->callback(function($tasks) {
                return $tasks[0]['title'] === 'ESTUDAR PHP';
            }));

        // Executa o Presenter
        $presenter = new TaskPresenter($modelMock, $viewMock);
        $presenter->index();
    }
}
