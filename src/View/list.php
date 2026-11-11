
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Task Master MVC</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 750px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
        }

        h1 {
            color: #2563eb;
            text-align: center;
        }

        input, button {
            width: 100%;
            padding: 10px;
            margin: 6px 0;
            box-sizing: border-box;
        }

        button {
            background: #2563eb;
            color: white;
            border: none;
            cursor: pointer;
        }

        li {
            background: #f1f5f9;
            padding: 15px;
            margin: 10px 0;
            list-style: none;
            border-radius: 6px;
        }

        .done {
            opacity: 0.6;
            text-decoration: line-through;
        }

        .error {
            color: red;
            margin: 10px 0;
        }

        .actions a {
            margin-right: 10px;
            text-decoration: none;
        }
    </style>
</head>

<body>
<div class="container">

    <h1>Task Master (MVC Edition)</h1>

    <?php if (isset($error)): ?>
        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?action=create">
        <input type="text" name="title" placeholder="Título" required>

        <input type="text" name="description" placeholder="Descrição">

        <input type="date" name="due_date" required>

        <input type="text" name="responsible" placeholder="Responsável" required>

        <button type="submit">Adicionar tarefa</button>
    </form>

    <ul>
        <?php foreach ($tasks as $task): ?>
            <li class="<?php echo $task['done'] ? 'done' : ''; ?>">

                <strong>
                    <?php echo htmlspecialchars($task['title']); ?>
                </strong>

                <br>

                <small>
                    <?php echo htmlspecialchars($task['description'] ?? ''); ?>
                    <br>
                    Vence em: <?php echo htmlspecialchars($task['due_date']); ?>
                    <br>
                    Responsável: <?php echo htmlspecialchars($task['responsible'] ?? ''); ?>
                </small>

                <div class="actions">
                    <?php if (!$task['done']): ?>
                        <a href="index.php?action=complete&id=<?php echo $task['id']; ?>">✅ Concluir</a>
                    <?php endif; ?>

                    <a href="index.php?action=delete&id=<?php echo $task['id']; ?>">❌ Excluir</a>
                </div>

            </li>
        <?php endforeach; ?>
    </ul>

</div>
</body>
</html>
