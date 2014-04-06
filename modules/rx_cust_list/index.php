<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.2.0-30                                               |
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
  $Id: index.php,v 1.1 2012-02-22 04:02:36 Franck Danard franckd@agmp.org Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoCustomerList.class.php";

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
            $content = saveNewEditCustomerList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case "save_edit":
            $content = viewFormEditCustomerList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case "view":
            $content = reportCustomerList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case "delete":
            $content = deleteCustomerList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        default:
            $content = reportCustomerList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
    }
    return $content;
}

function reportCustomerList($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $pCustomerList = new paloSantoCustomerList($pDB);
    $filter_field = getParameter("filter_field");
    $filter_value = getParameter("filter_value");
    $_DATA = $_POST;

    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    $oGrid->setTitle(_tr("Customer List"));
    $oGrid->setIcon("/modules/$module_name/images/icone.png");
    $oGrid->pagingShow(true); // show paging section.

    $oGrid->enableExport(true);   // enable export.
    $oGrid->setNameFile_Export(_tr("Customer List"));
    $oGrid->customAction("save_edit", _tr("Edit"));  
    $oGrid->deleteList(_tr("Are you sure you want to delete the selected items?"),"delete", _tr("Delete")); 

    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("DELETE", _tr("Delete"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("icon", "/modules/$module_name/images/icone.png");

    $url = array(
        "menu"         =>  $module_name,
        "filter_field" =>  $filter_field,
        "filter_value" =>  $filter_value);
    $oGrid->setURL($url);

    $arrColumns = array(_tr("Select"),_tr("Last Name"),_tr("First Name"),_tr("Address"),_tr("CP"),_tr("City"),_tr("Phone"),_tr("Mobile"),_tr("Mail"),_tr("Fax"),_tr("NIF"),_tr("Off_Doc"),);
    $oGrid->setColumns($arrColumns);

    $total   = $pCustomerList->getNumCustomerList($filter_field, $filter_value);

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

    $arrResult =$pCustomerList->getCustomerList($limit, $offset, $filter_field, $filter_value);

    if(is_array($arrResult) && $total>0){
        foreach($arrResult as $key => $value){ 
	    $id 	 = $value['id'];
	    $arrTmp[0] = "<input type='checkbox' name='guest_id[".$key."]' value='$id'>";		
	    $arrTmp[1] = $value['last_name'];
	    $arrTmp[2] = $value['first_name'];
	    $arrTmp[3] = $value['address'];
	    $arrTmp[4] = $value['cp'];
	    $arrTmp[5] = $value['city'];
	    $arrTmp[6] = $value['phone'];
	    $arrTmp[7] = $value['mobile'];
	    $arrTmp[8] = $value['mail'];
	    $arrTmp[9] = $value['fax'];
	    $arrTmp[10]= $value['NIF'];
	    $arrTmp[11]= $value['Off_Doc'];
           $arrData[] = $arrTmp;
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

function viewFormEditCustomerList($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $pCustomerList = new paloSantoCustomerList($pDB);
    $arrFormEditAction = createFieldForm();
    $oForm = new paloForm($smarty,$arrFormEditAction);

    //begin, Form data persistence to errors and other events.
    $_DATA  = $_POST;

    foreach($_DATA['guest_id'] as $key => $value_guest)

    $arrResult =$pCustomerList->getCustomerList('1','0', 'id', $value_guest);

    $_DATA['last_name'] 	= $arrResult[0]['last_name'];
    $_DATA['first_name'] 	= $arrResult[0]['first_name'];
    $_DATA['address'] 	= $arrResult[0]['address'];
    $_DATA['cp'] 		= $arrResult[0]['cp'];
    $_DATA['city'] 		= $arrResult[0]['city'];
    $_DATA['phone'] 		= $arrResult[0]['phone'];
    $_DATA['mobile'] 	= $arrResult[0]['mobile'];
    $_DATA['mail'] 		= $arrResult[0]['mail'];
    $_DATA['fax'] 		= $arrResult[0]['fax'];
    $_DATA['NIF'] 		= $arrResult[0]['NIF'];
    $_DATA['Off_Doc'] 	= $arrResult[0]['Off_Doc'];

    $action = getParameter("action");
    $id     = getParameter("id");
    $smarty->assign("ID", $id); //persistence id with input hidden in tpl
    $smarty->assign("icon", "/modules/$module_name/images/icone.png");

    if($action=="view")
        $oForm->setViewMode();
    else if($action=="view_edit" || getParameter("save_edit"))
        $oForm->setEditMode();
    //end, Form data persistence to errors and other events.

    if($action=="view" || $action=="view_edit"){ // the action is to view or view_edit.
        $dataCustomer = $pCustomerList->getCustomerListById($id);
        if(is_array($dataCustomer) & count($dataCustomer)>0)
            $_DATA = $dataCustomer;
        else{
            $smarty->assign("mb_title", _tr("Error get Data"));
            $smarty->assign("mb_message", $pCustomerList->errMsg);
        }
    }

    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("APPLY", _tr("Apply"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("SELECT",$value_guest);
    $smarty->assign("icon", "/modules/$module_name/images/icone.png");

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",_tr("Customer List"), $_DATA);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name&guest_id=$value_guest'>".$htmlForm."</form>";

    return $content;
}

function deleteCustomerList($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $pCustomerList = new paloSantoCustomerList($pDB);
    $arrFormEditAction = createFieldForm();
    $oForm = new paloForm($smarty,$arrFormEditAction);
    $_DATA  = $_POST;
    $guest_id = getParameter("guest_id");

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

        //genQuery($query, $param = NULL)
        foreach($guest_id as $key => $value)
        	$deleteCustomer = $pCustomerList->deleteGuest($value);
        $_POST 		  = null;

        $content = reportCustomerList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
    }
    return $content;
}

function saveNewEditCustomerList($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $pCustomerList = new paloSantoCustomerList($pDB);
    $arrFormEditAction = createFieldForm();
    $oForm = new paloForm($smarty,$arrFormEditAction);
    $_DATA  = $_POST;
    $_DATA['guest_id'] = getParameter("guest_id");

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

	 $OldData['id']		= "'".$_DATA['guest_id']."'";
        $arrValores['last_name'] 	= "'".$_DATA['last_name']."'";
        $arrValores['first_name'] 	= "'".$_DATA['first_name']."'";
        $arrValores['address']   	= "'".$_DATA['address']."'";
        $arrValores['cp']   	= "'".$_DATA['cp']."'";
        $arrValores['city']   	= "'".$_DATA['city']."'";
        $arrValores['phone']   	= "'".$_DATA['phone']."'";
        $arrValores['mobile']   	= "'".$_DATA['mobile']."'";
        $arrValores['mail']   	= "'".$_DATA['mail']."'";
        $arrValores['fax']   	= "'".$_DATA['fax']."'";
        $arrValores['NIF']   	= "'".$_DATA['NIF']."'";
        $arrValores['Off_Doc']   	= "'".$_DATA['Off_Doc']."'";

        //genQuery($query, $param = NULL)

        $save_rac = $pCustomerList->updateGuest("guest", $arrValores, $OldData);
        $_POST = null;

        $content = reportCustomerList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
    }
    return $content;
}

function createFieldForm()
{
    $arrFields = array(
            "last_name"   => array(        "LABEL"                  => _tr("Last Name"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "last_name"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "first_name"   => array(         "LABEL"                  => _tr("First Name"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "first_name"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "address"   => array(           "LABEL"                  => _tr("Address"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXTAREA",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "address"),
                                            "VALIDATION_TYPE"        => "text",
                                            "EDITABLE"               => "si",
                                            "COLS"                   => "50",
                                            "ROWS"                   => "4",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "cp"   => array(      "LABEL"                  => _tr("CP"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "cp"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "city"   => array(      "LABEL"                  => _tr("City"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "city"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "phone"   => array(      "LABEL"                  => _tr("Phone"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "phone"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "mobile"   => array(      "LABEL"                  => _tr("Mobile"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "mobile"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "mail"   => array(      "LABEL"                  => _tr("Mail"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "mail"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "fax"   => array(      "LABEL"                  => _tr("Fax"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "fax"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "NIF"   => array(      "LABEL"                  => _tr("NIF"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "NIF"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "Off_Doc"   => array(      "LABEL"                  => _tr("Off_Doc"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "Off_Doc"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            );
    return $arrFields;
}



function createFieldFilter(){
    $arrFilter = array(
	    "last_name" 	=> _tr("Last Name"),
	    "first_name" 	=> _tr("First Name"),
	    "address" 	=> _tr("Address"),
	    "cp" 		=> _tr("CP"),
	    "city" 		=> _tr("City"),
	    "phone" 		=> _tr("Phone"),
	    "mobile" 		=> _tr("Mobile"),
	    "mail" 		=> _tr("Mail"),
	    "fax" 		=> _tr("Fax"),
	    "NIF" 		=> _tr("NIF"),
	    "Off_Doc" 	=> _tr("Off_Doc"),
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