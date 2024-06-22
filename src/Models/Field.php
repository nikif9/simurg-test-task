<?php

namespace App\Models;

class Field
{
    public string $name;
    public string $type;
    public $value;
    public ?string $format;

    /**
     * @param string $name Имя поля
     * @param string $type Тип поля
     * @param mixed $value Значение поля
     * @param string|null $format Формат для числа или даты, если применимо
     */
    public function __construct(string $name, string $type, $value, ?string $format = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->value = $value;
        $this->format = $format;
    }
    /**
     * Возвращает отформатированное значение поля.
     *
     * @return string Отформатированное значение
     */
    public function getFormattedValue()
    {
        switch ($this->type) {
            case 'number':
                return sprintf($this->format, $this->value);
            case 'date':
                return (new \DateTime($this->value))->format($this->format);
            default:
                return $this->value;
        }
    }
}
