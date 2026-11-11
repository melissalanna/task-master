
<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Model/Task.php';

class TaskModelTest extends TestCase
{
    private PDO $pdo;
    private Task $taskModel;

    /**
     * Executado antes de cada teste.
     * Cria um banco temporário na memória.
     */
    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');

        $this->pdo->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );

        $this->taskModel = new Task($this->pdo);
    }

    /**
     * Verifica se o sistema rejeita uma tarefa sem título.
     */
    public function testNaoPermiteCadastrarTarefaSemTitulo(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->expectExceptionMessage(
            'Título, data e responsável são obrigatórios.'
        );

        $this->taskModel->save(
            '',
            'Descrição da tarefa',
            '2026-11-12',
            'Responsável'
        );
    }

    /**
     * Verifica se uma tarefa é cadastrada corretamente.
     */
    public function testCadastraUmaTarefaCorretamente(): void
    {
        $this->taskModel->save(
            'Estudar MVC',
            'Finalizar a atividade',
            '2026-11-12',
            'Responsável'
        );

        $tasks = $this->taskModel->getAll();

        $this->assertCount(1, $tasks);
        $this->assertSame('Estudar MVC', $tasks[0]['title']);
        $this->assertSame('Responsável', $tasks[0]['responsible']);
    }

    /**
     * Verifica se uma tarefa pode ser concluída.
     */
    public function testConcluiUmaTarefa(): void
    {
        $this->taskModel->save(
            'Testar aplicação',
            '',
            '2026-11-12',
            'Responsável'
        );

        $tasks = $this->taskModel->getAll();

        $id = (int) $tasks[0]['id'];

        $this->taskModel->complete($id);

        $tasks = $this->taskModel->getAll();

        $this->assertSame(1, (int) $tasks[0]['done']);
    }

    /**
     * Verifica se uma tarefa pode ser excluída.
     */
    public function testExcluiUmaTarefa(): void
    {
        $this->taskModel->save(
            'Tarefa temporária',
            '',
            '2026-11-12',
            'Responsável'
        );

        $tasks = $this->taskModel->getAll();

        $id = (int) $tasks[0]['id'];

        $this->taskModel->delete($id);

        $this->assertCount(0, $this->taskModel->getAll());
    }
}
