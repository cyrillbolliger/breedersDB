<?php

namespace App\Domain;

use App\Model\Entity\Setting;
use App\Model\Table\SettingsTable;
use Cake\Datasource\FactoryLocator;

class Settings
{
    public const ZPL_DRIVER_OFFSET_LEFT = 'zpl_driver_offset_left';

    private static array $cache;
    private static SettingsTable $table;

    public static function getZplDriverOffsetLeft(): int
    {
        return (int)self::get(self::ZPL_DRIVER_OFFSET_LEFT);
    }

    public static function setZplDriverOffsetLeft(int $left): void
    {
        self::set(self::ZPL_DRIVER_OFFSET_LEFT, (string)$left);
    }

    private static function get(string $key): string|null
    {
        if (empty(self::$cache)) {
            $entities = self::getTable()->find();
            foreach ($entities as $entity) {
                self::$cache[$key] = $entity->setting_value;
            }
        }

        return self::$cache[$key] ?? null;
    }

    private static function getEntity(string $key): Setting
    {
        $entity = self::getTable()->find()
            ->where(['setting_key' => $key])
            ->first();

        if (!$entity) {
            $entity = self::getTable()->newEmptyEntity();
            $entity->setting_key = $key;
        }

        return $entity;
    }

    private static function set(string $key, string $value): void
    {
        $entity = self::getEntity($key);
        $entity->setting_value = $value;
        self::getTable()->saveOrFail($entity);
        self::$cache[$key] = $value;
    }

    private static function getTable(): SettingsTable
    {
        if (empty(self::$table)) {
            $tableLocator = FactoryLocator::get('Table');
            /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
            self::$table = $tableLocator->get('Settings');
        }

        return self::$table;
    }
}
