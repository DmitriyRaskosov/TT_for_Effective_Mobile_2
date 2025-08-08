<?php

require_once __DIR__.'/Database.php';
require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/api/controllers/TasksController.php';

use Swagger\Annotations as SWG;

class Api {

    protected string $request_method;
    protected ?string $ctrl_request = null;
    protected array $request_data = [];

    /**
     * Создание экземпляра, данные получаем через parseRequestData()
     */
    public function __construct(string $method, array $request_uri)
    {
        $this->request_method = $method;
        $this->ctrl_request = $request_uri[1] ?? null;
        $this->parseRequestData();
    }

    /**
     * Здесь разбираются входные данные
     * @throws /Exception в parseJsonInput()
     */
    protected function parseRequestData(): void {
        $this->request_data = match($this->request_method) {
            'GET',
            'DELETE'    => $_GET,
            'POST'  => $_POST,
            'PUT',  => $this->parseJsonInput(),
            default  => []
        };
    }

    /**
     * Парсер
     * @throws /Exception если невалидный JSON
     */
    private function parseJsonInput(): array {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400); // Ошибка "Bad Request"
            throw new Exception("Invalid JSON");
        }
        return $data ?? [];
    }

    /**
     * Обработчик запроса, выводит данные через sendResponse()
     */
    public function handleRequest(): void
    {
        if (!$this->validateRequest()) {
            $this->sendResponse(['error' => 'Invalid request'], 400);
            return;
        }
        try {
            $controller_name = $this->ctrl_request.'Controller';
            if (!class_exists($controller_name)) {
                throw new Exception("Controller not found");
            }

            $controller = new $controller_name;
            $result = $this->routeRequest($controller);
            $this->sendResponse($result);
        } catch (Exception $e) {
            $this->sendResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Проверка корректности запроса
     */
    protected function validateRequest(): bool
    {
        return $this->ctrl_request !== null
            && in_array($this->request_method, ['GET', 'POST', 'PUT', 'DELETE']);
    }

    /**
     * Маршрутизация
     */
    protected function routeRequest($controller): array
    {
        return match($this->request_method) {
            'GET'    => $this->handleGet($controller),
            'POST'   => $controller->create($this->request_data),
            'PUT'    => $controller->update($this->request_data['filter'] ?? '', $this->request_data['id']),
            'DELETE' => $controller->delete($this->request_data['id'])
        };
    }

    /**
     * Обработка GET-запросов
     */
    protected function handleGet($controller): array
    {
        if (isset($this->request_data['id']) && $this->request_data['id'] !== '') {
            return $controller->getOne($this->request_data['id']);
        }
        return $controller->getAll();
    }

    /**
     * Метод вывода данных
     */
    protected function sendResponse(array $data, int $http_code = 200): void
    {
        http_response_code($http_code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

// Инициализация и запуск
$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri_segments = explode('/', trim($uri_path, '/'));
$api = new Api($_SERVER['REQUEST_METHOD'], $uri_segments);
$api->handleRequest();