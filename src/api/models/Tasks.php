<?php

require_once __DIR__.'/AbstractModel.php';
require_once __DIR__.'/../../Database.php';

/**
 * Модель задач.
 */
class Tasks extends AbstractModel {

    public static string $table_name = 'tasks';
    public static array $attributes = ['title', 'description', 'status'];

}