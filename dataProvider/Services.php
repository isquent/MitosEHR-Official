<?php
if(!isset($_SESSION)){
    session_name ('MitosEHR');
    session_start();
    session_cache_limiter('private');
}
include_once($_SESSION['site']['root'].'/classes/dbHelper.php');
/**
 * @brief       Services Class.
 * @details     This class will handle all services
 *
 * @author      Ernesto J. Rodriguez (Certun) <erodriguez@certun.com>
 * @version     Vega 1.0
 * @copyright   Gnu Public License (GPLv3)
 *
 */
class Services {
    /**
     * @var dbHelper
     */
    private $db;

    function __construct(){
        return $this->db = new dbHelper();
    }

    /**
     * @param stdClass $params
     * @return array
     */
    public function getServices(stdClass $params)
    {
        $sortx = $params->sort ? $params->sort[0]->property.' '.$params->sort[0]->direction : 'code ASC';
        $this->db->setSQL("SELECT *
                         FROM codes
                        WHERE code_text      LIKE '%$params->query%'
                           OR code_text_short LIKE '%$params->query%'
                           OR code            LIKE '$params->query%'
                           OR modifier 	      LIKE '$params->query%'
                           OR units 	      LIKE '$params->query%'
                           OR fee 	          LIKE '$params->query%'
                           OR related_code 	  LIKE '$params->query%'
                     ORDER BY $sortx");
        $records = $this->db->fetchRecords(PDO::FETCH_CLASS);
        if($params->code_type != 'all'){
            $records = $this->db->filterByQuery($records, 'code_type', $params->code_type);
        }
        $records = $this->db->filterByQuery($records, 'active', $params->active);
        $total   = count($records);
        $records = $this->db->filterByStartLimit($records,$params);
        return array('totals'=>$total,'rows'=>$records);
    }

    /**
     * @param stdClass $params
     * @return stdClass
     */
    public function addService(stdClass $params)
    {
        $data = get_object_vars($params);
        unset($data['id']);
        $sql = $this->db->sqlBind($data, "codes", "I");
        $this->db->setSQL($sql);
        $this->db->execLog();
        $params->id = $this->db->lastInsertId;
        return $params;
    }

    /**
     * @param stdClass $params
     * @return stdClass
     */
    public function updateService(stdClass $params)
    {
        $data = get_object_vars($params);
        $id = $data['id'];
        unset($data['id']);
        $sql = $this->db->sqlBind($data, "codes", "U", "id='$id'");
        $this->db->setSQL($sql);
        $this->db->execLog();
        return $params;
    }

    public function liveIDCXSearch(stdClass $params){
        $Str = explode(',', $params->query );
        $queryStr = trim(end(array_values($Str)));
        $queries = explode(' ', $queryStr);

//        $sql = "SELECT * FROM codes WHERE ";
//        foreach($queries as $query){
//            $sql .= "(code_text LIKE '%$query%' OR code_text_short LIKE '%$query%' OR code LIKE '$query%' OR related_code LIKE '$query%') AND ";
//        }
//        $sql .= "code_type = '2'";
//
//        //print $sql;
//
//        $this->db->setSQL($sql);
//        $records = $this->db->fetchRecords(PDO::FETCH_ASSOC);

        // primary array where all the record will be store
        $records = array();
        $idHaystack = array();
        foreach($queries as $query){
            $this->db->setSQL("SELECT *
                                 FROM codes
                                WHERE (code_text      LIKE '%$query%'
                                   OR code_text_short LIKE '%$query%'
                                   OR code            LIKE '$query%'
                                   OR related_code 	  LIKE '$query%')
                                   AND code_type = '2'
                             ORDER BY code ASC");
            foreach($this->db->fetchRecords(PDO::FETCH_ASSOC) as $row){
                if(array_key_exists($row['id'], $idHaystack)){
                    $records[$row['id']]['weight']++;
                }else{
                    $idHaystack[$row['id']] = true;
                    $row['weight'] = 1;
                    $records[$row['id']] = $row;
                }
            }
        }
        function cmp($a, $b) {
          if ($a['weight'] === $b['weight']) {
            return 0;
          } else {
            return $a['weight'] < $b['weight'] ? 1 : -1; // reverse order
          }
        }
        usort($records, 'cmp');

        $total   = count($records);
        $records = array_slice($records,$params->start,$params->limit);
        return array('totals'=>$total,'rows'=>$records);
    }


    public function getCptCodesBySelection(stdClass $params){

        return $this->getCptUsedByPid($params->pid);

    }


    public function getIcdxByEid($eid){
        $this->db->setSQL("SELECT * FROM encounter_codes_icdx WHERE eid = '$eid' ORDER BY id ASC");
        return $this->db->fetchRecords(PDO::FETCH_ASSOC);
    }

    public function getIcdxUsedBPid($pid){
        $this->db->setSQL("SELECT DISTINCT eci.code, codes.code_text
                             FROM encounter_codes_icdx AS eci
                        left JOIN codes ON eci.code = codes.code
                        LEFT JOIN form_data_encounter AS e ON eci.eid = e.eid
                            WHERE e.pid = '$pid'
                         ORDER BY e.start_date DESC");
        return $this->db->fetchRecords(PDO::FETCH_ASSOC);
    }

    public function getCptByEid($eid){
        $this->db->setSQL("SELECT * FROM encounter_codes_cpt WHERE eid = '$eid' ORDER BY id ASC");
        return $this->db->fetchRecords(PDO::FETCH_ASSOC);
    }

    public function getCptUsedByPid($pid){
        $this->db->setSQL("SELECT DISTINCT ecc.code, codes.code_text, e.start_date as last_date
                             FROM encounter_codes_cpt AS ecc
                        left JOIN codes ON ecc.code = codes.code
                        LEFT JOIN form_data_encounter AS e ON ecc.eid = e.eid
                            WHERE e.pid = '$pid'
                         ORDER BY e.start_date DESC");
        return $this->db->fetchRecords(PDO::FETCH_ASSOC);
    }

}

//$params = new stdClass();
//$params->query = 'head neoplasm face';
//$params->pid = '7';
//$params->start = 0;
//$params->limit = 25;
//
//$t = new Services();
//print '<pre>';
//print_r($t->getCptUsedByPid(10));
