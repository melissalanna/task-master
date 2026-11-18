<?php

use PHPUnit\Framework\TestCase;

/** Teste de integração: inicia um servidor PHP temporário e chama a API HTTP. */
class ApiTest extends TestCase
{
    private static $serverProcess;
    private static string $baseUrl = '';
    private static string $databasePath = '';

    public static function setUpBeforeClass(): void
    {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true) || !function_exists('proc_open')) {
            return;
        }

        $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
        if (!$socket) throw new RuntimeException('Não foi possível reservar uma porta para o servidor de teste.');
        $address = stream_socket_get_name($socket, false);
        fclose($socket);
        $port = (int) substr(strrchr($address, ':'), 1);

        self::$databasePath = sys_get_temp_dir() . '/task-master-api-' . uniqid('', true) . '.sqlite';
        self::$baseUrl = 'http://127.0.0.1:' . $port;
        $root = dirname(__DIR__);
        $command = [PHP_BINARY, '-S', '127.0.0.1:' . $port, '-t', $root];
        $descriptors = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $environment = [];

foreach (array_merge($_SERVER, $_ENV) as $key => $value) {
    if (is_scalar($value)) {
        $environment[$key] = (string) $value;
    }
}

$environment['TASK_DB_PATH'] = self::$databasePath;
        self::$serverProcess = proc_open($command, $descriptors, $pipes, $root, $environment);
        if (!is_resource(self::$serverProcess)) throw new RuntimeException('Não foi possível iniciar o servidor PHP.');
        foreach ($pipes as $pipe) {
            if (is_resource($pipe)) stream_set_blocking($pipe, false);
        }

        $ready = false;
        for ($attempt = 0; $attempt < 40; $attempt++) {
            $connection = @fsockopen('127.0.0.1', $port, $errorCode, $errorMessage, 0.1);
            if ($connection) {
                fclose($connection);
                $ready = true;
                break;
            }
            usleep(100000);
        }
        if (!$ready) throw new RuntimeException('O servidor PHP não iniciou a tempo.');
    }

    public static function tearDownAfterClass(): void
    {
        if (is_resource(self::$serverProcess)) {
            proc_terminate(self::$serverProcess);
            proc_close(self::$serverProcess);
        }
        if (isset(self::$databasePath) && file_exists(self::$databasePath)) {
            unlink(self::$databasePath);
        }
    }

    public function testApiRetornaListaEmJson(): void
    {
        $this->skipIfRequirementsAreMissing();
        [$status, $content, $headers] = $this->httpRequest('/api.php?action=list');
        $data = json_decode($content, true);

        $this->assertSame(200, $status);
        $this->assertIsArray($data);
        $this->assertStringContainsString('application/json', implode("\n", $headers));
    }

    public function testApiCadastraTarefaEDevolveNaLista(): void
    {
        $this->skipIfRequirementsAreMissing();
        $task = [
            'title' => 'Testar MVVM',
            'description' => 'Teste de integração da API',
            'due_date' => '2026-12-20',
            'responsible' => 'Melissa'
        ];
        [$status] = $this->httpRequest('/api.php?action=create', 'POST', $task);
        $this->assertSame(201, $status);

        [, $content] = $this->httpRequest('/api.php?action=list');
        $tasks = json_decode($content, true);
        $this->assertIsArray($tasks);
        $this->assertSame('Testar MVVM', $tasks[0]['title']);
        $this->assertSame('Melissa', $tasks[0]['responsible']);
    }

    private function skipIfRequirementsAreMissing(): void
    {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('A extensão pdo_sqlite é necessária para testar a API.');
        }
        if (!function_exists('proc_open')) {
            $this->markTestSkipped('A função proc_open é necessária para iniciar o servidor de teste.');
        }
        if (self::$baseUrl === '') {
            $this->markTestSkipped('O servidor de teste não foi iniciado.');
        }
    }

    private function httpRequest(string $path, string $method = 'GET', ?array $body = null): array
    {
        $headers = ['Content-Type: application/json'];
        $context = stream_context_create(['http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers),
            'content' => $body === null ? '' : json_encode($body),
            'ignore_errors' => true,
            'timeout' => 5
        ]]);
        $content = file_get_contents(self::$baseUrl . $path, false, $context);
        $responseHeaders = $http_response_header ?? [];
        preg_match('/HTTP\/\S+\s+(\d+)/', $responseHeaders[0] ?? '', $matches);
        return [(int) ($matches[1] ?? 0), $content === false ? '' : $content, $responseHeaders];
    }
}
