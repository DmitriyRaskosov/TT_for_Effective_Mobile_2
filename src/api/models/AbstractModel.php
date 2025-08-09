<?php

require_once __DIR__.'/../../Database.php';
abstract class AbstractModel {
    public ?int $id = null;

    abstract protected static function getModelAttributes(): array;

    /**
     * Валидация атрибутов модели
     * @throws /Exception если в атрибуте ошибка
     */

    public static function validateAttribute($data): void
    {
        $allowedKeys = static::getModelAttributes();
        $inputKeys = array_keys($data);

        // Проверяем лишние поля
        if ($extraKeys = array_diff($inputKeys, $allowedKeys)) {
            throw new Exception('Недопустимые поля: ' . implode(', ', $extraKeys));
        }

        // Проверяем обязательные поля
        if ($missingKeys = array_diff($allowedKeys, $inputKeys)) {
            throw new Exception('Отсутствуют обязательные поля: ' . implode(', ', $missingKeys));
        }
        // Проверка значений
        foreach ($data as $key => $value) {
            if ($value === null) {
                throw new Exception("Пустое значение атрибута: $key");
            }
        }
    }

    /**
     * Получение одной записи по ID
     * @throws /Exception если запись не была найдена
     */
    public static function getOne(int $id): bool|array
    {
        $data = Database::getConnection()->prepare('SELECT * FROM `'.static::$table_name.'` WHERE `id` = :id');
        $data->execute([':id' => $id]);
        $result = $data->fetchAll();
        if (empty($result)) {
            throw new Exception("Запись не найдена");
        }
        return $result;
    }

    /**
     * Получение всех записей
     */
    public static function getAll(): bool|array
    {
        $sql = "SELECT * FROM tasks";
        $data = Database::getConnection()->prepare($sql);
        $data->execute();
        return $data->fetchAll();
    }

    /**
     * Создание новой записи
     * @throws /Exception при провале валидации
     */
    public static function create(array $data): array
    {
        static::validateAttribute($data);
        $columns = [];
        $placeholders = [];
        $params = [];

        foreach ($data as $key => $value) {
            $columns[] = "`$key`";
            $placeholders[] = '?';
            $params[] = $value;
        }

        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s)',
            static::$table_name,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $data = Database::getConnection()->prepare($sql);
        $data->execute($params);

        return [
            'id' => Database::getConnection()->lastInsertId(),
            'status' => 'done'
        ];
    }

    /**
     * Обновление записи
     * @throws /Exception если запись не была найдена
     */
    public static function update(array $data, int $id): array {
        static::getOne($id);
        static::validateAttribute($data);

        $updates = [];
        $params = [];

        foreach ($data as $key => $value) {
            $updates[] = "`$key` = ?";
            $params[] = $value;
        }

        $sql = sprintf(
            'UPDATE `%s` SET %s WHERE `id` = ?',
            static::$table_name,
            implode(', ', $updates)
        );

        $params[] = $id;

        $data = Database::getConnection()->prepare($sql);
        $data->execute($params);

        return [
            'id' => $id,
            'status' => 'updated'
        ];
    }

    /**
     * Удаление записи
     * @throws /Exception если запись не была найдена
     */
    public static function delete(int $id): array {
        static::getOne($id); // Проверка существования записи

        $sql = 'DELETE FROM `'.static::$table_name.'` WHERE `id` ='.$id;
        $data = Database::getConnection()->prepare($sql);
        $data->execute();

        return [
            'id' => $id,
            'status' => 'deleted'
        ];
    }
}