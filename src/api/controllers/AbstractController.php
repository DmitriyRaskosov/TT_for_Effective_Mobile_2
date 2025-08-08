<?php

require_once __DIR__.'/../models/AbstractModel.php';
require_once __DIR__.'/../models/Tasks.php';

abstract class AbstractController {

    /**
     * @var string $model_name наименование модели
     */
    public static string $model_name = 'AbstractModel';

    /**
     * Метод получения какого-то единственного блока данных
     * @param integer $id поиск блока данных осуществляется по его id
     * @return mixed возврат найденного блока данных
     */
    public function getOne(int $id): mixed
    {
        $model_name = static::$model_name;
        return $model_name::getOne($id);
    }

    /**
     * Метод получения всех блоков данных
     * @return mixed возврат найденных данных
     */
    public function getAll(): mixed
    {
        $model_name = static::$model_name;
        return $model_name::getAll();
    }

    /**
     * Метод создания новых данных
     * @param array $post массив с новыми данными
     * @return mixed возвращает данные
     */
    public function create(array $post): mixed
    {
        $model_name = static::$model_name;
        return $model_name::create($post);
    }

    /**
     * Метод обновления данных
     * @param array $put массив данных, которые заменят одноимённые
     * @param integer $id id блока данных, в котором будет происходить замена
     * @return mixed возвращает успешно заменённые данные
     */
    public function update(array $put, int $id): mixed
    {
        $model_name = static::$model_name;
        return $model_name::update($put, $id);
    }

    /**
     * Метод удаления данных
     * @param integer $id поиск блока данных осуществляется по его id
     * @return mixed возвращает результат выполнения функции pg_execute
     */
    public function delete(int $id): mixed
    {
        $model_name = static::$model_name;
        return $model_name::delete($id);
    }

}
