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


?>