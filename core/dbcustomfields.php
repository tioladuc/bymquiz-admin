<?php
 require_once "DBCore.php";

 class CustomField {
    public $fieldName;
    public $query;
    public $data;

    public function __construct($fieldName, $query, $data) {
        $this->fieldName = $fieldName;
        $this->query = $query;
        $this->data = $data;

        if($query != null && trim($query == null ) == "" ) {
            $db = DBCore::getInstance();
            $dataTMP = $db->selectAll($query, []);
            $this->data = array();

            foreach($dataTMP as $item) {
                $this->data[ $item['_key_'] ] = $item['_value_'];
            }            
        }
    }

    public function displaySelect($currentValue, $isReadOnly = false, $addAllOption=false) {
        if($addAllOption) $this->data[''] = "All/Tout";
        $currentValue = $currentValue==null ? "" : $currentValue;

        $tmp = "<SELECT class='form-control' name='". $this->fieldName ."' ". ($isReadOnly ? " DISABLED " : "") ." >";
        foreach ($this->data as $key => $value) {
            $tmp .= "<option value='$key' ". 
                (strtoupper($currentValue) == strtoupper($key) ? " SELECTED=SELECTED " : "" ) .
                " >". ($addAllOption ? "Langue:" : "") ." $value </option>";
        }
        $tmp .= "</SELECT>";
        return $tmp;
    }
    public function getSelectValue() {
        return isset($_POST[$this->fieldName]) ? $_POST[$this->fieldName] : null;
    }
 }

 class Paging {
     public static $fieldName = 'pageName';
     public static $sql = null;
     public static $db = null;
     public static $params = null;

     public static function getValuePage() {
         return isset($_POST[self::$fieldName]) ? $_POST[self::$fieldName] : 1 ;
     }

     public static function getValuePagingSQL() {
        //echo "******************** LIMIT ". ((self::getValuePage() - 1)*$GLOBALS['MaxPage']) . " , " . $GLOBALS['MaxPage'] . " *************";
        return " LIMIT ". ((self::getValuePage() - 1)*$GLOBALS['MaxPage']) . " , " . $GLOBALS['MaxPage'] . " ";
    }

    public static function displayPaging() {
        $pattern = '/(select).*?(from)/is';
        $replacement = '$1 count(*) as total $2';
        $new_sql = self::$sql;
        $new_sql = str_replace("from_0_to_10", "XXZZYYWW", $new_sql);        
        $new_sql = preg_replace($pattern, $replacement, strtolower($new_sql));
        $new_sql = str_replace( "XXZZYYWW", "from_0_to_10", $new_sql);   
        $total = self::$db->selectOne($new_sql, self::$params)['total'];
        
        $remainder = $total % $GLOBALS['MaxPage'];
        $pages = ($total -$remainder)/ $GLOBALS['MaxPage'];
        $pages += $remainder == 0 ? 0 : 1;

        $hiddenFields = "";
        if($_POST) {
            foreach ($_POST as $key => $value) {
                if($key!=self::$fieldName && $key!="id" && $key!="delete") {
                    $hiddenFields .= "<input name='$key' type='hidden' value='$value' />";
                }
            }
        }

        $count = 0; $tmp = "";
        while( $count < $pages ) {
            $tmp .= "<td><form method='POST'>$hiddenFields<input type='submit' name='". self::$fieldName ."' 
                    value='".($count+1)."' class='btn ". ( self::getValuePage() ==($count+1) ? "btn-warning" : "btn-success") ."'  ></form></td>";
            $count++;
        }
        return "<center><table>$tmp</table></center>";
    }
    public static function setParameters($mySql, $myParams, $myBd) {
        self::$sql = $mySql;
        self::$db = $myBd;
        self::$params = $myParams;
    }
 }


?>