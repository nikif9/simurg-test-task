<?php

namespace App\Services;

use PDO;
use App\Models\Process;
use App\Models\Field;

class ProcessService
{
    private PDO $db; // Объект базы данных

    /**
     * ProcessService constructor.
     *
     * @param PDO $db Объект базы данных
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Создает процесс с заданными полями.
     *
     * @param string $name Имя процесса
     * @param array $fieldConfigs Конфигурация полей
     * @return Process Созданный процесс
     */
    public function createProcess(string $name, array $fieldConfigs): Process
    {
        $process = new Process($name, $this->db);

        // Добавление полей в процесс
        foreach ($fieldConfigs as $config) {
            $field = new Field($config['name'], $config['type'], $config['value'], $config['format'] ?? null);
            $process->addField($field);
        }

        // Сохранение процесса и его полей в базу данных
        $process->save();

        return $process;
    }

    /**
     * Возвращает процессы с постраничной навигацией.
     *
     * @param int $page Номер страницы
     * @param int $perPage Количество элементов на странице
     * @return Process[] Массив процессов
     */
    public function getProcesses(int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare("SELECT * FROM processes LIMIT :limit OFFSET :offset");
        $stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $processes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $process = new Process($row['name'], $this->db);
            $process->id = $row['id'];

            // Получение полей для каждого процесса
            $fieldStmt = $this->db->prepare("SELECT * FROM fields WHERE process_id = :process_id");
            $fieldStmt->execute(['process_id' => $process->id]);
            while ($fieldRow = $fieldStmt->fetch(PDO::FETCH_ASSOC)) {
                $field = new Field($fieldRow['name'], $fieldRow['type'], $fieldRow['value'], $fieldRow['format']);
                $process->addField($field);
            }

            $processes[] = $process;
        }

        return $processes;
    }
}
