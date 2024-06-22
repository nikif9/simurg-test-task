<?php

namespace App\Models;

class Field
{
    public string $name;
    public string $type;
    public $value;
    public ?string $format;

    public function __construct(string $name, string $type, $value, ?string $format = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->value = $value;
        $this->format = $format;
    }

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
