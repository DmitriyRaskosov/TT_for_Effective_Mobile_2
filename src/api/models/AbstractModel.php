<?php

require_once __DIR__.'/../../Database.php';
abstract class AbstractModel {
    public ?int $id = null;

    /**
     * Валидация атрибутов модели
     * @throws /Exception если в атрибуте ошибка
     */
    public static function validateAttribute(string $attribute): void
    {
        if (!in_array($attribute, static::$attributes)) {
            throw new Exception("Неверный атрибут: $attribute");
        }
    }

    /**
     * Получение одной записи по ID
     * @throws /Exception если запись не была найдена
     */
    public static function getOne(int $id): bool|array
    {
        $data = Database::getConnection()->prepare('SELECT * FROM `'.static::$table_name.'` WHERE `id` ='.$id);
        $data->execute();
        if (empty($data)) {
            throw new Exception("Запись не найдена");
        }
        return $data->fetchAll();
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
        $columns = [];
        $placeholders = [];
        $params = [];

        foreach ($data as $key => $value) {
            static::validateAttribute($key);

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

        $updates = [];
        $params = [];

        foreach ($data as $key => $value) {
            static::validateAttribute($key);

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