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
        case "save_new" :
            $content = saveModels($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        default:
            $content = reportModels($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
    }
    return $content;
}

function saveModels($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pModels = new paloSantoModels($pDB);
    $filter_field = getParameter("filter_field");
    $filter_value = getParameter("filter_value");
    $_DATA = $_POST;

    $value = $_DATA['model'];
    $save_model = $pModels->DeletModels("room_model", $value);
    $content = reportModels($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);

    return $content;
    
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
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("IMG", "images/list.png");

    $arrData = null;
    $arrResult =$pModels->getModels($limit, $offset, $filter_field, $filter_value);

    if(is_array($arrResult) && $total>0){
        foreach($arrResult as $key => $value){ 
           $value_check = $value['room_model'];
	    $arrTmp[0] = "<input type='checkbox' name='model' value='$value_check'>";
	    $arrTmp[1] = $value['room_model'];
	    $arrTmp[2] = $value['room_price'];
	    $arrTmp[3] = $value['room_guest'];
	    $arrTmp[4] = $value['room_vat']." %";
           $arrData[] = $arrTmp;
        }
    }


    $arrGrid = array("title"    => $arrLang["Models"],
                        "icon"     => "images/list.png",
                        "width"    => "99%",
                        "start"    => ($total==0) ? 0 : $offset + 1,
                        "end"      => $end,
                        "total"    => $total,
                        "url"      => $url,
                        "columns"  => array(
			0 => array("name"      => $arrLang["Delete"],
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