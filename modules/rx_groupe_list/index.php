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
  $Id: index.php,v 1.1 2010-04-03 05:04:03 Franck Danard franckd@agmp.org Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoGroupeList.class.php";

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
    //$pDB = "";

    //actions
    $action = getAction();
    $content = "";

    switch($action){
        case "save_edit":
            $content = saveNewAddGroupe($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "save_new":
            $content = viewFormAddGroupe($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        default:
            $content = reportGroupeList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
    }
    return $content;
}

function viewFormAddGroupe($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pAddGroupe = new paloSantoGroupeList($pDB);
    $arrFormAddGroupe = createFieldForm($arrLang, $pDB);
    $oForm = new paloForm($smarty,$arrFormAddGroupe);

    //begin, Form data persistence to errors and other events.
    $_DATA  = $_POST;
    $action = getParameter("action");
    $id     = getParameter("id");
    $smarty->assign("ID", $id); //persistence id with input hidden in tpl

    if($action=="view")
        $oForm->setViewMode();
    else if($action=="view_edit" || getParameter("save_edit"))
        $oForm->setEditMode();
    //end, Form data persistence to errors and other events.

    if($action=="view" || $action=="view_edit"){ // the action is to view or view_edit.
        $dataAddGroupe = $pAddGroupe->getAddGroupeById($id);
        if(is_array($dataAddGroupe) & count($dataAddGroupe)>0)
            $_DATA = $dataAddGroupe;
        else{
            $smarty->assign("mb_title", $arrLang["Error get Data"]);
            $smarty->assign("mb_message", $pAddGroupe->errMsg);
        }
    }

    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("IMG", "images/list.png");

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLang["Add Groupe"], $_DATA);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function saveNewAddGroupe($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pAddGroupe = new paloSantoGroupeList($pDB);
    $arrFormAddGroupe = createFieldForm($arrLang, $pDB);
    $oForm = new paloForm($smarty,$arrFormAddGroupe);
    $_DATA = $_POST;

    $where = " where free = '0' and groupe = ''";
    $Rooms_groupe = $pAddGroupe->getAddGroupe($where);

    foreach($Rooms_groupe as $k => $v)
    {
    	$arrRoom[$k] = $v['room_name'];
    	if ($_DATA['rooms'][$k] == $k) 
             {
                     $room_name = $v['room_name'];
              	$arrValores['groupe'] = "'".$_DATA['name']."'";
                     $where = "room_name = '".$room_name."'";
              	$groupe_save = $pAddGroupe->UpDateQuery('rooms',$arrValores,$where);
             }
    }

    if(!$oForm->validateForm($_POST)){
        // Validation basic, not empty and VALIDATION_TYPE 
        $smarty->assign("mb_title", $arrLang["Validation Error"]);
        $arrErrores = $oForm->arrErroresValidacion;
        $strErrorMsg = "<b>{$arrLang['The following fields contain errors']}:</b><br/>";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v)
                $strErrorMsg .= "$k, ";
        }
        $smarty->assign("mb_message", $strErrorMsg);

        $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLang["Add Groupe"], $_DATA);
        $content = viewFormAddGroupe($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
    }
    else{
        //NO ERROR, HERE IMPLEMENTATION OF SAVE

        $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLang["Add Groupe"], $_DATA);
        $content = viewFormAddGroupe($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
    }
    return $content;
}

function createFieldForm($arrLang, &$pDB)
{
    $pAddGroupe = new paloSantoGroupeList($pDB);
    $where = " where free = '0' and groupe = ''";
    $Rooms_groupe = $pAddGroupe->getAddGroupe($where);

    if (isset($Rooms_groupe)) {
    foreach($Rooms_groupe as $k => $v)
    	$arrRoom[$k] = $v['room_name'];
    }
    if (isset( $arrRoom)){
    	$arrOptions = $arrRoom;
    }
	else
    {
    	$arrOptions = array( '1' => 'Empty' );
    }
    $arrFields = array(
            "name"   => array(      "LABEL"                  => $arrLang["Name"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "rooms"   => array(      "LABEL"                  => $arrLang["Rooms"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrOptions,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "si",
						  "MULTIPLE"               => true,
                                            ),

            );
    return $arrFields;
}

function reportGroupeList($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pGroupeList = new paloSantoGroupeList($pDB);
    $filter_field = getParameter("filter_field");
    $filter_value = getParameter("filter_value");
    $action = getParameter("nav");
    $start  = getParameter("start");
    $as_csv = getParameter("exportcsv");

    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("DELETE", $arrLang["Delete"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("ADD", $arrLang["Add"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("IMG", "images/list.png");

    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    $totalGroupeList = $pGroupeList->getNumGroupeList('rooms', $filter_field, $filter_value);

    $limit  = 20;
    $total  = $totalGroupeList;
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $oGrid->enableExport();   // enable csv export.
    $oGrid->pagingShow(true); // show paging section.

    $oGrid->calculatePagination($action,$start);
    $offset = $oGrid->getOffsetValue();
    $end    = $oGrid->getEnd();
    $url    = "?menu=$module_name&filter_field=$filter_field&filter_value=$filter_value";

    $arrData = null;
    $where = "where free= '0' and groupe != ''";
      if(isset($filter_field) & $filter_field !="")
         $where = "where $filter_field like '$filter_value%' and free = '0' and groupe != ''";
    
    $arrResult = $pGroupeList->getGroupeList('rooms', $limit, $offset, $where);
    
    if(is_array($arrResult) && $total>0){
        foreach($arrResult as $key => $value){ 
	    $arrTmp[0] = $value['groupe'];
	    $arrTmp[1] = $value['room_name'];
           $where     = "where room_id = '".$value['id']."'";
	    $arrRegist = $pGroupeList->getGroupeList('register', '1', '0', $where);
           $Register  = $arrRegist['0'];
           $where     = "where id = '".$Register['guest_id']."'";
           $arrGuest  = $pGroupeList->getGroupeList('guest', '1', '0', $where );
           $Guest     = $arrGuest['0'];
	    $arrTmp[2] = $Guest['first_name'];
           $arrData[] = $arrTmp;
        }
    }

    $arrGrid = array("title"    => $arrLang["Groupe List"],
                        "icon"     => "images/list.png",
                        "width"    => "99%",
                        "start"    => ($total==0) ? 0 : $offset + 1,
                        "end"      => $end,
                        "total"    => $total,
                        "url"      => $url,
                        "columns"  => array(
			0 => array("name"      => $arrLang["Name"],
                                   "property1" => ""),
			1 => array("name"      => $arrLang["Room"],
                                   "property1" => ""),
			2 => array("name"      => $arrLang["guest"],
                                   "property1" => ""),
                                        )
                    );

    //begin section filter
    $arrFormFilterGroupeList = createFieldFilter($arrLang);
    $oFilterForm = new paloForm($smarty, $arrFormFilterGroupeList);
    $smarty->assign("SHOW", $arrLang["Show"]);

    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl","",$_POST);
    //end section filter

    if($as_csv == 'yes'){
        $name_csv = "GroupeList_".date("d-M-Y").".csv";
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
	    "groupe" => $arrLang["Name"],
	    "room_name" => $arrLang["Room"],
	    //"guest" => $arrLang["guest"],
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