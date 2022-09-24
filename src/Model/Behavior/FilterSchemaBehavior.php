<?php

declare(strict_types=1);

namespace App\Model\Behavior;

use Cake\Database\Schema\TableSchemaInterface;
use Cake\ORM\Behavior;

class FilterSchemaBehavior extends Behavior
{
    private TableSchemaInterface $schema;

    public function getFilterSchema(): array
    {
        $properties = [];
        foreach ($this->getTableSchema()->columns() as $column) {
            $name = $this->table()->getAlias() . '.' . $column;

            $properties[] = [
                'name' => $name,
                'label' => $this->translatedBaseTableName() . ' > ' . $this->translatedPropertyName($column),
                'options' => $this->getOptions($column),
            ];
        }

        return $properties;
    }

    private function getTableSchema()
    {
        if (!isset($this->schema)) {
            $this->schema = $this->table()->getSchema();
        }

        return $this->schema;
    }

    private function translatedBaseTableName(): string
    {
        if (method_exists($this->table(), 'getTranslatedName')) {
            return $this->table()->getTranslatedName();
        }

        return $this->table()->getAlias();
    }

    private function translatedPropertyName(string $column): string
    {
        if (method_exists($this->table(), 'getTranslatedColumnName')) {
            return $this->table()->getTranslatedColumnName($column) ?? $column;
        }

        return $column;
    }

    private function getOptions(string $columnName): array
    {
        $column = $this->getTableSchema()->getColumn($columnName);
        $type = $this->isEnumProperty($columnName)
            ? 'enum'
            : $this->mapDataType($column['type']);

        $options = [
            'type' => $type,
            'allowEmpty' => $column['null'],
        ];

        $validation = $this->validationRules($columnName, $type, $column);
        if ($validation) {
            $options['validation'] = $validation;
        }

        return $options;
    }

    private function isEnumProperty(string $column): bool
    {
        if (method_exists($this->table(), 'getSelectFields')) {
            $enumFields = $this->table()->getSelectFields();
            return in_array($column, $enumFields, true);
        }

        return false;
    }

    private function mapDataType(string $type): string
    {
        $map = [
            /* cakeisch db types => frontend data types */
            'string' => 'string',
            'text' => 'string',
            'char' => 'string',
            'integer' => 'integer',
            'smallinteger' => 'integer',
            'tinyinteger' => 'integer',
            'biginteger' => 'integer',
            'float' => 'double',
            'decimal' => 'double',
            'boolean' => 'boolean',
            'date' => 'date',
            'datetime' => 'datetime',
            'timestamp' => 'integer',
            'time' => 'time',
            'uuid' => 'string',
        ];

        return $map[$type];
    }

    private function validationRules(string $columnName, string $type, array $column): false|array
    {
        $methodName = "get{$columnName}FrontendValidationRules";
        if (method_exists($this->table(), $methodName)) {
            return $this->table()->$methodName();
        }

        return match ($type) {
            'string' => ['maxLen' => $column['length'], 'pattern' => null],
            'integer' => ['min' => PHP_INT_MIN, 'max' => PHP_INT_MAX, 'step' => 1],
            'double' => ['min' => PHP_FLOAT_MIN, 'max' => PHP_FLOAT_MAX, 'step' => null],
            'enum' => ['options' => $this->enumValues($columnName)],
            default => false
        };
    }

    private function enumValues(string $columnName)
    {
        $result = $this->table()->find()
            ->enableHydration(false)
            ->select([$columnName])
            ->distinct()
            ->orderAsc($columnName);

        return $result->all()->map(static fn($item) => $item[$columnName]);
    }
}
