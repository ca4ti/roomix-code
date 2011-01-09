<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  CodificaciÃ³n: UTF-8
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
  $Id: index.php,v 1.1 2010-03-28 06:03:36 Franck Danard franckd@agmp.org Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoGeneral.class.php";

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
        case "save_new":
            $content = saveNewGeneral($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        default: // view_form
            $content = viewFormGeneral($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
    }
    return $content;
}

function viewFormGeneral($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pGeneral = new paloSantoGeneral($pDB);

    $arrFormGeneral = createFieldForm($arrLang, $pDB);
    $oForm = new paloForm($smarty,$arrFormGeneral);

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
        $dataGeneral = $pGeneral->getGeneralById($id);
        if(is_array($dataGeneral) & count($dataGeneral)>0)
            $_DATA = $dataGeneral;
        else{
            $smarty->assign("mb_title", $arrLang["Error get Data"]);
            $smarty->assign("mb_message", $pGeneral->errMsg);
        }
    }

    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("IMG", "images/list.png");
    $smarty->caching = 0;

    $get_config = $pGeneral->getGeneral(); 

    $_DATA["operating_mode"] = '1';
    if ($get_config['o_m'] == "Hotel")
	$_DATA["operating_mode"] = '0';
 
    $_DATA["locked_when_check_out"] = 'off';
    if ($get_config['locked'] == "1")
	$_DATA["locked_when_check_out"] = 'on';

    $_DATA["calling_between_rooms"] = 'off';
    if ($get_config['cbr'] == "1")
	$_DATA["calling_between_rooms"] = 'on';

    $_DATA["rmbc"] = 'off';
    if ($get_config['rmbc'] == "1")
	$_DATA["rmbc"] = 'on';

    $_DATA["company"]   = $get_config['company'];
    $_DATA["clean"]     = $get_config['clean'];
    $_DATA["minibar"]   = $get_config['minibar'];
    $_DATA["reception"] = $get_config['reception'];
    $_DATA["mail"]      = $get_config['mail'];
    $_DATA["vat_1"]     = $get_config['vat_1'];
    $_DATA["vat_2"]     = $get_config['vat_2'];
    
    $smarty->assign("LOGO", $get_config['logo']);

        $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLang["General"], $_DATA);
        $content = "<form  method='POST' enctype='multipart/form-data' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function stro_replace($search, $replace, $subject)
{
    return strtr( $subject, array_combine($search, $replace) );
}

function saveNewGeneral($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pGeneral = new paloSantoGeneral($pDB);
    $arrFormGeneral = createFieldForm($arrLang, $pDB);
    $oForm = new paloForm($smarty,$arrFormGeneral);
    $_DATA  = $_POST;

    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("IMG", "images/list.png");
    $smarty->caching = 0;

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
        $content = viewFormGeneral($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
    }
    else{
        $pSG = new paloSantoGeneral($pDB);

	 $search = array('é', 'è', 'à', 'ê', 'ô','\n');
	 $replace = array('&eacute;', '&egrave;', '&agrave;', '&ecirc;', '&ocirc;','<br>');

	 $o_m="Hospital";
        if ($_DATA["operating_mode"] == "0") 
	 	$o_m="Hotel";

	 $locked="0";
        if ($_DATA["locked_when_check_out"] == "on")
	 	$locked="1";

	 $cbr="0";
        if ($_DATA["calling_between_rooms"] == "on")
	 	$cbr="1";

	 $rmbc="0";
        if ($_DATA["rmbc"] == "on")
	 	$rmbc="1";

        $reception = $_DATA['reception'];
        $clean     = $_DATA['clean'];
        $minibar   = $_DATA['minibar'];
        $mail      = $_DATA['mail'];
        $vat_1     = $_DATA['vat_1'];
        $vat_2     = $_DATA['vat_2'];
        //$company = nl2br(stro_replace ($search, $replace, $_DATA['company']));
        $company   = $_DATA['company'];
        //$route_archive = $_DATA['logo'];
        
	 if(is_uploaded_file($_FILES['file_record']['tmp_name'])) {
    		$route_archive = "modules/$module_name/images/".$_FILES['file_record']['name'];
    		copy($_FILES['file_record']['tmp_name'], $route_archive);
	 }

	 // Update the file: extensions_roomx.conf and reloading the dialplan
        //---------------------------------------------------------------------
        $get_config = $pGeneral->getGeneral(); 
	 if ($get_config['clean'] != $clean){
        	$cmd="sed -i 's|".$get_config['clean'].",|".$clean.",|' /etc/asterisk/extensions_roomx.conf";
        	exec($cmd);
	 }
	 if ($get_config['minibar'] != $minibar){
        	$cmd="sed -i 's|".$get_config['minibar'].",|".$minibar.",|' /etc/asterisk/extensions_roomx.conf";
       	 exec($cmd);
	 }
	 if ($get_config['reception'] != $reception){
        	$cmd="sed -i 's|".$get_config['reception'].",|".$reception.",|' /etc/asterisk/extensions_roomx.conf";
        	exec($cmd);
        }
	 exec("asterisk -rx 'dialplan reload'");
	
	 // Update config into the database.
        //----------------------------------

	 $arrValores['o_m']       = "'".$o_m."'"; 
	 $arrValores['locked']    = "'".$locked."'";
	 $arrValores['cbr']       = "'".$cbr."'";
	 $arrValores['clean']     = "'".$clean."'";
	 $arrValores['rmbc']      = "'".$rmbc."'";
	 $arrValores['minibar']   = "'".$minibar."'";
	 $arrValores['reception'] = "'".$reception."'";
	 $arrValores['company']   = "'".$company."'";
	 $arrValores['mail']      = "'".$mail."'";
	 $arrValores['vat_1']     = "'".$vat_1."'";
	 $arrValores['vat_2']     = "'".$vat_2."'";
	 if (isset($route_archive))
	 	$arrValores['logo']= "'".$route_archive."'";
        $save_general = $pSG->updateGeneral('config', $arrValores);

        $get_config = $pGeneral->getGeneral(); 
        $smarty->assign("LOGO", $get_config['logo']);
        $content = $save_general;
    }
    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLang["General"], $_DATA);
    $content = "<form  method='POST' enctype='multipart/form-data' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
    return $content;
}

function createFieldForm($arrLang, &$pDB)
{
    $_DATA = $_POST;
    $pCG = new paloSantoGeneral($pDB);
    $get_config = $pCG->getGeneral(); 
    $arrOptions[0] = "Hotel";
    $arrOptions[1] = "Hospital";

    $arrReception[0] = "100";

    $arrFields = array(
            "operating_mode"   => array(    "LABEL"                  => $arrLang["Operating Mode"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrOptions,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ), 
            "locked_when_check_out"   => array(      "LABEL"         => $arrLang["Locked when Check Out"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "CHECKBOX",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "calling_between_rooms"   => array(      "LABEL"         => $arrLang["Calling between rooms"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "CHECKBOX",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "Logo"                    => array(      "LABEL"         => $arrLang["Logo"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "FILE",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "reception"      => array(      "LABEL"                  => $arrLang["Reception"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:50px"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "si"
						  ),
            "company"          => array(    "LABEL"                  => $arrLang["Company"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXTAREA",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                				  "COLS"                   => "30",
                                            "ROWS"                   => "10",
                                            "EDITABLE"               => "si"
						  ),
            "clean"          => array(      "LABEL"                  => $arrLang["Clean"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:50px"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "si"
						  ),
            "minibar"        => array(      "LABEL"                  => $arrLang["Minibar"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:50px"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "si"
						  ),
            "rmbc"           => array(      "LABEL"                  => $arrLang["Room must be clean"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "CHECKBOX",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "mail"           => array(      "LABEL"                  => $arrLang["Mail"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:250px"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "si"
						  ),
            "vat_1"          => array(      "LABEL"                  => $arrLang["VAT1"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:50px"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "si"
						  ),
            "vat_2"          => array(      "LABEL"                  => $arrLang["VAT2"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:50px"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "si"
						  )
            );
    return $arrFields;
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