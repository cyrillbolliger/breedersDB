<?php

namespace App\Model\Table;

use App\Domain\ImageEditor\ImageEditor;
use App\Domain\ImageEditor\ImageEditorException;
use App\Domain\Upload\ChunkUploadStrategy;
use App\Domain\Upload\UploadStrategy;
use Cake\Core\Configure;
use Cake\Log\Log;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Validation\Validation;
use Cake\Event\Event;
use ArrayObject;

/**
 * MarkValues Model
 *
 * @property \Cake\ORM\Association\BelongsTo $MarkFormProperties
 * @property \Cake\ORM\Association\BelongsTo $Marks
 *
 * @method \App\Model\Entity\MarkValue get( $primaryKey, $options = [] )
 * @method \App\Model\Entity\MarkValue newEntity( $data = null, array $options = [] )
 * @method \App\Model\Entity\MarkValue[] newEntities( array $data, array $options = [] )
 * @method \App\Model\Entity\MarkValue|bool save( \Cake\Datasource\EntityInterface $entity, $options = [] )
 * @method \App\Model\Entity\MarkValue patchEntity( \Cake\Datasource\EntityInterface $entity, array $data, array $options = [] )
 * @method \App\Model\Entity\MarkValue[] patchEntities( $entities, array $data, array $options = [] )
 * @method \App\Model\Entity\MarkValue findOrCreate( $search, callable $callback = null, $options = [] )
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MarkValuesTable extends Table {

    public const ALLOWED_PHOTO_EXT = ['jpg', 'jpeg', 'png'];

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 *
	 * @return void
	 */
	public function initialize( array $config ): void {
		parent::initialize( $config );

		$this->setTable( 'mark_values' );
		$this->setDisplayField( 'id' );
		$this->setPrimaryKey( 'id' );

		$this->addBehavior( 'Timestamp' );

		$this->belongsTo( 'MarkFormProperties', [
			'foreignKey' => 'mark_form_property_id',
			'joinType'   => 'INNER'
		] );
		$this->belongsTo( 'Marks', [
			'foreignKey' => 'mark_id',
			'joinType'   => 'INNER'
		] );
	}

	/**
	 * Modify data before saving
	 *
	 * @param Event $event
	 * @param ArrayObject $data
	 * @param ArrayObject $options
	 */
	public function beforeMarshal( Event $event, ArrayObject &$data, ArrayObject $options ) {
		$type = $this->MarkFormProperties->get( $data['mark_form_property_id'] )->field_type;

        // convert date data into the yyyy-mm-dd format
		if ( 'DATE' === $type && preg_match('/^\d{1,2}\.\d{1,2}\.\d{4}$/', $data['value']) ) {
			$tmp           = preg_split( '/[.-\/]/', $data['value'] );
			$data['value'] = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0];
		}

        // store uploaded photo
        if ( 'PHOTO' === $type ) {
            $tmpFileName = $data['value'];
            $finalFileName = $this->storeFinalImage($tmpFileName);
            $data['value'] = $finalFileName;
        }
	}

    private function storeFinalImage(string $tmpFileName): string|false
    {
        $uploadHandler = new ChunkUploadStrategy(self::ALLOWED_PHOTO_EXT, $tmpFileName);
        $tmpFilePath = $uploadHandler->getTmpPath();

        if (! ImageEditor::isImage($tmpFilePath)) {
            return false;
        }

        $finalFileName = $uploadHandler->storeFinal(Configure::read('App.paths.photos'));
        $finalFilePath = realpath(
            Configure::read('App.paths.photos')
            . DS . UploadStrategy::getSubdir($finalFileName)
            . DS . $finalFileName
        );

        try {
            $editor = new ImageEditor($finalFilePath);
            $editor->normalizeRotation();
        } catch (ImageEditorException $e) {
            Log::error($e->getMessage());
            return false;
        }

        return $finalFileName;
    }

	/**
	 * Default validation rules.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 *
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault( Validator $validator ): \Cake\Validation\Validator {
		$validator
			->integer( 'id' )
			->allowEmptyString( 'id', __('This field is required'), 'create' )
			->add( 'id', 'unique', [ 'rule' => 'validateUnique', 'provider' => 'table' ] );

		$validator
			->requirePresence( 'value', 'create' )
			->notEmptyString( 'value' )
			->add( 'value', 'custom', [
				'rule'    => function ( $value, $context ) {
					return $this->_validateValue( $value, $context );
				},
				'message' => __( 'The validation rules of this data type has been violated. Please check data type again.' ),
			] );

		$validator
			->boolean( 'exceptional_mark' )
			->requirePresence( 'exceptional_mark', 'create' )
			->notEmptyString( 'exceptional_mark' );

		return $validator;
	}

	/**
	 * Validation rules for the value column
	 *
	 * @param $value
	 * @param $context
	 *
	 * @return bool
	 */
	protected function _validateValue( $value, $context ) {
		$mark_form_property = $this->MarkFormProperties->get( $context['data']['mark_form_property_id'] );
		if ( $mark_form_property ) {
			switch ( $mark_form_property->field_type ) {
				case 'INTEGER':
					return Validation::isInteger( $value )
					       && (int) $value >= (int) $mark_form_property->validation_rule['min']
					       && (int) $value <= (int) $mark_form_property->validation_rule['max'];

				case 'FLOAT':
					return Validation::decimal( $value )
					       && (float) $value >= (float) $mark_form_property->validation_rule['min']
					       && (float) $value <= (float) $mark_form_property->validation_rule['max'];

				case 'VARCHAR':
					return Validation::notBlank( $value )
					       && Validation::maxLength( $value, 255 );

				case 'BOOLEAN':
					return Validation::boolean( $value );

				case 'DATE':
					return Validation::date( $value, 'ymd' );

                case 'PHOTO':
                    $baseDir = Configure::read('App.paths.photos');
                    $subDir = UploadStrategy::getSubdir($value);
                    return file_exists($baseDir.DS.$subDir.DS.$value);

				default:
					return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Returns a rules checker object that will be used for validating
	 * application integrity.
	 *
	 * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
	 *
	 * @return \Cake\ORM\RulesChecker
	 */
	public function buildRules( RulesChecker $rules ): \Cake\ORM\RulesChecker {
		$rules->add( $rules->isUnique( [ 'id' ] ) );
		$rules->add( $rules->existsIn( [ 'mark_form_property_id' ], 'MarkFormProperties' ) );
		$rules->add( $rules->existsIn( [ 'mark_id' ], 'Marks' ) );

		return $rules;
	}
}
