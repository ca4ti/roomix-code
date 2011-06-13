<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.0-23                                               |
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
  $Id: paloSantoBillingreport.class.php,v 1.1 2010-06-16 08:06:19 Franck Danard franckd@agmp.org Exp $ */
class paloSantoBillingreport {
    var $_DB;
    var $errMsg;

    function paloSantoBillingreport(&$pDB)
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

    function getNumBillingreport($filter_field, $filter_value)
    {
        $where = "where status = '0'";
        if(isset($filter_field) & $filter_field !="")
            $where = "where status = '0' and $filter_field like '$filter_value%'";

        $query   = "SELECT COUNT(*) FROM register $where";

        $result=$this->_DB->getFirstRowQuery($query);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }
        return $result[0];
    }

    function UpdateBilling($sTabla, $arrValores ){
	// call function construirUpdate
	$where = "id = $arrValores[id]";
	$query = $this->_DB->construirUpdate($sTabla, $arrValores, $where);

	// now execute the query with genQuery(fucntion of the paloSantoDB.class)
	$result = $this->_DB->genQuery($query);

	// catch the error
	if($result==FALSE)
       return;
       return; 
    }

    function getBillingreport($limit, $offset, $filter_field, $filter_value)
    {
        $where = "where status = '0'";
        if(isset($filter_field) & $filter_field !="")
            $where = "where status = '0' and $filter_field like '$filter_value%'";

        $query   = "SELECT * FROM register $where ORDER BY `date_co` ASC LIMIT $limit OFFSET $offset";

        $result=$this->_DB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    function getRoomBillingreport($filter_field, $filter_value)
    {
        $where = "where status = '0'";
        if(isset($filter_field) & $filter_field !="")
            $where = "where $filter_field like '$filter_value%'";

        $query   = "SELECT * FROM rooms $where";

        $result=$this->_DB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result[0];
    }

    function getRegister($where)
    {
        $query   = "SELECT * FROM register $where ORDER BY `date_co` ASC";

        $result=$this->_DB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result[0];
    }

    function getGuestBillingreport($filter_field, $filter_value)
    {
        $where = "where status = '0'";
        if(isset($filter_field) & $filter_field !="")
            $where = "where $filter_field like '$filter_value%'";

        $query   = "SELECT * FROM guest $where ";

        $result=$this->_DB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result[0];
    }

    function getBillingreportById($id)
    {
        $query = "SELECT * FROM register WHERE id=$id and status = '0' ORDER BY `date_co` ASC";

        $result=$this->_DB->getFirstRowQuery($query,true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }
}
?>