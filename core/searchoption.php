<?php

class SearchOptions{

    static public function formSearchDisplay($searchValue, $viewOnly) {
        $searchOption = "<div class='search-gray-box'><table width='100%' >";
        $searchOption .= SelectGenerator::formSearchDisplay(self::difficultyParam(), $searchValue, $viewOnly);
        $searchOption .= SelectGenerator::formSearchDisplay(self::bibleSectionParam(), $searchValue, $viewOnly);
        $searchOption .= SelectGenerator::formSearchDisplay(self::bibleBooksParam(), $searchValue, $viewOnly);
        $searchOption .= SelectGenerator::formSearchDisplay(self::typeOfKnowledgeParam(), $searchValue, $viewOnly);

        return $searchOption . "</table></div>";
    }
    static public function formSearchListDisplay() {
        $searchValue = ''; $viewOnly = false;
        $searchOption = "<div class='row'>";
        $searchOption .= SelectGenerator::formSearchListDisplay(self::difficultyParam());
        $searchOption .= SelectGenerator::formSearchListDisplay(self::bibleSectionParam());
        $searchOption .= SelectGenerator::formSearchListDisplay(self::bibleBooksParam());
        $searchOption .= SelectGenerator::formSearchListDisplay(self::typeOfKnowledgeParam());

        $tmp  = $searchOption . "</div>";
        $tmp = str_replace('XX_COL_XX','3', $tmp);
        return $tmp;
    }
    static public function formSearchListValueSQLQuery() {
        $searchOption = " (1 = 1 ";
        $searchOption .= " AND " . SelectGenerator::formSearchValueSQLQuery(self::difficultyParam());
        $searchOption .= " AND " . SelectGenerator::formSearchValueSQLQuery(self::bibleSectionParam());
        $searchOption .= " AND " . SelectGenerator::formSearchValueSQLQuery(self::bibleBooksParam());
        $searchOption .= " AND " . SelectGenerator::formSearchValueSQLQuery(self::typeOfKnowledgeParam());

        return $searchOption . ") ";
    }

    static public function listSearchDisplay($searchValue) {
        $searchOption = '';
        $searchOption .= SelectGenerator::displayValueForList(self::difficultyParam(), $searchValue);
        $searchOption .= SelectGenerator::displayValueForList(self::bibleSectionParam(), $searchValue);
        $searchOption .= SelectGenerator::displayValueForList(self::bibleBooksParam(), $searchValue);
        $searchOption .= SelectGenerator::displayValueForList(self::typeOfKnowledgeParam(), $searchValue);

        return $searchOption;
    }

    static public function valueSearchDisplay() {
        $searchOption = '';
        $searchOption .= SelectGenerator::getValueForm(self::difficultyParam());
        $searchOption .= SelectGenerator::getValueForm(self::bibleSectionParam());
        $searchOption .= SelectGenerator::getValueForm(self::bibleBooksParam());
        $searchOption .= SelectGenerator::getValueForm(self::typeOfKnowledgeParam());

        return $searchOption;
    }

    #region Difficulties
    static public function difficultyParam (){ 
        return array(
            "data" => self::getDifficulties(),
            /* array(
                "all" => "All / Toute",
                "old" => "Ancienne Alliance",
                "new" => "Nouvelle Alliance",
                "torah" => "Torah",
                "nevi'im" => "Nevi'im",
                "ketouvim" => "ketouvim",
                "evangiles" => "Gospel / Evangiles",
                "testament" => "testament"
            ),*/
            "tag" => "difficulty",
            "displayName" => "Difficulty",
            "defaultValue" => "all"
        );
    }
    #endregion

    #region Bible Section
    static public function bibleSectionParam() {
        return array(
            "data" => self::getBiblesections(), /*array(
                "all" => "All / Toute",
                "easy" => "Easy / Simple",
                "average" => "Average / Moyen",
                "high" => "High / Grande",
                "expert" => "Very High / Trés Grande"
            ),*/  
            "tag" => "bibleSection",
            "displayName" => "Bible Section",
            "defaultValue" => "all"      
            
        );
    }
    #endregion

    #region Bible book
    static public function bibleBooksParam() {
        return array(
            "data" => self::getBibleBooks(), /*array(
                "all" => "All / Toute",
                "easy" => "Easy / Simple",
                "average" => "Average / Moyen",
                "high" => "High / Grande",
                "expert" => "Very High / Trés Grande"
            ),*/  
            "tag" => "bibleBooks",
            "displayName" => "Bible Book",
            "defaultValue" => "all"      
            
        );
    }
    #endregion

    #region Type of knowledge
    static public function typeOfKnowledgeParam() {
        return array(
            "data" => self::getTypeOfKnowledge(), /*array(
                "all" => "All / Toute",
                "easy" => "Easy / Simple",
                "average" => "Average / Moyen",
                "high" => "High / Grande",
                "expert" => "Very High / Trés Grande"
            ),*/  
            "tag" => "typeOfKnowledge",
            "displayName" => "Type of Knowledge",
            "defaultValue" => "all"      
            
        );
    }
    #endregion

    #region utilities methods
    static function getDifficulties() {
        
        if(isset($_SESSION['difficultiesConstants'])) return $_SESSION['difficultiesConstants'];
        
        $dataArray = self::baseGetConstants("/constants/difficulties");

        //print_r($dataArray);
        $_SESSION['difficultiesConstants'] = $dataArray;
        return $dataArray;
    }
    static function getBiblesections() {
        if(isset($_SESSION['bibleSectionsConstants'])) return $_SESSION['bibleSectionsConstants'];

        $dataArray = self::baseGetConstants("/constants/biblesections");

        //print_r($dataArray);
        $_SESSION['bibleSectionsConstants'] = $dataArray;
        return $dataArray;
    }
    static function getBibleBooks() {
        if(isset($_SESSION['bibleBooksConstants'])) return $_SESSION['bibleBooksConstants'];
        
        $dataArray = self::baseGetConstants("/constants/biblebooks");

        //print_r($dataArray);
        $_SESSION['bibleBooksConstants'] = $dataArray;
        return $dataArray;
    }
    static function getTypeOfKnowledge() {
        if(isset($_SESSION['typeKnowledgeConstants'])) return $_SESSION['typeKnowledgeConstants'];
        
        $dataArray = self::baseGetConstants("/constants/typeknowledge");

        //print_r($dataArray);
        $_SESSION['typeKnowledgeConstants'] = $dataArray;
        return $dataArray;
    }
    static function baseGetConstants($urlPart) {
        $url = $GLOBALS['ApiUrl'] . $urlPart;

        // Initialize cURL
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute request
        $response = curl_exec($ch);
        
        // Check for errors
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
            curl_close($ch);
            exit;
        }

        curl_close($ch);

        // Decode JSON response
        $result = json_decode($response, true);

        // Get associative array from "data"
        $dataArray = $result['data'] ?? [];
        return $dataArray;
    }
    #endregion

    #region bible part
    static public  $biblePart = array(
        "all" => "All / Toute",
        "easy" => "Easy / Simple",
        "average" => "Average / Moyen",
        "high" => "High / Grande",
        "expert" => "Very High / Trés Grande"
        );
    static private function getValueFormForDifficulties() {
        $value = "";
        if ( isset($_POST['biblepartSearchOption']) ) {
            $value = "<biblepart>". $_POST['difficultySearchOption'] ."</biblepart>";
        }
        return $value;
    }

    static private function getValueForDifficulties($searchValue) {
        $difficulty = "all";
        if (preg_match('/<biblepart>(.*?)<\/biblepart>/', $searchValue, $matches)) {
            $difficulty = $matches[1];
        }
        return $difficulty;
    }

    static private function displayValueListForDifficulties($searchValue) {
        $difficulty = self::getValueForDifficulties($searchValue);
        return "Difficulty: " . self::$biblePart[ $difficulty ];
    }

    static private function formSearchDisplayDifficulties($searchValue) {
        $difficulty = self::getValueForDifficulties($searchValue);
        $tmp = "<tr><td align='right'>Difficulty :</td><td><select name='difficultySearchOption'>";
        foreach (self::$biblePart as $key => $value) {
            $tmp .= "<option value='$key' ". ($key==$difficulty ? " SELECTED=\"SELECTED\" " : "") .">$value</option>";
        }
        $tmp .= "</select></td></tr>";
        return $tmp;
    }
    #endregion
    
}
    class SelectGenerator {
        static public function getValueForm($param) {
            $value = "";
            if ( isset($_POST[ $param['tag'] . 'SearchOption']) ) {
                $value = "<". $param['tag'] .">". $_POST[ $param['tag'] . 'SearchOption'] ."</". $param['tag'] .">";
            }
            
            return $value;
        }
    
        static public function getValue($param, $searchValue) {
            $value = $param['defaultValue'];
            if (preg_match('/<'. $param['tag'] .'>(.*?)<\/'. $param['tag'] .'>/', $searchValue, $matches)) {
                $value = $matches[1];
            }
            return $value;
        }
    
        static public function displayValueForList($param, $searchValue) {
            $difficulty = self::getValue($param, $searchValue);
            return " [ ". $param['displayName'] ." : " . (isset($param['data'][ $difficulty ]) ? $param['data'][ $difficulty ] : $difficulty) . " ] ";
        }
    
        static public function formSearchDisplay($param, $searchValue, $viewOnly) {
            $currentValue = self::getValue($param, $searchValue);
            $tmp = "<tr><td align='right'>". $param['displayName'] ." :</td>
                    <td><select name='".$param['tag']."SearchOption' ". ($viewOnly ? " disabled " : "") .">";
            foreach ($param['data'] as $key => $value) {
                $tmp .= "<option value='$key' ". ( strtoupper($key) == strtoupper($currentValue) ? " SELECTED=\"SELECTED\" " : "") .">$value</option>";
            }
            $tmp .= "</select></td></tr>";
            return $tmp;
        }

        static public function formSearchListDisplay($param) {
            $varName = $param['tag'].'SearchOption';

            $currentValue = isset($_POST[$varName]) ? $_POST[$varName] : "all";
            $tmp = "<div class='col-md-XX_COL_XX'>". $param['displayName'] ." :<br/>
                    <select name='".$param['tag']."SearchOption' >";
            foreach ($param['data'] as $key => $value) {
                $tmp .= "<option value='$key' ". ( strtoupper($key) == strtoupper($currentValue) ? " SELECTED=\"SELECTED\" " : "") .">$value</option>";
            }
            $tmp .= "</select></div>";
            return $tmp;
        }
        static public function formSearchValueSQLQuery($param) {
            $varName = $param['tag'].'SearchOption';
            $value = isset($_POST[$varName]) ? $_POST[$varName] : "";
            $value = $value == "all" ? "" : $value;

            $searchQuery = " searchoptions LIKE '%%' ";
            if($value != "") {
                $searchQuery = " searchoptions LIKE '%<". $param['tag'] .">$value</". $param['tag'] .">%' ";
            }

            return $searchQuery;
        }
    }

?>