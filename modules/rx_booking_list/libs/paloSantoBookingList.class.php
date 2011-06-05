<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.4-21                                               |
  | http://www.elastix.org                                               |
  +----------------------------------------------------------------------+
  | Copyright (c) 2006 Palosanto Solutions S. A.                         |
  +----------------------------------------------------------------------+
  | Cdla. Nueva Kennedy Calle E 222 y 9na. Este                          |
  | Telfs. 2283-268, 2294-440, 2284-356                                  |
  | Guayaquil - Ecuador                                                  |
  | http://www.palosanto.com                                             |
  +----------------------------------------------------------------------+
  | The contents of this file are subject to the General Public License  |
  | (GPL) Version 2 (the "License"); you may not use this file except in |
  | compliance with the License. You may obtain a copy of the License at |
  | http://www.opensource.org/licenses/gpl-license.php                   |
  |                                                                      |
  | Software distributed under the License is distributed on an "AS IS"  |
  | basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See  |
  | the License for the specific language governing rights and           |
  | limitations under the License.                                       |
  +----------------------------------------------------------------------+
  | The Original Code is: Elastix Open Source.                           |
  | The Initial Developer of the Original Code is PaloSanto Solutions    |
  +----------------------------------------------------------------------+
  $Id: paloSantoBookingList.class.php,v 1.1 2011-05-26 06:05:07 Franck Danard franckd@agmp.org Exp $ */
class paloSantoBookingList{
    var $_DB;
    var $errMsg;

    function paloSantoBookingList(&$pDB)
    {
        // Se recibe como parámetro una referencia a una conexión paloDB
        if (is_object($pDB)) {
            $this->_DB =& $pDB;
            $this->errMsg = $this->_DB->errMsg;
        } else {
            $dsn = (string)$pDB;
            $this->_DB = new paloDB($dsn);

            if (!$this->_DB->connStatus) {
                $this->errMsg = $this->_DB->errMsg;
                // debo llenar alguna variable de error
            } else {
                // debo llenar alguna variable de error
            }
        }
    }

    /*HERE YOUR FUNCTIONS*/

    function getNumBookingList($filter_field, $filter_value)
    {
        $where    = "WHERE date_ci is not null";
        $arrParam = null;
        if(isset($filter_field) & $filter_field !=""){
            $where    = "where $filter_field like '%$filter_value%' and date_ci is not null ";
            //$arrParam = array("$filter_value%");
        }

        $query   = "SELECT COUNT(*) FROM booking RIGHT JOIN `rooms` ON room_id  = rooms.id RIGHT JOIN `guest` ON guest_id = guest.id $where";
	 // echo $query;
        $result=$this->_DB->getFirstRowQuery($query, false, "");

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }
        return $result[0];
    }

    function getBookingList($limit, $offset, $filter_field, $filter_value)
    {
        $where    = "WHERE date_ci is not null";
        $arrParam = null;
        if(isset($filter_field) & $filter_field !=""){
            $where    = "where $filter_field like '%$filter_value%' and date_ci is not null";
            //$arrParam = array("$filter_value%");
        }

        $query   = "SELECT `room_name`, `first_name`, `last_name`, `num_guest`,
                    DATE(date_format(`date_ci`,'%Y-%m-%d'))  AS date_ci , 
		      DATE(date_format(`date_co`,'%Y-%m-%d')) AS date_co  
		      FROM `booking` 
		      RIGHT JOIN `rooms` ON room_id  = rooms.id
		      RIGHT JOIN `guest` ON guest_id = guest.id $where LIMIT $limit OFFSET $offset";

        $result=$this->_DB->fetchTable($query, true, "");

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    function getBookingListById($id)
    {
        $query = "SELECT * FROM booking WHERE date_ci is not null";

        $result=$this->_DB->getFirstRowQuery($query, true, array("$id"));

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }
}
?>