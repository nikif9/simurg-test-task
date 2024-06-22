<?php

namespace App\Models;

use PDO;

class Process
{
    public int $id;
    public string $name;
    private array $fields = [];
    private PDO $db;

    /**
     *
     * @param string $name Имя процесса
     * @param PDO $db Объект базы данных
     */
    public function __construct(string $name, PDO $db)
    {
        $this->name = $name;
        $this->db = $db;
    }
    /**
     * Добавляет поле в процесс.
     *
     * @param Field $field Поле для добавления
     */
    public function addField(Field $field)
    {
        $this->fields[$field->name] = $field;
    }
    /**
     * Сохраняет процесс и его поля в базу данных.
     */
    public function save()
    {
        $stmt = $this->db->prepare("INSERT INTO processes (name) VALUES (:name)");
        $stmt->execute(['name' => $this->name]);
        $this->id = $this->db->lastInsertId();

        foreach ($this->fields as $field) {
            $stmt = $this->db->prepare("INSERT INTO fields (process_id, name, type, value, format) VALUES (:process_id, :name, :type, :value, :format)");
            $stmt->execute([
                'process_id' => $this->id,
                'name' => $field->name,
                'type' => $field->type,
                'value' => $field->value,
                'format' => $field->format
            ]);
        }
    }
    /**
     * Возвращает все поля процесса.
     *
     * @return Field[] Массив полей
     */
    public function getFields(): array
    {
        return $this->fields;
    }
}
