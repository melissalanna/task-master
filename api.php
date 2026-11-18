<?php
// API REST simples do Task Master: responde somente em JSON.

require_once __DIR__ . '/src/Model/Task.php';

header('Content-Type: application/json; charset=utf-8');

function respond(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    $databasePath = getenv('TASK_DB_PATH') ?: (__DIR__ . '/tasks.sqlite');
    $pdo = new PDO('sqlite:' . $databasePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $model = new Task($pdo);

    $action = $_GET['action'] ?? 'list';

    switch ($action) {
        case 'list':
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                respond(['error' => 'Método não permitido.'], 405);
            }
            respond($model->getAll());

        case 'create':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                respond(['error' => 'Método não permitido.'], 405);
            }

            $data = json_decode(file_get_contents('php://input'), true);
            if (!is_array($data)) {
                respond(['error' => 'Envie os dados da tarefa em JSON válido.'], 400);
            }

            // A assinatura do Model é: título, descrição, vencimento, responsável.
            $model->save(
                (string) ($data['title'] ?? ''),
                (string) ($data['description'] ?? ''),
                (string) ($data['due_date'] ?? ''),
                (string) ($data['responsible'] ?? '')
            );
            respond(['status' => 'success', 'message' => 'Tarefa cadastrada.'], 201);

        case 'complete':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                respond(['error' => 'Método não permitido.'], 405);
            }
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if (!$id || $id < 1) {
                respond(['error' => 'ID de tarefa inválido.'], 400);
            }
            $model->complete($id);
            respond(['status' => 'success', 'message' => 'Tarefa concluída.']);

        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                respond(['error' => 'Método não permitido.'], 405);
            }
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if (!$id || $id < 1) {
                respond(['error' => 'ID de tarefa inválido.'], 400);
            }
            $model->delete($id);
            respond(['status' => 'success', 'message' => 'Tarefa excluída.']);

        default:
            respond(['error' => 'Ação não encontrada.'], 404);
    }
} catch (InvalidArgumentException $exception) {
    respond(['error' => $exception->getMessage()], 400);
} catch (Throwable $exception) {
    respond(['error' => 'Erro interno ao processar a solicitação.'], 500);
}
