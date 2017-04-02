<pre>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Created by PhpStorm.
 * User: cyrillbolliger
 * Date: 02.04.17
 * Time: 11:46
 */

$batch_doubles = [
    173 => [167,168,171,172],
    2   => 3,
    96  => 123,
    132 => 134,
    82  => 166,
    9   => 5,
    130 => 53,
    68  => 161,
    184 => 175,
    86  => 69,
    175 => 184,
    69  => 86,
    176 => 181,
    70  => 74,
    138 => 139,
];

$empty_marks = [96,97,98,99,100,101,102,103,104,105,106,107,109,112,179,263,323,324,325,326,327,328,338,396,403,407,457,482,520,584,601,604,655,658,659,661,662,664,666,674,685,688,716,745,749,750,769,775,787,825,865,866,869,870,875,932,940,952,978,1053,1070,1078,1282,1288,1328,1464,1465,1470,1484,1486,1531,1532,1533,1580,1660,1723,1772,1785,1787,1800,1812,1813,1818,1819,1820,1848,1868,1892,2101,2288,2290,2325,2337,2348,2349,2350,2352,2353,2356,2367,2506,2507,2510,2516,2517,2525,2526,2529,2540,2542,2543,2545,2553,2555,2556,2558,2560,2563,2568,2571,2573,2584,2585,2599,2600,2619,2624,2671,2687,2694,2695,2696,2700,2718,2727,2756,2767,2805,2816,2837,2857,2866,2868,2872,2880,2881,2905,2915,2919,2932,2936,2937,2938,2939,2940,2941,2942,2943,2945,2949,2951,2957,2960,2964,2969,2971,2972,2973,2974,2979,2980,2997,3034,3038,3055,3061,3064,3090,3151,3157,3162,3165,3179,3181,3185,3186,3223,3227,3230,3241,3251,3260,3283,3297,3301,3302,3312,3315];

$servername = "localhost";
$username = "root";
$password = "root";

try {
    $old = new PDO("mysql:host=$servername;dbname=movedb", $username, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    $new = new PDO("mysql:host=$servername;dbname=poc_move", $username, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
} catch(PDOException $e) {
    die('Could not connect to the database:<br/>' . $e);
}


//move('LoseKreuzungen','crossings');
//move('Kreuzungen','mother_trees');
//move('Baeume','trees');
//move('Lose','batches');
//move('Klon', 'varieties');
//move('Reihen', 'rows');
//move('Bonituren', 'marks');
//move('Forms','mark_forms');
//move('FormsEigenschaften','mark_form_fields');
//move('Eigenschaften','mark_form_properties');
//move('EigenschaftenBonituren','mark_values');

function mark_values($oldtbl, $newtbl, $old, $new) {
    truncate($new, $newtbl, true);
    
    for($i=0;$i<100;$i++){
        $query = "SELECT * FROM $oldtbl ORDER BY EigenschaftBoniturID LIMIT ".($i*1000).', 1000';
        $rows = getAssoc($old, $query);
        
        if (empty($rows)) {
            break;
        }
        
        $column_names = [
            'id' => 'EigenschaftBoniturID',
            'value' => 'Wert',
            'mark_form_property_id' => 'EigenschaftID',
            'mark_id' => 'BoniturID',
        ];
        
        $data = translate($column_names, $rows);
        
        foreach ($data as $key => $d) {
            if (empty($d['value'])) {
                unset($data[$key]);
            }
        }
        
        insert($new,$newtbl,$data,true);
    }
    
    // clean out values without existing marks
    $ids = array();
    for($i=0;$i<100;$i++) {
        $query = "SELECT $newtbl.id FROM $newtbl INNER JOIN marks ON marks.id = $newtbl.mark_id ORDER BY $newtbl.id LIMIT ".($i*1000).', 1000';
        $existing = getAssoc($new, $query);
        
        if (empty($existing)) {
            break;
        }
        
        foreach ($existing as $entry) {
            $ids[] = $entry['id'];
        }
    }
    $ids[] = 0;
    $existing_ids = implode(',',$ids);
    
    $query = "DELETE FROM $newtbl WHERE id NOT IN ($existing_ids)";
    $new->beginTransaction();
    $new->exec($query);
    $new->commit();
}

function mark_form_properties($oldtbl, $newtbl, $old, $new) {
    $query = "SELECT * FROM $oldtbl";
    $rows = getAssoc($old, $query);
    
    $column_names = [
        'id' => 'EigenschaftID',
        'name' => 'Bezeichnung',
        'validation_rule' => 'Gueltigkeitsregel',
        'field_type' => 'Typ'
    ];
    
    $data = translate($column_names, $rows);
    
    $validation_rules = [
        '0 or 1' => null,
        '0 OR 1' => null,
        '>= 0' => ['min'=>0,'max'=>PHP_INT_MAX,'step'=>1],
        '>= 0 AND < 1000' => ['min'=>0,'max'=>999,'step'=>1],
        '>= 1 AND < 15' => ['min'=>1,'max'=>14,'step'=>1],
        '>= 5 AND < 20' => ['min'=>5,'max'=>19,'step'=>1],
        'BETWEEN 1 AND 9' => ['min'=>1,'max'=>9,'step'=>1],
        'Between 1 and 9' => ['min'=>1,'max'=>9,'step'=>1],
        'DATE' => null,
        'TEXT' => null,
    ];
    
    foreach ($data as $key => $d) {
        if ('TEXT' == $d['field_type']) {
            $data[$key]['field_type'] = 'VARCHAR';
        }
        $data[$key]['validation_rule'] = json_encode($validation_rules[$d['validation_rule']]);
        $data[$key]['mark_form_property_type_id'] = 1;
    }
    
    truncate($new, $newtbl, true);
    insert($new,$newtbl,$data,true);
}

function mark_form_fields($oldtbl, $newtbl, $old, $new) {
    $query = "SELECT * FROM $oldtbl";
    $rows = getAssoc($old, $query);
    
    $column_names = [
        'id' => 'FormEigenschaftID',
        'priority' => 'Prioritaet',
        'mark_form_id' => 'FormID',
        'mark_form_property_id' => 'EigenschaftID',
    ];
    
    $data = translate($column_names, $rows);
    
    foreach ($data as $key => $d) {
        if (empty($d['priority'])) {
            $data[$key]['priority'] = 0;
        }
    }
    
    truncate($new, $newtbl, true);
    insert($new,$newtbl,$data,true);
}

function mark_forms($oldtbl, $newtbl, $old, $new) {
    $query = "SELECT * FROM $oldtbl";
    $rows = getAssoc($old, $query);
    
    $column_names = [
        'id' => 'FormID',
        'name' => 'Bezeichnung',
    ];
    
    $data = translate($column_names, $rows);
    
    truncate($new, $newtbl, true);
    insert($new,$newtbl,$data,true);
}

function marks($oldtbl, $newtbl, $old, $new) {
    $query = "SELECT * FROM $oldtbl";
    $rows = getAssoc($old, $query);
    
    $column_names = [
        'id' => 'BoniturID',
        'date' => 'Datum',
        'author' => 'Autor',
        'mark_form_id' => 'FormID',
        'tree_id' => 'BaumID',
        'variety_id' => 'KlonID',
        'batch_id' => 'LosID'
    ];
    
    $data = translate($column_names, $rows);
    
    global $batch_doubles;
    $doubles = transposeArray($batch_doubles);
    
    global $empty_marks;
    
    foreach ($data as $key => $d) {
        // Batch doubles
        if (array_key_exists($d['batch_id'], $doubles)) {
            $data[$key]['batch_id'] = $doubles[$d['batch_id']];
        }
        // Remove 9999 tree
        if (3250 == ($d['tree_id'])) {
            unset($data[$key]);
        }
        // Faulty mark
        if (29 == $d['id']){
            unset($data[$key]);
        }
        // Empty marks
        if (in_array($d['id'], $empty_marks)) {
            unset($data[$key]);
        }
    }
    
    truncate($new, $newtbl, true);
    insert($new,$newtbl,$data,true);
}

function rows($oldtbl, $newtbl, $old, $new) {
    $query = "SELECT * FROM $oldtbl";
    $rows = getAssoc($old, $query);
    
    $column_names = [
        'id' => 'ReiheID',
        'code' => 'Bezeichnung',
        'date_created' => 'JahrErstellung',
        'date_eliminated' => 'JahrLoeschung',
        'deleted' => 'Geloescht',
    ];
    
    $data = translate($column_names, $rows);
    
    foreach ($data as $key => $d) {
        if ($d['deleted']) {
            $data[$key]['deleted'] = date("Y-m-d H:i:s");
        } else {
            $data[$key]['deleted'] = null;
        }
    }
    
    truncate($new, $newtbl, true);
    insert($new,$newtbl,$data,true);
}

function varieties($oldtbl, $newtbl, $old, $new) {
    $query = "SELECT * FROM $oldtbl LEFT JOIN Kloninfo USING(KlonID)";
    $rows = getAssoc($old, $query);
    
    $column_names = [
        'id' => 'KlonID',
        'code' => 'Bezeichnung',
        'official_name' => 'Sortenname',
        'plant_breeder' => 'Züchter',
        'registration' => 'Zulassung',
        'description' => 'Beschrieb',
        'batch_id' => 'LosID',
    ];
    
    $data = translate($column_names, $rows);
    
    global $batch_doubles;
    $doubles = transposeArray($batch_doubles);
    
    foreach ($data as $key => $d) {
        $data[$key]['code'] = preg_replace('/[^äöüa-zA-Z0-9-_]/','_',$d['code']);
        if (array_key_exists($d['batch_id'], $doubles)) {
            $data[$key]['batch_id'] = $doubles[$d['batch_id']];
        }
    }
    
    truncate($new, $newtbl, true);
    insert($new,$newtbl,$data,true);
}

function batches($oldtbl, $newtbl, $old, $new) {
    $query = "SELECT * FROM $oldtbl";
    $rows = getAssoc($old, $query);
    
    $column_names = [
        'id' => 'LosID',
        'code' => 'KuerzelLos',
        'date_sowed' => 'AussaatDatum',
        'numb_seeds_sowed' => 'AnzahlSamenAusssaat',
        'numb_sprouts_grown' => 'AnzahlKeimlinge',
        'seed_tray' => 'Saatschale',
        'date_planted' => 'AuspflanzDatum',
        'numb_sprouts_planted' => 'AnzahlAusgepflanzt',
        'patch' => 'Beet',
        'note' => 'Bemerkungen',
        'crossing_id' => 'LosKreuzungID',
    ];
    
    $data = translate($column_names, $rows);
    
    global $batch_doubles;
    $doubles = transposeArray($batch_doubles);
    
    foreach ($data as $key => $d) {
        if (258 == $d['crossing_id']) {
            $data[$key]['crossing_id'] = 1;
        }
        if (array_key_exists($d['id'], $doubles)) {
            unset($data[$key]);
        }
    }
    
    truncate($new, $newtbl, true);
    insert($new,$newtbl,$data,true);
}

function trees($oldtbl, $newtbl, $old, $new) {
    $query = "SELECT $oldtbl.*, Standorte.Offset, Standorte.ReiheID FROM $oldtbl LEFT JOIN Standorte USING(StandortID)";
    $rows = getAssoc($old, $query);
    
    $column_names = [
        'id' => 'BaumID',
        'publicid' => 'Nummer',
        'date_grafted' => 'DatumVeredlung',
        'date_planted' => 'DatumPflanzung',
        'date_eliminated' => 'DatumEliminierung',
        'genuine_seedling' => 'Originalsaemling',
        'offset' => 'Offset',
        'note' => 'Bemerkung',
        'variety_id' => 'KlonID',
        'row_id' => 'ReiheID',
    ];
    
    $data = translate($column_names, $rows);
    $beudon = [604,605,606,607];
    
    foreach ($data as $key => $d) {
        // set experiment site
        if (in_array($d['row_id'], $beudon)) {
            $data[$key]['experiment_site_id'] = 2;
        } else {
            $data[$key]['experiment_site_id'] = 1;
        }
        // format publicid
        if ( 0 !== strpos($d['publicid'], '#') ) {
            $data[$key]['publicid'] = sprintf('%08d', $d['publicid']);
        } else {
            $data[$key]['publicid'] = '#' . sprintf('%08d', substr($d['publicid'],1));
        }
        // Remove 9999 tree
        if (3250 == ($d['id'])) {
            unset($data[$key]);
        }
    }
    
    truncate($new, $newtbl, true);
    insert($new,$newtbl,$data,true);
}

function mother_trees($oldtbl, $newtbl, $old, $new) {
    
    $query = "SELECT * FROM $oldtbl";
    $rows = getAssoc($old, $query);
    
    $column_names = [
        'id' => 'KreuzungID',
        'code' => 'KreuzungID',
        'date_pollen_harvested' => 'DatumPollenernte',
        'date_impregnated' => 'DatumBefruchtung',
        'date_fruit_harvested' => 'DatumFruchternte',
        'numb_portions' => 'Portionen',
        'numb_flowers' => 'AnzahlBlueten',
        'numb_seeds' => 'AnzahlSamen',
        'target' => 'Kreuzungsziel',
        'note' => 'Bemerkungen',
        'crossing_id' => 'LosKreuzungID',
        'tree_id' => 'MutterbaumID',
    ];
    
    $data = translate($column_names, $rows);
    
    foreach ($data as $key => $d) {
        $data[$key]['planed'] = 0;
        // Set SORTE
        if (258 == $d['crossing_id']) {
            $data[$key]['crossing_id'] = 1;
        }
        // Remove 9999 tree
        if (3250 == ($d['tree_id'])) {
            unset($data[$key]);
        }
    }
    
    truncate($new, $newtbl, true);
    insert($new,$newtbl,$data,true);
}


function crossings($oldtbl, $newtbl, $old, $new) {
    
    $query = "SELECT * FROM $oldtbl";
    $rows = getAssoc($old, $query);
    
    $column_names = [
        'id' => 'LosKreuzungID',
        'code' => 'KuerzelKreuzung',
        'mother_variety_id' => 'MutterKlonID',
        'father_variety_id' => 'VaterKlonID',
    ];
    
    $data = translate($column_names, $rows);
    
    // Set SORTE (LosKreuzungID: 258 --> id: 1; code: SORTE)
    foreach ($data as $key => $d) {
        if (258 == $d['id']) {
            $data[$key]['id'] = 1;
            $data[$key]['code'] = 'SORTE';
        }
    }
    
    truncate($new, $newtbl, true);
    insert($new,$newtbl,$data,true);
}

function move($oldtbl, $newtbl, $func = null) {
    global $old;
    global $new;
    $func = $func ? $func : $newtbl;
    
    echo "Move $oldtbl to $newtbl\n";
    echo "$oldtbl count: ".tblcount($old,$oldtbl)."\n";
    
    call_user_func($func,$oldtbl, $newtbl, $old, $new);
    
    echo "$newtbl count: ".tblcount($new,$newtbl)."\n\n";
}

function truncate($new, $newtbl, $ignore = false) {
    try {
        $new->beginTransaction();
        if ($ignore) {
            $new->exec("SET FOREIGN_KEY_CHECKS = 0");
        }
        $new->exec("TRUNCATE TABLE $newtbl");
        $new->commit();
    } catch(PDOException $ex) {
        //Something went wrong rollback!
        $new->rollBack();
        echo $ex->getMessage();
    } finally {
        if ($ignore) {
            $new->exec("SET FOREIGN_KEY_CHECKS = 1");
        }
    }
}

function insert($db, $tbl, $rows, $ignore = false) {
    $first_key = array_keys($rows)[0];
    if (! is_array($rows[$first_key])) {
        $tmp = $rows;
        unset($rows);
        $rows[0] = $tmp;
        unset($tmp);
    }
    
    $column_names = array_keys($rows[$first_key]);
    $columns = implode(',', $column_names);
    $value_placeholders_array = array_map(function($str){return ':'.$str;}, $column_names);
    $value_placeholders = implode(',',$value_placeholders_array);
    $query = "INSERT INTO $tbl ($columns) VALUES ($value_placeholders)";
    
    try {
        $db->beginTransaction();
        if ($ignore) {
            $db->exec("SET FOREIGN_KEY_CHECKS = 0");
        }
        foreach($rows as $row) {
            $statement = $db->prepare($query);
            foreach($column_names as $column_name) {
                $statement->bindParam(':'.$column_name, $row[$column_name]);
            }
            
            $statement->execute();
        }
        $db->commit();
    } catch(PDOException $ex) {
        //Something went wrong rollback!
        $db->rollBack();
        echo $ex->getMessage();
    } finally {
        if ($ignore) {
            $db->exec("SET FOREIGN_KEY_CHECKS = 1");
        }
    }
}

function translate($column_names, $rows) {
    $data = array();
    foreach( $rows as $row ) {
        foreach($column_names as $new_name => $old_name) {
            $d[$new_name] = $row[$old_name];
        }
        $data[] = $d;
    }
    return $data;
}

function getAssoc($db, $query){
    return $db->query($query)->fetchAll();
}

function tblcount($db, $tbl) {
    return $db->query("SELECT count(*) as count FROM $tbl")->fetchColumn();
}

function transposeArray($array) {
    $return = array();
    foreach($array as $key => $value) {
        $values = (array) $value;
        foreach($values as $v) {
            $return[$v] = $key;
        }
    }
    return $return;
}