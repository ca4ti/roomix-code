<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.2.0-27                                               |
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
  $Id: index.php,v 1.1 2012-02-05 06:02:01 Danard Franck franckd@agmp.org Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoRemote_Action.class.php";

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

    //actions
    $action = getAction();
    $content = "";

    switch($action){
        case "save_new":
            $content = saveNewEditAction($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case "save_edit":
            $content = viewFormEditAction($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case "view":
            $content = reportRemote_Action($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        default:
            $content = reportRemote_Action($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
    }
    return $content;
}


function reportRemote_Action($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{

    $pRemote_Action = new paloSantoRemote_Action($pDB);

    $filter_field = getParameter("filter_field");
    $filter_value = getParameter("filter_value");

    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("APPLY", _tr("Apply"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("icon", "/modules/$module_name/images/icone.png");

    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    $oGrid->setTitle(_tr("Remote_Action"));
    $oGrid->setIcon(_tr("/modules/$module_name/images/icone.png"));
    $oGrid->pagingShow(true); // show paging section.

    $oGrid->enableExport(true);   // enable export.
    $oGrid->setNameFile_Export(_tr("Remote_Action"));

    $url = array(
        "menu"         =>  $module_name,
        "filter_field" =>  $filter_field,
        "filter_value" =>  $filter_value);
    $oGrid->setURL($url);

    $arrColumns = array(_tr("select"),_tr("Rooms"),_tr("Action CI"),_tr("Action CO"),);
    $oGrid->setColumns($arrColumns);

    $total   = $pRemote_Action->getNumRemote_Action($filter_field, $filter_value);
    $arrData = null;
    if($oGrid->isExportAction()){
        $limit  = $total; // max number of rows.
        $offset = 0;      // since the start.
    }
    else{
        $limit  = 20;
        $oGrid->setLimit($limit);
        $oGrid->setTotal($total);
        $offset = $oGrid->calculateOffset();
    }

    $arrResult =$pRemote_Action->getRemote_Action($limit, $offset, $filter_field, $filter_value);


    if(is_array($arrResult) && $total>0){
        foreach($arrResult as $key => $value){ 
	    $value_check = $value['room_name'];
	    $arrTmp[0]   = "<input type='checkbox' name='room[".$key."]' value='$value_check'>";
	    $arrTmp[1]   = $value['room_name'];
	    $arrTmp[2]   = $value['RACI'];
	    $arrTmp[3]   = $value['RACO'];
           $arrData[]   = $arrTmp;
        }
    }
    $oGrid->setData($arrData);

    //begin section filter
    $oFilterForm = new paloForm($smarty, createFieldFilter());
    $smarty->assign("SHOW", _tr("Show"));
    $htmlFilter  = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl","",$_POST);
    //end section filter

    $oGrid->showFilter(trim($htmlFilter));
    $content = $oGrid->fetchGrid();
    //end grid parameters

    return $content;
}

function viewFormEditAction($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $pRemote_Action = new paloSantoRemote_Action($pDB);
    $arrFormEditAction = createFieldForm();
    $oForm = new paloForm($smarty,$arrFormEditAction);

    //begin, Form data persistence to errors and other events.
    $_DATA  = $_POST;

    foreach($_DATA['room'] as $key => $value_room)

    $arrResult =$pRemote_Action->getRemote_Action('1','0', 'room_name', $value_room);

    $_DATA['action_ci'] = $arrResult[0]['RACI'];
    $_DATA['action_co'] = $arrResult[0]['RACO'];

    $action = getParameter("action");
    $id     = getParameter("id");
    $smarty->assign("ID", $id); //persistence id with input hidden in tpl

    if($action=="view")
        $oForm->setViewMode();
    else if($action=="view_edit" || getParameter("save_edit"))
        $oForm->setEditMode();
    //end, Form data persistence to errors and other events.

    if($action=="view" || $action=="view_edit"){ // the action is to view or view_edit.
        $dataEditAction = $pRemote_Action->getEditActionById($id);
        if(is_array($dataEditAction) & count($dataEditAction)>0)
            $_DATA = $dataEditAction;
        else{
            $smarty->assign("mb_title", _tr("Error get Data"));
            $smarty->assign("mb_message", $pRemote_Action->errMsg);
        }
    }

    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("APPLY", _tr("Apply"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("SELECT",$value_room);
    $smarty->assign("icon", "/modules/$module_name/images/icone.png");

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",_tr("Edit Action"), $_DATA);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name&room=$value_room'>".$htmlForm."</form>";

    return $content;
}

function saveNewEditAction($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $pRemote_Action = new paloSantoRemote_Action($pDB);
    $arrFormEditAction = createFieldForm();
    $oForm = new paloForm($smarty,$arrFormEditAction);
    $_DATA  = $_POST;
    $_DATA['room'] = getParameter("room");

    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("APPLY", _tr("Apply"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("icon", "/modules/$module_name/images/icone.png");

    if(!$oForm->validateForm($_POST)){
        // Validation basic, not empty and VALIDATION_TYPE 
        $smarty->assign("mb_title", _tr("Validation Error"));
        $arrErrores = $oForm->arrErroresValidacion;
        $strErrorMsg = "<b>"._tr("The following fields contain errors").":</b><br/>";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v)
                $strErrorMsg .= "$k, ";
        }
        $smarty->assign("mb_message", $strErrorMsg);
        $content = viewFormEditAction($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
    }
    else{
        //NO ERROR, HERE IMPLEMENTATION OF SAVE

	 $OldData['room_name']	= "'".$_DATA['room']."'";
        $arrValores['RACI'] 	= "'".$_DATA['action_ci']."'";
        $arrValores['RACO']   	= "'".$_DATA['action_co']."'";

        //genQuery($query, $param = NULL)

        $save_rac = $pRemote_Action->updateRoom("rooms", $arrValores, $OldData);
        $_POST = null;

        $content = reportRemote_Action($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
    }
    return $content;
}

function createFieldForm()
{
    $arrOptions = array('val1' => 'Value 1', 'val2' => 'Value 2', 'val3' => 'Value 3');

    $arrFields = array(
            "room"   => array(      "LABEL"                  => _tr("Room"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "action_ci"   => array(      "LABEL"                  => _tr("Action CI"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "action_co"   => array(      "LABEL"                  => _tr("Action CO"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),

            );
    return $arrFields;
}


function createFieldFilter(){
    $arrFilter = array(
	    "room_name" => _tr("Rooms"),
	    "RACI" => _tr("Action CI"),
	    "RACO" => _tr("Action CO"),
                    );

    $arrFormElements = array(
            "filter_field" => array("LABEL"                  => _tr("Search"),
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
    else if(getParameter("view")) 
        return "report";
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