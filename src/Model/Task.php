
<?php

class Task
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;

        $this->initializeDatabase();
    }

    /**
     * Cria a tabela e verifica se todas as colunas existem.
     * Também permite utilizar bancos criados na Atividade 1.
     */
    private function initializeDatabase(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS tasks (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                description TEXT,
                due_date TEXT,
                responsible TEXT,
                done INTEGER DEFAULT 0
            )
        ");

        $columns = $this->pdo
            ->query("PRAGMA table_info(tasks)")
            ->fetchAll(PDO::FETCH_COLUMN, 1);

        $newColumns = [
            'description' => 'TEXT',
            'due_date' => 'TEXT',
            'responsible' => 'TEXT',
            'done' => 'INTEGER DEFAULT 0'
        ];

        foreach ($newColumns as $column => $type) {
            if (!in_array($column, $columns, true)) {
                $this->pdo->exec(
                    "ALTER TABLE tasks ADD COLUMN {$column} {$type}"
                );
            }
        }
    }

    /**
     * Busca todas as tarefas cadastradas.
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->query("
            SELECT *
            FROM tasks
            ORDER BY id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Valida e cadastra uma nova tarefa.
     */
    public function save(
        string $title,
        string $description,
        string $dueDate,
        string $responsible
    ): void {
        $title = trim($title);
        $description = trim($description);
        $dueDate = trim($dueDate);
        $responsible = trim($responsible);

        if ($title === '' || $dueDate === '' || $responsible === '') {
            throw new InvalidArgumentException(
                'Título, data e responsável são obrigatórios.'
            );
        }

        $date = DateTime::createFromFormat('!Y-m-d', $dueDate);

        if (!$date || $date->format('Y-m-d') !== $dueDate) {
            throw new InvalidArgumentException(
                'Informe uma data de vencimento válida.'
            );
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO tasks (
                title,
                description,
                due_date,
                responsible
            )
            VALUES (
                :title,
                :description,
                :due_date,
                :responsible
            )
        ");

        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':due_date' => $dueDate,
            ':responsible' => $responsible
        ]);
    }

    /**
     * Marca uma tarefa como concluída.
     */
    public function complete(int $id): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE tasks
            SET done = 1
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $id
        ]);
    }

    /**
     * Exclui uma tarefa.
     */
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM tasks
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $id
        ]);
    }
}
