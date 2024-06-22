<?php

use PHPUnit\Framework\TestCase;
use App\Services\ProcessService;
use App\Models\Field;
use App\Models\Process;

class ProcessServiceTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite:business_processes.db');
        $this->db->exec("
            CREATE TABLE processes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE
            );

            CREATE TABLE fields (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                process_id INTEGER NOT NULL,
                name TEXT NOT NULL,
                type TEXT NOT NULL,
                value TEXT,
                format TEXT,
                FOREIGN KEY (process_id) REFERENCES processes(id)
            );
        ");
    }

    public function testCreateProcess()
    {
        $service = new ProcessService($this->db);

        $fields = [
            ['name' => 'Text', 'type' => 'text', 'value' => 'Text'],
            ['name' => 'Number', 'type' => 'number', 'value' => 455, 'format' => '%+.2f'],
            ['name' => 'Date', 'type' => 'date', 'value' => '2023-03-12', 'format' => 'd.m.Y'],
        ];

        $process = $service->createProcess('TestProcess', $fields);

        $this->assertInstanceOf(Process::class, $process);
        $this->assertEquals('TestProcess', $process->name);
        $this->assertCount(3, $process->getFields());

        $fieldsFromProcess = $process->getFields();
        $this->assertEquals('Text', $fieldsFromProcess['Text']->value);
        $this->assertEquals(sprintf('%+.2f', 455), $fieldsFromProcess['Number']->getFormattedValue());
        $this->assertEquals('12.03.2023', $fieldsFromProcess['Date']->getFormattedValue());
    }

    public function testGetProcesses()
    {
        $service = new ProcessService($this->db);

        $fields = [
            ['name' => 'Text', 'type' => 'text', 'value' => 'Text'],
            ['name' => 'Number', 'type' => 'number', 'value' => 455, 'format' => '%+.2f'],
            ['name' => 'Date', 'type' => 'date', 'value' => '2023-03-12', 'format' => 'd.m.Y'],
        ];

        $service->createProcess('TestProcess1', $fields);
        $service->createProcess('TestProcess2', $fields);

        $processes = $service->getProcesses(1, 1);
        $this->assertCount(1, $processes);

        $processes = $service->getProcesses(1, 2);
        $this->assertCount(2, $processes);
    }
}
