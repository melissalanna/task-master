
<?php
// ==========================================
// AULA 01: TASK MASTER - EXPANDINDO O SISTEMA
// ==========================================

// 1. CONEXÃO COM O BANCO DE DADOS
$dbFile = __DIR__ . '/tasks.sqlite';
$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Criar tabela caso não exista
$pdo->exec("CREATE TABLE IF NOT EXISTS tasks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    done INTEGER DEFAULT 0
)");

// Atualizar a tabela existente com os novos campos
$columns = $pdo->query("PRAGMA table_info(tasks)")->fetchAll(PDO::FETCH_COLUMN, 1);

if (!in_array('description', $columns)) {
    $pdo->exec("ALTER TABLE tasks ADD COLUMN description TEXT");
}

if (!in_array('due_date', $columns)) {
    $pdo->exec("ALTER TABLE tasks ADD COLUMN due_date TEXT");
}

if (!in_array('responsible', $columns)) {
    $pdo->exec("ALTER TABLE tasks ADD COLUMN responsible TEXT");
}

// 2. LÓGICA DE NEGÓCIO
$error = '';

// Criar nova tarefa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {

    $title = trim($_POST['title']);
    $description = trim($_POST['description'] ?? '');
    $dueDate = trim($_POST['due_date'] ?? '');
    $responsible = trim($_POST['responsible'] ?? '');

    if (empty($title)) {
        $error = "O título da tarefa é obrigatório!";
    } elseif (empty($dueDate)) {
        $error = "A data de vencimento é obrigatória!";
    } elseif (empty($responsible)) {
        $error = "O responsável pela tarefa é obrigatório!";
    } else {

        $stmt = $pdo->prepare("
            INSERT INTO tasks (title, description, due_date, responsible)
            VALUES (:title, :description, :due_date, :responsible)
        ");

        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':due_date' => $dueDate,
            ':responsible' => $responsible
        ]);

        header("Location: index.php");
        exit;
    }
}

// Concluir ou excluir tarefa
if (isset($_GET['action']) && isset($_GET['id'])) {

    $id = (int) $_GET['id'];

    if ($_GET['action'] === 'complete') {
        $stmt = $pdo->prepare("UPDATE tasks SET done = 1 WHERE id = :id");
        $stmt->execute([':id' => $id]);

    } elseif ($_GET['action'] === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    header("Location: index.php");
    exit;
}

// 3. BUSCA DE DADOS
$stmt = $pdo->query("SELECT * FROM tasks ORDER BY id DESC");
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Task Master - Expandido</title>

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            color: #333;
            display: flex;
            justify-content: center;
            padding: 30px 15px;
        }

        .container {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }

        h1 {
            font-size: 1.5rem;
            text-align: center;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }

        .error {
            color: #dc2626;
            background: #fee2e2;
            padding: 10px;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .form-group {
            margin-top: 15px;
            margin-bottom: 15px;
        }

        input, textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-family: inherit;
        }

        textarea {
            resize: vertical;
            min-height: 70px;
        }

        label {
            font-weight: bold;
            font-size: 0.9rem;
        }

        button {
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px 15px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background: #1d4ed8;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .task-info {
            flex: 1;
            min-width: 0;
        }

        .task-title {
            font-weight: bold;
            font-size: 1rem;
        }

        .task-description {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
            overflow-wrap: anywhere;
        }

        .task-details {
            font-size: 0.85rem;
            color: #555;
            margin-top: 8px;
        }

        li.done .task-title {
            text-decoration: line-through;
            color: #9ca3af;
        }

        .actions a {
            text-decoration: none;
            margin-left: 5px;
            cursor: pointer;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Task Master</h1>

    <?php if ($error): ?>
        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Formulário de cadastro de tarefas -->
    <form method="POST" action="index.php">

        <div class="form-group">
            <label for="title">Título da tarefa *</label>
            <input
                type="text"
                id="title"
                name="title"
                placeholder="O que precisa ser feito?"
                autocomplete="off"
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

    <ul>

        <?php foreach ($tasks as $task): ?>

            <li class="<?php echo $task['done'] ? 'done' : ''; ?>">

                <div class="task-info">

                    <div class="task-title">
                        <?php echo htmlspecialchars($task['title']); ?>
                    </div>

                    <?php if (!empty($task['description'])): ?>
                        <div class="task-description">
                            <?php echo htmlspecialchars($task['description']); ?>
                        </div>
                    <?php endif; ?>

                    <div class="task-details">

                        <div>
                            <strong>Vencimento:</strong>
                            <?php
                            echo !empty($task['due_date'])
                                ? date('d/m/Y', strtotime($task['due_date']))
                                : 'Não informado';
                            ?>
                        </div>

                        <div>
                            <strong>Responsável:</strong>
                            <?php
                            echo !empty($task['responsible'])
                                ? htmlspecialchars($task['responsible'])
                                : 'Não informado';
                            ?>
                        </div>

                    </div>

                </div>

                <div class="actions">

                    <?php if (!$task['done']): ?>
                        <a href="?action=complete&id=<?php echo $task['id']; ?>" title="Concluir">✅</a>
                    <?php endif; ?>

                    <a
                        href="?action=delete&id=<?php echo $task['id']; ?>"
                        onclick="return confirm('Tem certeza que deseja excluir esta tarefa?');"
                        title="Excluir"
                    >❌</a>

                </div>

            </li>

        <?php endforeach; ?>

    </ul>

</div>

</body>
</html>
