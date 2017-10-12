<?php
/**
 * Created by PhpStorm.
 * User: cyrillbolliger
 * Date: 18.09.17
 * Time: 11:47
 */

namespace DBUpdate;

class Updater
{
    private $db;
    
    /**
     * Updater constructor.
     *
     * @param $dbconf
     */
    public function __construct(array $dbconf)
    {
        // get connection to db or return false
        try {
            $this->db = new \PDO("mysql: host={$dbconf['host']};dbname={$dbconf['database']};port={$dbconf['port']}", $dbconf['username'],
                $dbconf['password'], array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
        } catch (\PDOException $e) {
            return false;
        }
    }
    
    private function runUpdateRoutines(string $currentVersion) {
        if (1 === version_compare('1.0.0', $currentVersion)) {
            $this->updateTo1dot0dot0();
        }
    
        if (1 === version_compare('1.1.0', $currentVersion)) {
            $this->updateTo1dot1dot0();
        }
    }
    
    public function update(string $currentVersion): bool
    {
        // return false if connection to db could not be established
        if (empty($this->db)) {
            return false;
        }
        
        // get us all or nothing
        try {
            $this->db->beginTransaction();
            $this->runUpdateRoutines($currentVersion);
            $this->db->commit();
            return true;
        } catch(PDOException $ex) {
            // rollback on any error
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Update all values of type DATE in mark_values to match the format yyyy-mm-dd
     */
    private function updateTo1dot0dot0()
    {
        // get ids of proprerties with field_type DATE
        $array = [];
        $tmp   = $this->getAssoc("SELECT id FROM mark_form_properties WHERE field_type = 'DATE'");
        foreach ($tmp as $result) {
            $array[] = $result['id'];
        }
        $ids_of_date_type_string = implode(',', $array);
        
        // get values with given property ids
        $values = $this->getAssoc("SELECT * FROM mark_values WHERE mark_form_property_id IN ($ids_of_date_type_string)");
        
        // correct values
        foreach ($values as $key => $value) {
            if ( ! preg_match("/^\d{4}-\d{2}-\d{2}$/", $value['value'])) {
                preg_match("/^(\d{1,2})[.,](\d{1,2})[.,](\d{2,4})$/", trim($value['value']), $matches);
                if (4 === count($matches)) {
                    $matches[3] = 4 === strlen($matches[3]) ? $matches[3] : '20'.$matches[3];
                    $matches[2] = 2 === strlen($matches[2]) ? $matches[2] : '0'.$matches[2];
                    $matches[1] = 2 === strlen($matches[1]) ? $matches[1] : '0'.$matches[1];
                    $value['value'] = $matches[3].'-'.$matches[2].'-'.$matches[1];
                    $query = "UPDATE mark_values SET `value` = '{$value['value']}' WHERE id = {$value['id']}";
                } else {
                    $query = "DELETE FROM mark_values WHERE id = {$value['id']}";
                }
                $this->db->exec($query);
            }
        }
    }
    
    /**
     * Add a note field to mark form properties
     */
    private function updateTo1dot1dot0(){
        // add field to table
        $query1 = "ALTER TABLE `mark_form_properties` ADD COLUMN `note` TEXT NULL DEFAULT NULL AFTER `field_type`";
        $this->db->exec($query1);
    }
    
    private function getAssoc(string $query): array
    {
        return $this->db->query($query)->fetchAll();
    }
}