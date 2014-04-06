<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.0-18                                               |
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
  $Id: paloSantoHome.class.php,v 1.1 2010-03-28 08:03:53 Franck Danard franckd@agmp.org Exp $ */
class paloSantoHome {
    var $_DB;
    var $errMsg;

    function paloSantoHome(&$pDB)
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

    function getNumHome($filter_field, $filter_value)
    {
        $where = "";
        if(isset($filter_field) & $filter_field !="")
            $where = "where $filter_field like '$filter_value%'";

        $query   = "SELECT COUNT(*) FROM rooms $where";

        $result=$this->_DB->getFirstRowQuery($query);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }
        return $result[0];
    }

    function getHome()
    {
        $query   = "SELECT id, room_name FROM rooms WHERE free=1";

        $result=$this->_DB->fetchTable($query, true);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        foreach($result as $k => $v)
        $arrRoom[$v['id']] = $v['room_name'];
        return $arrRoom;
    }

    function getVersion()
    {
        $query   = "SELECT version FROM config";

        $result=$this->_DB->fetchTable($query, true);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result[0];
    }

    function getHomeById($id)
    {
        $query = "SELECT room_name FROM rooms WHERE id=$id";

        $result=$this->_DB->getFirstRowQuery($query,true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    function getBookingStatus()
    {
       $query    = "SELECT count(id) AS booking FROM `booking` WHERE DATE(date_format(`date_ci`,'%Y-%m-%d')) = current_date();";

        $result=$this->_DB->fetchTable($query, true, "");

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result[0]['booking'];
    }

    function ToDay()
    {
        $query   = "SELECT date( current_date( ) ) AS ToDay";

        $result=$this->_DB->fetchTable($query, true, "");

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result[0]['ToDay'];
    }

    function Clean_booking()
    {
        $query   = "DELETE FROM register WHERE status=2 and date_ci < current_date()";

        $result=$this->_DB->fetchTable($query, true, "");

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return;
    }
}
?>