<?php
use Migrations\AbstractSeed;

/**
 * Queries seed.
 */
class QueriesSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeds is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'id' => '1',
                'code' => 'Beste Früchte',
                'my_query' => '{"root_view":"MarksView","breeding_obj_aggregation_mode":"convar","fields":{"BatchesView":{"id":"0","crossing_batch":"0","date_sowed":"0","numb_seeds_sowed":"0","numb_sprouts_grown":"0","seed_tray":"0","date_planted":"0","numb_sprouts_planted":"0","patch":"0","note":"0","crossing_id":"0"},"CrossingsView":{"id":"0","code":"0","mother_variety":"0","father_variety":"0","target":"0"},"MarksView":{"id":"0","date":"0","author":"0","tree_id":"0","variety_id":"0","batch_id":"0","value":"0","exceptional_mark":"0","name":"0","property_id":"0","field_type":"0","property_type":"0"},"MotherTreesView":{"id":"0","crossing":"0","code":"0","planed":"0","date_pollen_harvested":"0","date_impregnated":"0","date_fruit_harvested":"0","numb_portions":"0","numb_flowers":"0","numb_fruits":"0","numb_seeds":"0","note":"0","publicid":"0","convar":"0","offset":"0","row":"0","experiment_site":"0","tree_id":"0","crossing_id":"0"},"ScionsBundlesView":{"id":"0","identification":"0","convar":"0","numb_scions":"0","date_scions_harvest":"0","descents_publicid_list":"0","note":"0","external_use":"0","variety_id":"0"},"TreesView":{"id":"0","publicid":"0","convar":"0","date_grafted":"0","date_planted":"0","date_eliminated":"0","date_labeled":"0","genuine_seedling":"0","offset":"0","row":"0","dont_eliminate":"0","note":"0","variety_id":"0","grafting":"0","rootstock":"0","experiment_site":"0"},"VarietiesView":{"id":"0","convar":"1","official_name":"0","acronym":"0","plant_breeder":"0","registration":"0","description":"0","batch_id":"0"},"MarkProperties":{"22":{"check":"0","mode":"count","operator":"","value":""},"13":{"check":"0","mode":"count","operator":"","value":""},"8":{"check":"0","mode":"count","operator":"","value":""},"19":{"check":"1","mode":"median","operator":"greater_or_equal","value":"6"},"7":{"check":"0","mode":"all","operator":"","value":"0"},"14":{"check":"1","mode":"avg","operator":"greater_or_equal","value":"5"},"6":{"check":"0","mode":"count","operator":"","value":""},"21":{"check":"0","mode":"all","operator":"","value":""},"17":{"check":"1","mode":"median","operator":"greater_or_equal","value":"6"},"20":{"check":"1","mode":"count","operator":"","value":""},"12":{"check":"0","mode":"all","operator":"","value":""},"10":{"check":"0","mode":"count","operator":"","value":""},"16":{"check":"1","mode":"all","operator":"","value":"0"},"5":{"check":"0","mode":"all","operator":"","value":"0"},"4":{"check":"0","mode":"count","operator":"","value":""},"3":{"check":"0","mode":"count","operator":"","value":""},"9":{"check":"0","mode":"count","operator":"","value":""},"15":{"check":"1","mode":"count","operator":"","value":""},"11":{"check":"0","mode":"count","operator":"","value":""},"18":{"check":"1","mode":"median","operator":"greater_or_equal","value":"5"},"1":{"check":"0","mode":"count","operator":"","value":""},"2":{"check":"0","mode":"count","operator":"","value":""}}},"where":"{\\"condition\\":\\"AND\\",\\"rules\\":[],\\"valid\\":true}"}',
                'description' => 'Aromatik Median >= 6
Aussehen Durchnischitt >= 5
Biss Median >= 6
Saftigkeit >= 5

Ohne Kriterien: Regenflecken, Fleischverbräunung',
                'query_group_id' => '1',
                'deleted' => NULL,
                'created' => '2020-11-29 22:47:50',
                'modified' => '2020-11-29 22:48:38',
            ],
            [
                'id' => '2',
                'code' => 'Kein Schorf - Wenig Mehltau',
                'my_query' => '{"root_view":"MarksView","breeding_obj_aggregation_mode":"convar","fields":{"BatchesView":{"id":"0","crossing_batch":"0","date_sowed":"0","numb_seeds_sowed":"0","numb_sprouts_grown":"0","seed_tray":"0","date_planted":"0","numb_sprouts_planted":"0","patch":"0","note":"0","crossing_id":"0"},"CrossingsView":{"id":"0","code":"0","mother_variety":"0","father_variety":"0","target":"0"},"MarksView":{"id":"0","date":"0","author":"0","tree_id":"0","variety_id":"0","batch_id":"0","value":"0","exceptional_mark":"0","name":"0","property_id":"0","field_type":"0","property_type":"0"},"MotherTreesView":{"id":"0","crossing":"0","code":"0","planed":"0","date_pollen_harvested":"0","date_impregnated":"0","date_fruit_harvested":"0","numb_portions":"0","numb_flowers":"0","numb_fruits":"0","numb_seeds":"0","note":"0","publicid":"0","convar":"0","offset":"0","row":"0","experiment_site":"0","tree_id":"0","crossing_id":"0"},"ScionsBundlesView":{"id":"0","identification":"0","convar":"0","numb_scions":"0","date_scions_harvest":"0","descents_publicid_list":"0","note":"0","external_use":"0","variety_id":"0"},"TreesView":{"id":"0","publicid":"0","convar":"0","date_grafted":"0","date_planted":"0","date_eliminated":"0","date_labeled":"0","genuine_seedling":"0","offset":"0","row":"0","dont_eliminate":"0","note":"0","variety_id":"0","grafting":"0","rootstock":"0","experiment_site":"0"},"VarietiesView":{"id":"0","convar":"1","official_name":"0","acronym":"0","plant_breeder":"0","registration":"0","description":"0","batch_id":"0"},"MarkProperties":{"22":{"check":"0","mode":"count","operator":"","value":""},"13":{"check":"0","mode":"count","operator":"","value":""},"8":{"check":"0","mode":"count","operator":"","value":""},"19":{"check":"0","mode":"count","operator":"","value":""},"7":{"check":"0","mode":"all","operator":"","value":"0"},"14":{"check":"0","mode":"count","operator":"","value":""},"6":{"check":"0","mode":"count","operator":"","value":""},"21":{"check":"0","mode":"all","operator":"","value":""},"17":{"check":"0","mode":"count","operator":"","value":""},"20":{"check":"0","mode":"count","operator":"","value":""},"12":{"check":"0","mode":"all","operator":"","value":""},"10":{"check":"0","mode":"count","operator":"","value":""},"16":{"check":"0","mode":"all","operator":"","value":"0"},"5":{"check":"0","mode":"all","operator":"","value":"0"},"4":{"check":"0","mode":"count","operator":"","value":""},"3":{"check":"1","mode":"median","operator":"less","value":"4"},"9":{"check":"0","mode":"count","operator":"","value":""},"15":{"check":"0","mode":"count","operator":"","value":""},"11":{"check":"0","mode":"count","operator":"","value":""},"18":{"check":"0","mode":"count","operator":"","value":""},"1":{"check":"1","mode":"max","operator":"less_or_equal","value":"2"},"2":{"check":"0","mode":"max","operator":"less_or_equal","value":""}}},"where":"{\\"condition\\":\\"AND\\",\\"rules\\":[],\\"valid\\":true}"}',
                'description' => 'Blattschorf grösster Wert <= 2
Mehltau Media < 4',
                'query_group_id' => '1',
                'deleted' => NULL,
                'created' => '2020-11-29 22:52:27',
                'modified' => '2020-11-29 22:56:29',
            ],
            [
                'id' => '3',
                'code' => 'Gepflanzt 1 Quartal 2020',
                'my_query' => '{"root_view":"TreesView","fields":{"BatchesView":{"id":"0","crossing_batch":"0","date_sowed":"0","numb_seeds_sowed":"0","numb_sprouts_grown":"0","seed_tray":"0","date_planted":"0","numb_sprouts_planted":"0","patch":"0","note":"0","crossing_id":"0"},"CrossingsView":{"id":"0","code":"0","mother_variety":"0","father_variety":"0","target":"0"},"MarksView":{"id":"0","date":"0","author":"0","tree_id":"0","variety_id":"0","batch_id":"0","value":"0","exceptional_mark":"0","name":"0","property_id":"0","field_type":"0","property_type":"0"},"MotherTreesView":{"id":"0","crossing":"0","code":"0","planed":"0","date_pollen_harvested":"0","date_impregnated":"0","date_fruit_harvested":"0","numb_portions":"0","numb_flowers":"0","numb_fruits":"0","numb_seeds":"0","note":"0","publicid":"0","convar":"0","offset":"0","row":"0","experiment_site":"0","tree_id":"0","crossing_id":"0"},"ScionsBundlesView":{"id":"0","identification":"0","convar":"0","numb_scions":"0","date_scions_harvest":"0","descents_publicid_list":"0","note":"0","external_use":"0","variety_id":"0"},"TreesView":{"id":"0","publicid":"1","convar":"0","date_grafted":"0","date_planted":"1","date_eliminated":"0","date_labeled":"0","genuine_seedling":"0","offset":"0","row":"1","dont_eliminate":"0","note":"0","variety_id":"0","grafting":"0","rootstock":"0","experiment_site":"1"},"VarietiesView":{"id":"0","convar":"1","official_name":"0","acronym":"0","plant_breeder":"0","registration":"0","description":"0","batch_id":"0"},"MarkProperties":{"22":{"check":"0","mode":"count","operator":"","value":""},"13":{"check":"0","mode":"count","operator":"","value":""},"8":{"check":"0","mode":"count","operator":"","value":""},"19":{"check":"0","mode":"count","operator":"","value":""},"7":{"check":"0","mode":"all","operator":"","value":"0"},"14":{"check":"0","mode":"count","operator":"","value":""},"6":{"check":"0","mode":"count","operator":"","value":""},"21":{"check":"0","mode":"all","operator":"","value":""},"17":{"check":"0","mode":"count","operator":"","value":""},"20":{"check":"0","mode":"count","operator":"","value":""},"12":{"check":"0","mode":"all","operator":"","value":""},"10":{"check":"0","mode":"count","operator":"","value":""},"16":{"check":"0","mode":"all","operator":"","value":"0"},"5":{"check":"0","mode":"all","operator":"","value":"0"},"4":{"check":"0","mode":"count","operator":"","value":""},"3":{"check":"0","mode":"count","operator":"","value":""},"9":{"check":"0","mode":"count","operator":"","value":""},"15":{"check":"0","mode":"count","operator":"","value":""},"11":{"check":"0","mode":"count","operator":"","value":""},"18":{"check":"0","mode":"count","operator":"","value":""},"1":{"check":"0","mode":"count","operator":"","value":""},"2":{"check":"0","mode":"count","operator":"","value":""}}},"where":"{\\"condition\\":\\"AND\\",\\"rules\\":[{\\"id\\":\\"TreesView.date_planted\\",\\"field\\":\\"TreesView.date_planted\\",\\"type\\":\\"date\\",\\"input\\":\\"text\\",\\"operator\\":\\"greater_or_equal\\",\\"value\\":\\"01.01.2020\\"},{\\"id\\":\\"TreesView.date_planted\\",\\"field\\":\\"TreesView.date_planted\\",\\"type\\":\\"date\\",\\"input\\":\\"text\\",\\"operator\\":\\"less_or_equal\\",\\"value\\":\\"31.03.2020\\"}],\\"valid\\":true}"}',
                'description' => '',
                'query_group_id' => '2',
                'deleted' => NULL,
                'created' => '2020-11-29 22:58:13',
                'modified' => '2020-11-29 23:01:08',
            ],
            [
                'id' => '4',
                'code' => 'Standort Birmensdorf',
                'my_query' => '{"root_view":"TreesView","fields":{"BatchesView":{"id":"0","crossing_batch":"0","date_sowed":"0","numb_seeds_sowed":"0","numb_sprouts_grown":"0","seed_tray":"0","date_planted":"0","numb_sprouts_planted":"0","patch":"0","note":"0","crossing_id":"0"},"CrossingsView":{"id":"0","code":"0","mother_variety":"0","father_variety":"0","target":"0"},"MarksView":{"id":"0","date":"0","author":"0","tree_id":"0","variety_id":"0","batch_id":"0","value":"0","exceptional_mark":"0","name":"0","property_id":"0","field_type":"0","property_type":"0"},"MotherTreesView":{"id":"0","crossing":"0","code":"0","planed":"0","date_pollen_harvested":"0","date_impregnated":"0","date_fruit_harvested":"0","numb_portions":"0","numb_flowers":"0","numb_fruits":"0","numb_seeds":"0","note":"0","publicid":"0","convar":"0","offset":"0","row":"0","experiment_site":"0","tree_id":"0","crossing_id":"0"},"ScionsBundlesView":{"id":"0","identification":"0","convar":"0","numb_scions":"0","date_scions_harvest":"0","descents_publicid_list":"0","note":"0","external_use":"0","variety_id":"0"},"TreesView":{"id":"0","publicid":"1","convar":"1","date_grafted":"0","date_planted":"0","date_eliminated":"0","date_labeled":"0","genuine_seedling":"0","offset":"0","row":"1","dont_eliminate":"0","note":"0","variety_id":"0","grafting":"0","rootstock":"0","experiment_site":"1"},"VarietiesView":{"id":"0","convar":"0","official_name":"0","acronym":"0","plant_breeder":"0","registration":"0","description":"0","batch_id":"0"},"MarkProperties":{"22":{"check":"0","mode":"count","operator":"","value":""},"13":{"check":"0","mode":"count","operator":"","value":""},"8":{"check":"0","mode":"count","operator":"","value":""},"19":{"check":"0","mode":"count","operator":"","value":""},"7":{"check":"0","mode":"all","operator":"","value":"0"},"14":{"check":"0","mode":"count","operator":"","value":""},"6":{"check":"0","mode":"count","operator":"","value":""},"21":{"check":"0","mode":"all","operator":"","value":""},"17":{"check":"0","mode":"count","operator":"","value":""},"20":{"check":"0","mode":"count","operator":"","value":""},"12":{"check":"0","mode":"all","operator":"","value":""},"10":{"check":"0","mode":"count","operator":"","value":""},"16":{"check":"0","mode":"all","operator":"","value":"0"},"5":{"check":"0","mode":"all","operator":"","value":"0"},"4":{"check":"0","mode":"count","operator":"","value":""},"3":{"check":"0","mode":"count","operator":"","value":""},"9":{"check":"0","mode":"count","operator":"","value":""},"15":{"check":"0","mode":"count","operator":"","value":""},"11":{"check":"0","mode":"count","operator":"","value":""},"18":{"check":"0","mode":"count","operator":"","value":""},"1":{"check":"0","mode":"count","operator":"","value":""},"2":{"check":"0","mode":"count","operator":"","value":""}}},"where":"{\\"condition\\":\\"AND\\",\\"rules\\":[{\\"id\\":\\"TreesView.experiment_site\\",\\"field\\":\\"TreesView.experiment_site\\",\\"type\\":\\"string\\",\\"input\\":\\"select\\",\\"operator\\":\\"equal\\",\\"value\\":\\"New Ebonymouth\\"}],\\"valid\\":true}"}',
                'description' => '',
                'query_group_id' => '2',
                'deleted' => NULL,
                'created' => '2020-11-29 23:04:13',
                'modified' => '2020-11-29 23:05:39',
            ],
        ];

        $table = $this->table('queries');
        $table->insert($data)->save();
    }
}
