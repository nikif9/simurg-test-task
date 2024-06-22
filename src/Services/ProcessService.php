<?php

namespace App\Services;

use PDO;
use App\Models\Process;
use App\Models\Field;

class ProcessService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function createProcess(string $name, array $fieldConfigs): Process
    {
        $process = new Process($name, $this->db);

        foreach ($fieldConfigs as $config) {
            $field = new Field($config['name'], $config['type'], $config['value'], $config['format'] ?? null);
            $process->addField($field);
        }

        $process->save();

        return $process;
    }

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
