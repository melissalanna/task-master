
<?php

$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Task Master</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f3f4f6;
            color: #333;
            display: flex;
            justify-content: center;
            padding: 30px 15px;
            margin: 0;
        }

        .container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 600px;
        }

        h1 {
            text-align: center;
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
        }

        .error {
            color: #991b1b;
            background: #fee2e2;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .form-group {
            margin: 15px 0;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 6px;
        }

        input, textarea {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-family: inherit;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }

        button:hover {
            background: #1d4ed8;
        }

        h2 {
            margin-top: 30px;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            gap: 15px;
        }

        .task-info {
            flex: 1;
            min-width: 0;
        }

        .task-title {
            font-weight: bold;
            font-size: 1.05rem;
        }

        .task-description {
            color: #666;
            margin-top: 6px;
            overflow-wrap: anywhere;
        }

        .task-details {
            font-size: 0.9rem;
            color: #555;
            margin-top: 8px;
            line-height: 1.7;
        }

        li.done .task-title {
            text-decoration: line-through;
            color: #999;
        }

        .actions {
            white-space: nowrap;
        }

        .actions a {
            text-decoration: none;
            margin-left: 5px;
        }

        .empty {
            text-align: center;
            color: #777;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Task Master</h1>

    <?php if (!empty($error)): ?>
        <div class="error">
            <?= $escape($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?action=create">

        <div class="form-group">
            <label for="title">Título da tarefa *</label>
            <input
                type="text"
                id="title"
                name="title"
                placeholder="O que precisa ser feito?"
                required
            >
        </div>

        <div class="form-group">
            <label for="description">Descrição (opcional)</label>
            <textarea
                id="description"
                name="description"
                placeholder="Descreva os detalhes da tarefa..."
            ></textarea>
        </div>

        <div class="form-group">
            <label for="due_date">Data de vencimento *</label>
            <input
                type="date"
                id="due_date"
                name="due_date"
                required
            >
        </div>

        <div class="form-group">
            <label for="responsible">Responsável *</label>
            <input
                type="text"
                id="responsible"
                name="responsible"
                placeholder="Nome do responsável"
                required
            >
        </div>

        <button type="submit">Adicionar tarefa</button>

    </form>

    <h2>Minhas tarefas</h2>

    <ul>

        <?php foreach ($tasks as $task): ?>

            <li class="<?= !empty($task['done']) ? 'done' : '' ?>">

                <div class="task-info">

                    <div class="task-title">
                        <?= $escape($task['title']) ?>
                    </div>

                    <?php if (!empty($task['description'])): ?>
                        <div class="task-description">
                            <?= $escape($task['description']) ?>
                        </div>
                    <?php endif; ?>

                    <div class="task-details">

                        <div>
                            <strong>Vencimento:</strong>

                            <?php
                            $dueDate = $task['due_date'] ?? '';

                            echo $dueDate !== ''
                                ? date('d/m/Y', strtotime($dueDate))
                                : 'Não informado';
                            ?>
                        </div>

                        <div>
                            <strong>Responsável:</strong>
                            <?= $escape($task['responsible'] ?? 'Não informado') ?>
                        </div>

                    </div>

                </div>

                <div class="actions">

                    <?php if (empty($task['done'])): ?>
                        <a
                            href="index.php?action=complete&id=<?= (int) $task['id'] ?>"
                            title="Concluir tarefa"
                        >✅</a>
                    <?php endif; ?>

                    <a
                        href="index.php?action=delete&id=<?= (int) $task['id'] ?>"
                        onclick="return confirm('Deseja realmente excluir esta tarefa?');"
                        title="Excluir tarefa"
                    >🗑️</a>

                </div>

            </li>

        <?php endforeach; ?>

    </ul>

    <?php if (empty($tasks)): ?>
        <p class="empty">Nenhuma tarefa cadastrada.</p>
    <?php endif; ?>

</div>

</body>
</html>
