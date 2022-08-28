<?php
/**
 * Created by PhpStorm.
 * User: cyrillbolliger
 * Date: 14.09.17
 * Time: 20:42
 */

namespace App\Model\Behavior;

use Cake\Core\Exception\Exception;
use Cake\ORM\Behavior;

class TranslatableFieldsBehavior extends Behavior {
    /**
     * Translate field names
     *
     * @param string $key
     *
     * @return string
     * @throws Exception if given key doesn't exist in translations array
     */
    public function translateFields( string $key ) {
        $translations = [
            'BatchesView.id'                           => __( 'Batch -> Id' ),
            'BatchesView.crossing_batch'               => __( 'Batch -> Crossing.Batch' ),
            'BatchesView.date_sowed'                   => __( 'Batch -> Date Sowed' ),
            'BatchesView.numb_seeds_sowed'             => __( 'Batch -> Numb Seeds Sowed' ),
            'BatchesView.numb_sprouts_grown'           => __( 'Batch -> Numb Sprouts Grown' ),
            'BatchesView.seed_tray'                    => __( 'Batch -> Seed Tray' ),
            'BatchesView.date_planted'                 => __( 'Batch -> Date Planted' ),
            'BatchesView.numb_sprouts_planted'         => __( 'Batch -> Numb Sprouts Planted' ),
            'BatchesView.patch'                        => __( 'Batch -> Patch' ),
            'BatchesView.note'                         => __( 'Batch -> Note' ),
            'BatchesView.crossing_id'                  => __( 'Batch -> Crossing Id' ),
            'CrossingsView.id'                         => __( 'Crossing -> Id' ),
            'CrossingsView.code'                       => __( 'Crossing -> Code' ),
            'CrossingsView.mother_variety'             => __( 'Crossing -> Mother Variety' ),
            'CrossingsView.father_variety'             => __( 'Crossing -> Father Variety' ),
            'CrossingsView.target'                     => __( 'Crossing -> Target' ),
            'MarksView.id'                             => __( 'Mark -> Id' ),
            'MarksView.date'                           => __( 'Mark -> Date' ),
            'MarksView.author'                         => __( 'Mark -> Author' ),
            'MarksView.tree_id'                        => __( 'Mark -> Tree Id' ),
            'MarksView.variety_id'                     => __( 'Mark -> Variety Id' ),
            'MarksView.batch_id'                       => __( 'Mark -> Batch Id' ),
            'MarksView.value'                          => __( 'Mark -> Value' ),
            'MarksView.exceptional_mark'               => __( 'Mark -> Exceptional Mark' ),
            'MarksView.name'                           => __( 'Mark -> Property' ),
            'MarksView.property_id'                    => __( 'Mark -> Property Id' ),
            'MarksView.field_type'                     => __( 'Mark -> Data Type' ),
            'MarksView.property_type'                  => __( 'Mark -> Property Type' ),
            'MotherTreesView.id'                       => __( 'Mother Tree -> Id' ),
            'MotherTreesView.crossing'                 => __( 'Mother Tree -> Crossing' ),
            'MotherTreesView.code'                     => __( 'Mother Tree -> Identification' ),
            'MotherTreesView.planed'                   => __( 'Mother Tree -> Planed' ),
            'MotherTreesView.date_pollen_harvested'    => __( 'Mother Tree -> Date Pollen Harvested' ),
            'MotherTreesView.date_impregnated'         => __( 'Mother Tree -> Date Impregnated' ),
            'MotherTreesView.date_fruit_harvested'     => __( 'Mother Tree -> Date Fruit Harvested' ),
            'MotherTreesView.numb_portions'            => __( 'Mother Tree -> Numb Portions' ),
            'MotherTreesView.numb_flowers'             => __( 'Mother Tree -> Numb Flowers' ),
            'MotherTreesView.numb_fruits'              => __( 'Mother Tree -> Numb Fruits' ),
            'MotherTreesView.numb_seeds'               => __( 'Mother Tree -> Numb Seeds' ),
            'MotherTreesView.note'                     => __( 'Mother Tree -> Note' ),
            'MotherTreesView.convar'                   => __( 'Mother Tree -> Convar' ),
            'MotherTreesView.publicid'                 => __( 'Mother Tree -> Publicid' ),
            'MotherTreesView.offset'                   => __( 'Mother Tree -> Offset' ),
            'MotherTreesView.row'                      => __( 'Mother Tree -> Row' ),
            'MotherTreesView.experiment_site'          => __( 'Mother Tree -> Experiment Site' ),
            'MotherTreesView.tree_id'                  => __( 'Mother Tree -> Tree Id' ),
            'MotherTreesView.crossing_id'              => __( 'Mother Tree -> Crossing Id' ),
            'ScionsBundlesView.id'                     => __( 'Scions Bundle -> Id' ),
            'ScionsBundlesView.identification'         => __( 'Scions Bundle -> Identification' ),
            'ScionsBundlesView.convar'                 => __( 'Scions Bundle -> Convar' ),
            'ScionsBundlesView.numb_scions'            => __( 'Scions Bundle -> Numb Scions' ),
            'ScionsBundlesView.date_scions_harvest'    => __( 'Scions Bundle -> Date Scions Harvest' ),
            'ScionsBundlesView.descents_publicid_list' => __( 'Scions Bundle -> Descents (Publicids)' ),
            'ScionsBundlesView.note'                   => __( 'Scions Bundle -> Note' ),
            'ScionsBundlesView.external_use'           => __( 'Scions Bundle -> Reserved for external partners' ),
            'ScionsBundlesView.variety_id'             => __( 'Scions Bundle -> Varienty Id' ),
            'TreesView.id'               => __( 'Tree -> Id' ),
            'TreesView.publicid'         => __( 'Tree -> Publicid' ),
            'TreesView.convar'           => __( 'Tree -> Convar' ),
            'TreesView.date_grafted'     => __( 'Tree -> Date Grafted' ),
            'TreesView.date_planted'     => __( 'Tree -> Date Planted' ),
            'TreesView.date_eliminated'  => __( 'Tree -> Date Eliminated' ),
            'TreesView.date_labeled'     => __( 'Tree -> Date Labeled' ),
            'TreesView.genuine_seedling' => __( 'Tree -> Genuine Seedling' ),
            'TreesView.offset'           => __( 'Tree -> Offset' ),
            'TreesView.dont_eliminate'   => __( "Tree -> Don't eliminate" ),
            'TreesView.row'              => __( 'Tree -> Row' ),
            'TreesView.note'             => __( 'Tree -> Note' ),
            'TreesView.variety_id'       => __( 'Tree -> Variety Id' ),
            'TreesView.grafting'                       => __( 'Tree -> Grafting' ),
            'TreesView.rootstock'                      => __( 'Tree -> Rootstock' ),
            'TreesView.experiment_site'                => __( 'Tree -> Experiment Site' ),
            'VarietiesView.id'                         => __( 'Varieties -> Id' ),
            'VarietiesView.convar'                     => __( 'Varieties -> Convar' ),
            'VarietiesView.official_name'              => __( 'Varieties -> Official Name' ),
            'VarietiesView.acronym'                    => __( 'Varieties -> Acronym' ),
            'VarietiesView.plant_breeder'              => __( 'Varieties -> Plant Breeder' ),
            'VarietiesView.registration'               => __( 'Varieties -> Registration' ),
            'VarietiesView.description'                => __( 'Varieties -> Description' ),
            'VarietiesView.batch_id'                   => __( 'Varieties -> Batch Id' ),
        ];

        if ( ! array_key_exists($key, $translations ) ) {
            throw new \Exception( "Translation of field {$key} not found." );
        }

        return $translations[ $key ];
    }
}
