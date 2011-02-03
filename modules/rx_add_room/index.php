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
  $Id: index.php,v 1.1 2010-04-03 04:04:58 Franck Danard franckd@agmp.org Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoAddRoom.class.php";
    include_once "modules/$module_name/libs/paloSantoModel.class.php";
    $DocumentRoot = (isset($_SERVER['argv'][1]))?$_SERVER['argv'][1]:"/var/www/html";
    require_once("$DocumentRoot/libs/misc.lib.php");

    //include file language agree to elastix configuration
    //if file language not exists, then include language by default (en)
    $lang=get_language();
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $lang_file="modules/$module_name/lang/$lang.lang";
    if (file_exists("$base_dir/$lang_file")) include_once "$lang_file";
    else include_once "modules/$module_name/lang/en.lang";

    //global variables
    global $arrConf;
    global $arrConfModule;
    global $arrLang;
    global $arrLangModule;
    $arrConf = array_merge($arrConf,$arrConfModule);
    $arrLang = array_merge($arrLang,$arrLangModule);

    //folder path for custom templates
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    //conexion resource
    $pDB = new paloDB($arrConf['dsn_conn_database']);
    $pDBM = "mysql://root:".obtenerClaveConocidaMySQL('root')."@localhost/roomx";

    //actions
    $action = getAction();
    $content = "";
    $_DATA=$_POST;

    $pAddRoom = new paloSantoAddRoom($pDB);
    $pAddRoomModel = new paloSantoModel($pDBM);

    switch($action){
        case "save_new":
            $content = saveAddRoom($smarty, $module_name, $local_templates_dir, $pDB, $pDBM, $arrConf, $arrLang);
            break;
        default:
            $content = reportAddRoom($smarty, $module_name, $local_templates_dir, $pDB, $pDBM, $arrConf, $arrLang);
            break;
    }
    return $content;
}

function saveAddRoom($smarty, $module_name, $local_templates_dir, &$pDB, &$pDBM, $arrConf, $arrLang)
{
       $pFreePBX = new paloSantoAddRoom($pDB);
       $pRoomX = new paloSantoModel($pDBM);

       $_DATA = $_POST;

	$r_selected=$_DATA['room'];

       foreach($r_selected as $key => $value)
	{
       	$room_idx = $pFreePBX -> getAddRoom('','','extension',$value);
       	$room_name = $room_idx[0];

		$where = "where extension = '$value'";
              $arrRoomExist = $pRoomX->get_Room('rooms' , $where);

		$arrValores['id']	    = "'".$arrRoomExist['id']."'";
       	$arrValores['extension'] = "'".$value."'";
       	$arrValores['room_name'] = "'".$room_name['name']."'";
       	$arrValores['model']     = "'".$_DATA[intval($value)]."'";

		if ( $arrRoomExist['extension'] != $value ) 
		{
       		$save_rooms = $pRoomX->insertQuery('rooms',$arrValores);	
		}
		else
		{
			$save_rooms = $pRoomX->updateQuery('rooms',$arrValores);
		}

        // Modify the context extension into Freepbx data
        //-----------------------------------------------
        $value_rl['value']  	= "'true'";
        $where              	= "variable = 'need_reload';";
        $arrReload          	= $pFreePBX->updateFreepbx('admin',$value_rl, $where);

        $value_ac['data']   	= "'from-roomx'";
        $where              	= "id = '".$value."' and keyword = 'context';";
        $arrAccount         	= $pFreePBX->updateFreepbx('sip',$value_ac, $where);

        $cmd="/var/lib/asterisk/bin/module_admin reload";
        exec($cmd);	
        }

}

function reportAddRoom($smarty, $module_name, $local_templates_dir, &$pDB, &$pDBM, $arrConf, $arrLang)
{
    $pAddRoom = new paloSantoAddRoom($pDB);
    $pAddRoomModel = new paloSantoModel($pDBM);
    $_DATA = $_POST;

    $filter_field = getParameter("filter_field");
    $filter_value = getParameter("filter_value");
    $action = getParameter("nav");
    $start  = getParameter("start");
    $as_csv = getParameter("exportcsv");

    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    $totalAddRoom = $pAddRoom->getNumAddRoom($filter_field, $filter_value);
    $totalModel = $pAddRoomModel->getNumAddRoomModel('models',$filter_field, $filter_value);

    $limit  = 10;
    $total  = $totalAddRoom;
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $oGrid->enableExport();   // enable csv export.
    $oGrid->pagingShow(true); // show paging section.

    $oGrid->calculatePagination($action,$start);
    $offset = $oGrid->getOffsetValue();
    $end    = $oGrid->getEnd();
    $url    = "?menu=$module_name&filter_field=$filter_field&filter_value=$filter_value";
    $select = "";

    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("DELETE", $arrLang["Delete"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("IMG", "images/list.png");

    $arrData = null;
    $arrResult =$pAddRoom->getAddRoom($limit, $offset, $filter_field, $filter_value);

    $arrModel = $pAddRoomModel->getModel('models','', '', $filter_field, $filter_value);

    if(is_array($arrResult) && $total>0){
        foreach($arrResult as $key => $value){
	 
	    $where = "where extension = '".$value['extension']."'";
           $arrRooms = $pAddRoomModel->getModel('rooms', $where);
           $chk=0;

           foreach($arrRooms as $key_room => $value_rooms){
           	if ($value_rooms['extension'] == $value['extension']) {
                     $chk=1;
			}
	    }

	    $select="";
    	    if(is_array($arrModel) && $totalModel>0){
            	foreach($arrModel as $key_r => $value_r){ 
	       
		$where = "where extension = '".$value['extension']."'";
           	$arrRoom = $pAddRoomModel->getModel('rooms', $where);
              if (is_array($arrRoom)){
           		$Room = $arrRoom['0'];
           		if ($Room['model'] == $value_r['room_model']) {
	    				$select = "<option selected='selected'>".$value_r['room_model']."</option>".$select;
	    			}
	    			else {
					$select = "<option>".$value_r['room_model']."</option>".$select;
	    			}
        		}
		}
    	    }

           $checked = "";
           if ( $chk == 1) 
                $checked = "checked";
           
	    $arrTmp[0] = "<input type='checkbox' name='room[".$key."]' value=".$value['extension']." ".$checked.">";
	    $arrTmp[1] = $value['extension'];
	    $arrTmp[2] = $value['name'];
	    $arrTmp[3] = "<select name='".$value['extension']."' >$select</select>";
           $arrData[] = $arrTmp;
        }
    }




    $arrGrid = array("title"       => $arrLang["Add Room"],
                        "icon"     => "images/list.png",
                        "width"    => "99%",
                        "start"    => ($total==0) ? 0 : $offset + 1,
                        "end"      => $end,
                        "total"    => $total,
                        "url"      => $url,
                        "columns"  => array(
			0 => array("name"      => $arrLang["Room"],
                                   "property1" => ""),
			1 => array("name"      => $arrLang["Extension"],
                                   "property1" => ""),
			2 => array("name"      => $arrLang["Name"],
                                   "property1" => ""),
			3 => array("name"      => $arrLang["Model"],
                                   "property1" => ""),
                                        )
                    );


    //begin section filter
    $arrFormFilterAddRoom = createFieldFilter($arrLang);
    $oFilterForm = new paloForm($smarty, $arrFormFilterAddRoom);
    $smarty->assign("SHOW", $arrLang["Show"]);

    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl","",$_POST);
    //end section filter

    if($as_csv == 'yes'){
        $name_csv = "AddRoom_".date("d-M-Y").".csv";
        header("Cache-Control: private");
        header("Pragma: cache");
        header("Content-Type: application/octec-stream");
        header("Content-disposition: inline; filename={$name_csv}");
        header("Content-Type: application/force-download");
        $content = $oGrid->fetchGridCSV($arrGrid, $arrData);
    }
    else{
        $oGrid->showFilter(trim($htmlFilter));
        $content = "<form  method='POST' style='margin-bottom:0;' action=\"$url\">".$oGrid->fetchGrid($arrGrid, $arrData,$arrLang)."</form>";
    }
    //end grid parameters

    return $content;
}


function createFieldFilter($arrLang){
    $arrFilter = array(
	    "extension" => $arrLang["Extension"],
	    "name" => $arrLang["Name"],
                    );

    $arrFormElements = array(
            "filter_field" => array("LABEL"                  => $arrLang["Search"],
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "SELECT",
                                    "INPUT_EXTRA_PARAM"      => $arrFilter,
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""),
            "filter_value" => array("LABEL"                  => "",
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "TEXT",
                                    "INPUT_EXTRA_PARAM"      => "",
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""),
                    );
    return $arrFormElements;
}


function getAction()
{
    if(getParameter("save_new")) //Get parameter by POST (submit)
        return "save_new";
    else if(getParameter("save_edit"))
        return "save_edit";
    else if(getParameter("delete")) 
        return "delete";
    else if(getParameter("new_open")) 
        return "view_form";
    else if(getParameter("action")=="view")      //Get parameter by GET (command pattern, links)
        return "view_form";
    else if(getParameter("action")=="view_edit")
        return "view_form";
    else
        return "report"; //cancel
}
?>