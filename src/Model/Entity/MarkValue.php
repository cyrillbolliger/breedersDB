<?php

namespace App\Model\Entity;

use Cake\I18n\FrozenTime;
use Cake\ORM\Entity;

/**
 * MarkValue Entity
 *
 * @property int $id
 * @property string $value
 * @property bool $exceptional_mark
 * @property int $mark_form_property_id
 * @property int $mark_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\MarkFormProperty $mark_form_property
 * @property \App\Model\Entity\Mark $mark
 */
class MarkValue extends Entity {

	/**
	 * Fields that can be mass assigned using newEntity() or patchEntity().
	 *
	 * Note that when '*' is set to true, this allows all unspecified fields to
	 * be mass assigned. For security purposes, it is advised to set '*' to false
	 * (or remove it), and explicitly make individual fields accessible as needed.
	 *
	 * @var array
	 */
	protected $_accessible = [
		'*'  => true,
		'id' => false
	];

	/**
	 * Return nicely formatted date if its a date. Else just return the value as it is.
	 * The conversion to the ymd format at saving is done in the beforeMarshalling method of the table.
	 *
	 * @return string|void
	 */
	protected function _getValue() {
		if ( $this->isNew() ) {
			return isset($this->_fields['value']) ? $this->_fields['value'] : null;
		}

		$MarkFormProperties = \Cake\Datasource\FactoryLocator::get('Table')->get( 'MarkFormProperties' );

		$type = $MarkFormProperties->get( $this->_fields['mark_form_property_id'] )->field_type;

		if ( 'DATE' === $type ) {
			$date = FrozenTime::parse( $this->_fields['value'] );

			return $date->i18nFormat();
		}

		return $this->_fields['value'];
	}
}
