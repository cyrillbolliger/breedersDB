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
	
	    if (1 === version_compare('1.2.0', $currentVersion)) {
		    $this->updateTo1dot2dot0();
	    }
	    
	    if (1 === version_compare('1.4.0', $currentVersion)) {
            $this->updateTo1dot4dot0();
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
	
	/**
	 * Add mark_property_id to marks view
	 */
	private function updateTo1dot2dot0(){
    	// replace marks view
		$query1 = "DROP TABLE IF EXISTS `marks_view`; CREATE OR REPLACE VIEW `marks_view` AS SELECT	mark_values.id, marks.`date`, marks.author, marks.tree_id, marks.variety_id, marks.batch_id, mark_values.`value`, mark_values.exceptional_mark, mark_form_properties.`name`, mark_form_properties.id AS property_id, mark_form_properties.field_type, mark_form_property_types.`name` AS property_type FROM marks INNER JOIN mark_values ON marks.id = mark_values.mark_id INNER JOIN mark_form_properties ON mark_values.mark_form_property_id = mark_form_properties.id INNER JOIN mark_form_property_types ON mark_form_properties.mark_form_property_type_id = mark_form_property_types.id;";
		$this->db->exec($query1);
		
		// rename queries.query to queries.query_data
		$query2 = "ALTER TABLE `queries` CHANGE COLUMN `query` `my_query` TEXT NULL DEFAULT NULL;";
		$this->db->exec($query2);
    }
    
    /**
     * Add numb_fruits to to mother_trees
     */
    private function updateTo1dot4dot0(){
        // add column
        $query2 = "ALTER TABLE `mother_trees_view` ADD COLUMN `numb_fruits` INT(11) NULL DEFAULT NULL AFTER `numb_flowers`;";
        $this->db->exec($query2);
        
        // update view
        $query1 = "DROP TABLE IF EXISTS `mother_trees_view`; CREATE OR REPLACE VIEW `mother_trees_view` AS SELECT mother_trees.id, crossings.`code` AS crossing, mother_trees.`code`, mother_trees.planed, mother_trees.date_pollen_harvested, mother_trees.date_impregnated, mother_trees.date_fruit_harvested, mother_trees.numb_portions, mother_trees.numb_flowers, mother_trees.numb_fruits, mother_trees.numb_seeds, mother_trees.target, mother_trees.note, trees_view.publicid, trees_view.convar, trees_view.`offset`, trees_view.`row`, trees_view.experiment_site, mother_trees.tree_id, mother_trees.crossing_id FROM mother_trees INNER JOIN trees_view ON mother_trees.tree_id = trees_view.id INNER JOIN crossings ON mother_trees.crossing_id = crossings.id WHERE mother_trees.deleted IS NULL";
        $this->db->exec($query1);
    }
    
    private function getAssoc(string $query): array
    {
        return $this->db->query($query)->fetchAll();
    }
}
