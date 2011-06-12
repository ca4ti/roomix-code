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
  $Id: paloSantoCompanyReport.class.php,v 1.1 2011-05-18 04:05:28 Franck Danard franckd@agmp.org Exp $ */
class paloSantoCompanyReport{
    var $_DB;
    var $errMsg;

    function paloSantoCompanyReport(&$pDB)
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

    function getNumCompanyReport($filter_field, $filter_value)
    {
        $where    = "";
        $arrParam = null;
        if(isset($filter_field) & $filter_field !=""){
            $where    = "where $filter_field like ?";
            $arrParam = array("$filter_value%");
        }

        $query   = "SELECT COUNT(*) FROM table $where";

        $result=$this->_DB->getFirstRowQuery($query, false, $arrParam);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }
        return $result[0];
    }

    function getCheckInCompanyReport($date_start,$date_end)
    {
	 $query   = "TRUNCATE TABLE `calendar`";
        $result=$this->_DB->fetchTable($query, true, "");

	 $query   = "CALL fill_calendar('$date_start', ADDDATE('$date_end', INTERVAL 1 DAY))";
        $result=$this->_DB->fetchTable($query, true, "");

        $query	= "SELECT calendar.datefield AS DATE,
       		   IFNULL(count(date_ci),0) AS Num_ci
			   FROM register RIGHT JOIN calendar ON (DATE(date_ci) = calendar.datefield)
			   WHERE (calendar.datefield BETWEEN (SELECT MIN(DATE('$date_start')) FROM register) AND (SELECT MAX(DATE('$date_end')) FROM register WHERE status = '0'))
			   GROUP BY DATE;";

        $result=$this->_DB->fetchTable($query, true, "");

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    function getTotalRooms($date_start,$date_end)
    {
	 $query   = "TRUNCATE TABLE `calendar`";
        $result=$this->_DB->fetchTable($query, true, "");

	 $query   = "CALL fill_calendar('$date_start', ADDDATE('$date_end', INTERVAL 1 DAY))";
        $result=$this->_DB->fetchTable($query, true, "");

	 $query 	= "SELECT calendar.datefield AS DATE, 
			   IFNULL(sum(total_room),0) AS Total_Room
			   FROM register RIGHT JOIN calendar ON (DATE(date_co) = calendar.datefield)
			   WHERE (calendar.datefield BETWEEN (SELECT MIN(DATE('$date_start')) FROM register) AND 
			   (SELECT MAX(DATE('$date_end')) FROM register WHERE status ='0'))
			   GROUP BY DATE";

        $result=$this->_DB->fetchTable($query, true, "");

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    function getTotalCalls($date_start,$date_end)
    {
	 $query   = "TRUNCATE TABLE `calendar`";
        $result=$this->_DB->fetchTable($query, true, "");

	 $query   = "CALL fill_calendar('$date_start', ADDDATE('$date_end', INTERVAL 1 DAY))";
        $result=$this->_DB->fetchTable($query, true, "");

	 $query 	= "SELECT calendar.datefield AS DATE, 
			   IFNULL(sum(total_call),0) AS Total_Calls
			   FROM register RIGHT JOIN calendar ON (DATE(date_co) = calendar.datefield)
			   WHERE (calendar.datefield BETWEEN (SELECT MIN(DATE('$date_start')) FROM register) AND 
			   (SELECT MAX(DATE('$date_end')) FROM register WHERE status ='0'))
			   GROUP BY DATE";

        $result=$this->_DB->fetchTable($query, true, "");

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    function getTotalBar($date_start,$date_end)
    {

	 $query   = "TRUNCATE TABLE `calendar`";
        $result=$this->_DB->fetchTable($query, true, "");

	 $query   = "CALL fill_calendar('$date_start', ADDDATE('$date_end', INTERVAL 1 DAY))";
        $result=$this->_DB->fetchTable($query, true, "");

	 $query 	= "SELECT calendar.datefield AS DATE, 
			   IFNULL(sum(total_bar),0) AS Total_Bar
			   FROM register RIGHT JOIN calendar ON (DATE(date_co) = calendar.datefield)
			   WHERE (calendar.datefield BETWEEN (SELECT MIN(DATE('$date_start')) FROM register) AND 
			   (SELECT MAX(DATE('$date_end')) FROM register WHERE status ='0'))
			   GROUP BY DATE";

        $result=$this->_DB->fetchTable($query, true, "");

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    function getTotalBilling($date_start,$date_end)
    {
	 $query   = "TRUNCATE TABLE `calendar`";
        $result=$this->_DB->fetchTable($query, true, "");

	 $query   = "CALL fill_calendar('$date_start', ADDDATE('$date_end', INTERVAL 1 DAY))";
        $result=$this->_DB->fetchTable($query, true, "");

	 $query 	= "SELECT calendar.datefield AS DATE, 
			   IFNULL(sum(total_billing),0) AS Total_Billing
			   FROM register RIGHT JOIN calendar ON (DATE(date_co) = calendar.datefield)
			   WHERE (calendar.datefield BETWEEN (SELECT MIN(DATE('$date_start')) FROM register) AND 
			   (SELECT MAX(DATE('$date_end')) FROM register WHERE status ='0'))
			   GROUP BY DATE";

        $result=$this->_DB->fetchTable($query, true, "");

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    function getCheckOutCompanyReport($date_start,$date_end)
    {
	 $query   = "TRUNCATE TABLE `calendar`";
        $result=$this->_DB->fetchTable($query, true, "");

	 $query   = "CALL fill_calendar('$date_start', ADDDATE('$date_end', INTERVAL 1 DAY))";
        $result=$this->_DB->fetchTable($query, true, "");

        $query	= "SELECT calendar.datefield AS DATE,
       		   IFNULL(count(date_co),0) AS Num_co
			   FROM register RIGHT JOIN calendar ON (DATE(date_co) = calendar.datefield)
			   WHERE (calendar.datefield BETWEEN (SELECT MIN(DATE('$date_start')) FROM register) AND (SELECT MAX(DATE('$date_end')) FROM register WHERE status = '0'))
			   GROUP BY DATE;";

        $result=$this->_DB->fetchTable($query, true, "");

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    function getCompanyReportById($id)
    {
        $query = "SELECT * FROM table WHERE id=?";

        $result=$this->_DB->getFirstRowQuery($query, true, array("$id"));

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }
}
?>