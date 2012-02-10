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
  $Id: index.php,v 1.1 2010-04-03 04:04:47 Franck Danard franckd@agmp.org Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoModels.class.php";

    //include file language agree to elastix configuration
    //if file language not exists, then include language by default (en)
    $lang=get_language();
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $lang_file="modules/$module_name/lang/$lang.lang";
    if (file_exists("$base_dir/$lang_file")) include_once "$lang_file";
    else include_once "modules/$module_name/lang/en.lang";

    //global variables
    global $arrConf;
    global $OldData;
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
        case "add" :
            $content = viewFormAddModel($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "save_new":
            $content = saveNewModel($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "save_edit":
            $content = saveEditModel($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "edit" :
            $content = viewFormEditModel($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "delete" :
            $content = Delete_Models($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        default:
            $content = reportModels($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
    }
    return $content;
}

function Delete_Models($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pModels = new paloSantoModels($pDB);
    $filter_field = getParameter("filter_field");
    $filter_value = getParameter("filter_value");
    $_DATA = $_POST;
    
    foreach($_DATA['model'] as $key => $value)
    	$delete_model = $pModels->DeletModels("room_model", $value);
    $content = reportModels($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);

    return $content;    
}

function saveNewModel($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pAddModel = new paloSantoModels($pDB);
    $arrFormAddModel = createFieldForm($arrLang, $pDB);
    $oForm = new paloForm($smarty,$arrFormAddModel);
    $_DATA = $_POST;

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
        $content = viewFormAddModel($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
    }
    else{
        //NO ERROR, HERE IMPLEMENTATION OF SAVE

	 // Checking VAT idx -> VAT
        //------------------------------

        $VATOptions = $pAddModel->getVatModel();
	 $n = 1;
	 foreach($VATOptions as $v => $value){
	 	if ($_DATA['vat'] == $n)
	 		$_DATA['vat'] = $value;
		$n++;
	 }
	
        $arrValores['room_model'] 	 = "'".$_DATA['model']."'";
        $arrValores['room_price']   = "'".$_DATA['price']."'";
        $arrValores['room_guest']   = "'".$_DATA['guest']."'";
        $arrValores['room_vat']     = "'".$_DATA['vat']."'";

        //genQuery($query, $param = NULL)

        $save_model = $pAddModel->insertModel("models", $arrValores);
        $_POST = null;
	
        $content = viewFormAddModel($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);

    }
    return $content;
}

function saveEditModel($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pAddModel 	= new paloSantoModels($pDB);
    $arrFormAddModel = createFieldForm($arrLang, $pDB);
    $oForm 		= new paloForm($smarty,$arrFormAddModel);
    $_DATA 		= $_POST;
    $OldData['room_model'] = "'".getParameter("old_model")."'";
    $OldData['room_price'] = "'".getParameter("old_price")."'";
    $OldData['room_guest'] = "'".getParameter("old_guest")."'";
    $OldData['room_vat']   = "'".getParameter("old_vat")."'";

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
        $content = viewFormAddModel($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
    }
    else{
        //NO ERROR, HERE IMPLEMENTATION OF SAVE

	 // Checking VAT idx -> VAT
        //------------------------------

        $VATOptions = $pAddModel->getVatModel();
	 $n = 1;
	 foreach($VATOptions as $v => $value){
	 	if ($_DATA['vat'] == $n)
	 		$_DATA['vat'] = $value;
		$n++;
	 }
	
        $arrValores['room_model'] 	 = "'".$_DATA['model']."'";
        $arrValores['room_price']   = "'".$_DATA['price']."'";
        $arrValores['room_guest']   = "'".$_DATA['guest']."'";
        $arrValores['room_vat']     = "'".$_DATA['vat']."'";

        //genQuery($query, $param = NULL)

        $save_model = $pAddModel->updateModel("models", $arrValores, $OldData);
        $_POST = null;
	
        $content = reportModels($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);

    }
    return $content;
}
function viewFormEditModel($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pEditModel = new paloSantoModels($pDB);
    $arrFormAddModel = createFieldForm($arrLang, $pDB);
    $oForm = new paloForm($smarty,$arrFormAddModel);

    //begin, Form data persistence to errors and other events.
    $_DATA  = $_POST;
    $action = "edit";
    $id     = getParameter("id");
    $model  = getParameter("model");

    $smarty->assign("ID", $id); //persistence id with input hidden in tpl
    $smarty->assign("SAVE","Save");
    $smarty->assign("icon", "/modules/$module_name/images/icone.png");

    if($action=="view")
        $oForm->setViewMode();
    else if($action=="edit" || getParameter("save_edit"))
        $oForm->setEditMode();
    //end, Form data persistence to errors and other events.
    
    $i = 0;
    foreach($model as $model_K => $model_Value)
	{
		$id_model[$i] = $model_Value;
		$i++;
	}

    if($action=="edit" || $action=="view_edit"){ // the action is to edit or view_edit.
	 $model_needed   ="'".$id_model[0]."'";
        $dataEditModel = $pEditModel->getModelsById($model_needed);
        if(is_array($dataEditModel) & count($dataEditModel)>0)
            $_DATA = $dataEditModel;
        else{
            $smarty->assign("mb_title", $arrLang["Error get Data"]);
            $smarty->assign("mb_message", $pEditModel->errMsg);
        }
	$old_model	= $_DATA['model'] = $_DATA['room_model'];
	$old_price	= $_DATA['price'] = $_DATA['room_price'];
	$old_guest	= $_DATA['guest'] = $_DATA['room_guest'];
	$old_vat	= $_DATA['vat']   = $_DATA['room_vat'];
    }

    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLang["Edit"], $_DATA);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name&old_model=$old_model&old_price=$old_price&old_guest=$old_guest&old_vat=$old_vat'>".$htmlForm."</form>";

    return $content;
}

function viewFormAddModel($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pAddModel = new paloSantoModels($pDB);
    $arrFormAddModel = createFieldForm($arrLang, $pDB);
    $oForm = new paloForm($smarty,$arrFormAddModel);

    //begin, Form data persistence to errors and other events.
    $_DATA  = $_POST;
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
        $dataAddModel = $pAddModel->getAddModelById($id);
        if(is_array($dataAddModel) & count($dataAddModel)>0)
            $_DATA = $dataAddModel;
        else{
            $smarty->assign("mb_title", $arrLang["Error get Data"]);
            $smarty->assign("mb_message", $pAddModel->errMsg);
        }
    }

    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLang["Add Model"], $_DATA);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function createFieldForm($arrLang,&$pDB)
{
    $VAT_Model  = new paloSantoModels($pDB);
    $VATOptions = $VAT_Model->getVatModel();
    $arrOptions =array('1' => $VATOptions['vat_1'], '2' => $VATOptions['vat_2']);

    $arrFields = array(
            "model"   => array(      "LABEL"                  => $arrLang["Model"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "price"   => array(      "LABEL"                  => $arrLang["Price"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "guest"   => array(      "LABEL"                  => $arrLang["Guest"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "vat"   => array(      "LABEL"                  => $arrLang["VAT"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrOptions,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),

            );
    return $arrFields;
}


function reportModels($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pModels = new paloSantoModels($pDB);
    $filter_field = getParameter("filter_field");
    $filter_value = getParameter("filter_value");
    $action = getParameter("nav");
    $start  = getParameter("start");
    $as_csv = getParameter("exportcsv");

    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    $totalModels = $pModels->getNumModels($filter_field, $filter_value);

    $limit  = 20;
    $total  = $totalModels;
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    //$oGrid->enableExport();   // enable csv export.
    $oGrid->pagingShow(true); // show paging section.

    $oGrid->calculatePagination($action,$start);
    $offset = $oGrid->getOffsetValue();
    $end    = $oGrid->getEnd();
    $url    = "?menu=$module_name&filter_field=$filter_field&filter_value=$filter_value";

    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("DELETE", $arrLang["Delete"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("ADD", $arrLang["Add"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("IMG", "/modules/$module_name/images/icone.png");

    $arrData = null;
    $arrResult =$pModels->getModels($limit, $offset, $filter_field, $filter_value);

    if(is_array($arrResult) && $total>0){
        foreach($arrResult as $key => $value){ 
           $value_check = $value['room_model'];
	    $arrTmp[0]   = "<input type='checkbox' name='model[".$key."]' value='$value_check'>";
	    $arrTmp[1]   = $value['room_model'];
	    $arrTmp[2]   = $value['room_price'];
	    $arrTmp[3]   = $value['room_guest'];
	    $arrTmp[4]   = $value['room_vat']." %";
           $arrData[]   = $arrTmp;
        }
    }


    $arrGrid = array("title"    => $arrLang["Models"],
                        "icon"     => "/modules/$module_name/images/icone.png",
                        "width"    => "99%",
                        "start"    => ($total==0) ? 0 : $offset + 1,
                        "end"      => $end,
                        "total"    => $total,
                        "url"      => $url,
                        "columns"  => array(
			0 => array("name"      => $arrLang["Select"],
                                   "property1" => ""),
			1 => array("name"      => $arrLang["Models"],
                                   "property1" => ""),
			2 => array("name"      => $arrLang["Prices"],
                                   "property1" => ""),
			3 => array("name"      => $arrLang["Guest"],
                                   "property1" => ""),
			4 => array("name"      => $arrLang["V.A.T"],
                                   "property1" => ""),
                                        )
                    );


    //begin section filter
    $arrFormFilterModels = createFieldFilter($arrLang);
    $oFilterForm = new paloForm($smarty, $arrFormFilterModels);
    $smarty->assign("SHOW", $arrLang["Show"]);

    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl","",$_POST);
    //end section filter

    if($as_csv == 'yes'){
        $name_csv = "Models_".date("d-M-Y").".csv";
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
	    "room_model" => $arrLang["Models"],
	    "room_price" => $arrLang["Prices"],
	    "room_guest" => $arrLang["Guest"],
	    "room_vat"   => $arrLang["V.A.T"],
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
    if(getParameter("add")) //Get parameter by POST (submit)
        return "add";
    else if(getParameter("edit"))
        return "edit";
    else if(getParameter("save_new"))
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