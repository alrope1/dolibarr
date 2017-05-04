<?php
/* Copyright (C) 2001-2005	Rodolphe Quiedeville	<rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015	Laurent Destailleur		<eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012	Regis Houssin			<regis.houssin@capnetworks.com>
 * Copyright (C) 2016       Juanjo Menent           <jmenent@2byte.es>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *       \file       htdocs/admin/ihm.php
 *       \brief      Page to setup GUI display options
 */

require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/usergroups.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formadmin.class.php';

$langs->load("admin");
$langs->load("languages");
$langs->load("other");

$langs->load("companies");
$langs->load("products");
$langs->load("members");
$langs->load("projects");
$langs->load("hrm");
$langs->load("agenda");

if (! $user->admin) accessforbidden();

$action = GETPOST('action');


if (! defined("MAIN_MOTD")) define("MAIN_MOTD","");

// List of supported permanent search area
$searchform=array();
/* deprecated
if (empty($conf->use_javascript_ajax))
{
    $searchform=array("MAIN_SEARCHFORM_SOCIETE", "MAIN_SEARCHFORM_CONTACT", "MAIN_SEARCHFORM_PRODUITSERVICE", "MAIN_SEARCHFORM_ADHERENT", "MAIN_SEARCHFORM_PROJECT", "MAIN_SEARCHFORM_EMPLOYEE");
    $searchformconst=array($conf->global->MAIN_SEARCHFORM_SOCIETE,$conf->global->MAIN_SEARCHFORM_CONTACT,$conf->global->MAIN_SEARCHFORM_PRODUITSERVICE,$conf->global->MAIN_SEARCHFORM_ADHERENT,$conf->global->MAIN_SEARCHFORM_PROJECT,$conf->global->MAIN_SEARCHFORM_EMPLOYEE);
    $searchformtitle=array($langs->trans("Companies"), $langs->trans("Contacts"), $langs->trans("ProductsAndServices"), $langs->trans("Members"), $langs->trans("Projects"), $langs->trans("Users"));
    $searchformmodule=array('Module1Name','Module1Name','Module50Name','Module310Name','Module400Name');
}
*/


/*
 * Action
 */

if (GETPOST('cancel'))
{
    $action='';
}

if ($action == 'removebackgroundlogin' && ! empty($conf->global->MAIN_LOGIN_BACKGROUND))
{
    require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

    $logofile=$conf->mycompany->dir_output.'/logos/'.$conf->global->MAIN_LOGIN_BACKGROUND;
    dol_delete_file($logofile);
    dolibarr_del_const($db, "MAIN_LOGIN_BACKGROUND",$conf->entity);
    $mysoc->logo='';

    /*$logosmallfile=$conf->mycompany->dir_output.'/logos/thumbs/'.$mysoc->logo_small;
    dol_delete_file($logosmallfile);
    dolibarr_del_const($db, "MAIN_INFO_SOCIETE_LOGO_SMALL",$conf->entity);
    $mysoc->logo_small='';

    $logominifile=$conf->mycompany->dir_output.'/logos/thumbs/'.$mysoc->logo_mini;
    dol_delete_file($logominifile);
    dolibarr_del_const($db, "MAIN_INFO_SOCIETE_LOGO_MINI",$conf->entity);
    $mysoc->logo_mini='';*/
}

if ($action == 'update')
{
	dolibarr_set_const($db, "MAIN_LANG_DEFAULT",				$_POST["main_lang_default"],'chaine',0,'',$conf->entity);
	dolibarr_set_const($db, "MAIN_MULTILANGS",					$_POST["main_multilangs"],'chaine',0,'',$conf->entity);

	dolibarr_set_const($db, "MAIN_THEME",						$_POST["main_theme"],'chaine',0,'',$conf->entity);

	$val=(implode(',',(colorStringToArray(GETPOST('THEME_ELDY_BACKBODY'),array()))));
	if ($val == '') dolibarr_del_const($db, 'THEME_ELDY_BACKBODY', $conf->entity);
	else dolibarr_set_const($db, 'THEME_ELDY_BACKBODY', implode(',',colorStringToArray(GETPOST('THEME_ELDY_BACKBODY'),array())),'chaine',0,'',$conf->entity);
	
	$val=GETPOST('THEME_TOPMENU_DISABLE_IMAGE');
	if (! $val) dolibarr_del_const($db, 'THEME_TOPMENU_DISABLE_IMAGE', $conf->entity);
    else dolibarr_set_const($db, 'THEME_TOPMENU_DISABLE_IMAGE', GETPOST('THEME_TOPMENU_DISABLE_IMAGE'),'chaine',0,'',$conf->entity);
    
    $val=(implode(',',(colorStringToArray(GETPOST('THEME_ELDY_TOPMENU_BACK1'),array()))));
	if ($val == '') dolibarr_del_const($db, 'THEME_ELDY_TOPMENU_BACK1', $conf->entity);
    else dolibarr_set_const($db, 'THEME_ELDY_TOPMENU_BACK1', implode(',',colorStringToArray(GETPOST('THEME_ELDY_TOPMENU_BACK1'),array())),'chaine',0,'',$conf->entity);

	$val=(implode(',',(colorStringToArray(GETPOST('THEME_ELDY_BACKTITLE1'),array()))));
	if ($val == '') dolibarr_del_const($db, 'THEME_ELDY_BACKTITLE1', $conf->entity);
    else dolibarr_set_const($db, 'THEME_ELDY_BACKTITLE1', implode(',',colorStringToArray(GETPOST('THEME_ELDY_BACKTITLE1'),array())),'chaine',0,'',$conf->entity);

	$val=(implode(',',(colorStringToArray(GETPOST('THEME_ELDY_TEXTTITLENOTAB'),array()))));
	if ($val == '') dolibarr_del_const($db, 'THEME_ELDY_TEXTTITLENOTAB', $conf->entity);
    else dolibarr_set_const($db, 'THEME_ELDY_TEXTTITLENOTAB', implode(',',colorStringToArray(GETPOST('THEME_ELDY_TEXTTITLENOTAB'),array())),'chaine',0,'',$conf->entity);
    
    if (GETPOST('THEME_ELDY_USE_HOVER') == '') dolibarr_set_const($db, "THEME_ELDY_USE_HOVER", '0', 'chaine', 0, '', $conf->entity);    // If empty, we set to '0' ('000000' is for black)
	else dolibarr_set_const($db, "THEME_ELDY_USE_HOVER", $_POST["THEME_ELDY_USE_HOVER"], 'chaine', 0, '', $conf->entity);

	$val=(implode(',',(colorStringToArray(GETPOST('THEME_ELDY_TEXTLINK'),array()))));
	if ($val == '') dolibarr_del_const($db, 'THEME_ELDY_TEXTLINK', $conf->entity);
    else dolibarr_set_const($db, 'THEME_ELDY_TEXTLINK', implode(',',colorStringToArray(GETPOST('THEME_ELDY_TEXTLINK'),array())),'chaine',0,'',$conf->entity);
	
    dolibarr_set_const($db, "MAIN_SIZE_LISTE_LIMIT",			$_POST["main_size_liste_limit"],'chaine',0,'',$conf->entity);
    dolibarr_set_const($db, "MAIN_SIZE_SHORTLIST_LIMIT",		$_POST["main_size_shortliste_limit"],'chaine',0,'',$conf->entity);
    dolibarr_set_const($db, "MAIN_DISABLE_JAVASCRIPT",			$_POST["main_disable_javascript"],'chaine',0,'',$conf->entity);
    dolibarr_set_const($db, "MAIN_BUTTON_HIDE_UNAUTHORIZED",	$_POST["MAIN_BUTTON_HIDE_UNAUTHORIZED"],'chaine',0,'',$conf->entity);
    dolibarr_set_const($db, "MAIN_START_WEEK",					$_POST["MAIN_START_WEEK"],'chaine',0,'',$conf->entity);
    dolibarr_set_const($db, "MAIN_DEFAULT_WORKING_DAYS",		$_POST["MAIN_DEFAULT_WORKING_DAYS"],'chaine',0,'',$conf->entity);
    dolibarr_set_const($db, "MAIN_DEFAULT_WORKING_HOURS",		$_POST["MAIN_DEFAULT_WORKING_HOURS"],'chaine',0,'',$conf->entity);
    dolibarr_set_const($db, "MAIN_SHOW_LOGO",					$_POST["MAIN_SHOW_LOGO"],'chaine',0,'',$conf->entity);
    dolibarr_set_const($db, "MAIN_FIRSTNAME_NAME_POSITION",		$_POST["MAIN_FIRSTNAME_NAME_POSITION"],'chaine',0,'',$conf->entity);
    
	dolibarr_set_const($db, "MAIN_HELPCENTER_DISABLELINK",		$_POST["MAIN_HELPCENTER_DISABLELINK"],'chaine',0,'',0);	// Param for all entities
	dolibarr_set_const($db, "MAIN_MOTD",						dol_htmlcleanlastbr($_POST["main_motd"]),'chaine',0,'',$conf->entity);
	dolibarr_set_const($db, "MAIN_HOME",						dol_htmlcleanlastbr($_POST["main_home"]),'chaine',0,'',$conf->entity);
	dolibarr_set_const($db, "MAIN_HELP_DISABLELINK",			$_POST["MAIN_HELP_DISABLELINK"],'chaine',0,'',0);	    // Param for all entities
	dolibarr_set_const($db, "MAIN_BUGTRACK_ENABLELINK",         $_POST["MAIN_BUGTRACK_ENABLELINK"],'chaine',0,'',$conf->entity);

	$varforimage='imagebackground'; $dirforimage=$conf->mycompany->dir_output.'/logos/';
	if ($_FILES[$varforimage]["tmp_name"])
	{
	    if (preg_match('/([^\\/:]+)$/i',$_FILES[$varforimage]["name"],$reg))
	    {
	        $original_file=$reg[1];
	
	        $isimage=image_format_supported($original_file);
	        if ($isimage >= 0)
	        {
	            dol_syslog("Move file ".$_FILES[$varforimage]["tmp_name"]." to ".$dirforimage.$original_file);
	            if (! is_dir($dirforimage))
	            {
	                dol_mkdir($dirforimage);
	            }
	            $result=dol_move_uploaded_file($_FILES[$varforimage]["tmp_name"],$dirforimage.$original_file,1,0,$_FILES[$varforimage]['error']);
	            if ($result > 0)
	            {
	                dolibarr_set_const($db, "MAIN_LOGIN_BACKGROUND",$original_file,'chaine',0,'',$conf->entity);
	
	                // Create thumbs of logo (Note that PDF use original file and not thumbs)
	                /*
	                if ($isimage > 0)
	                {
	                    // Create thumbs
	                    //$object->addThumbs($newfile);    // We can't use addThumbs here yet because we need name of generated thumbs to add them into constants. TODO Check if need such constants. We should be able to retreive value with get...
	
	                    // Create small thumb, Used on logon for example
	                    $imgThumbSmall = vignette($dirforimage.$original_file, $maxwidthsmall, $maxheightsmall, '_small', $quality);
	                    if (image_format_supported($imgThumbSmall) >= 0 && preg_match('/([^\\/:]+)$/i',$imgThumbSmall,$reg))
	                    {
	                        $imgThumbSmall = $reg[1];    // Save only basename
	                        dolibarr_set_const($db, "MAIN_INFO_SOCIETE_LOGO_SMALL",$imgThumbSmall,'chaine',0,'',$conf->entity);
	                    }
	                    else dol_syslog($imgThumbSmall);
	
	                    // Create mini thumb, Used on menu or for setup page for example
	                    $imgThumbMini = vignette($dirforimage.$original_file, $maxwidthmini, $maxheightmini, '_mini', $quality);
	                    if (image_format_supported($imgThumbMini) >= 0 && preg_match('/([^\\/:]+)$/i',$imgThumbMini,$reg))
	                    {
	                        $imgThumbMini = $reg[1];     // Save only basename
	                        dolibarr_set_const($db, "MAIN_INFO_SOCIETE_LOGO_MINI",$imgThumbMini,'chaine',0,'',$conf->entity);
	                    }
	                    else dol_syslog($imgThumbMini);
	                }
	                else dol_syslog("ErrorImageFormatNotSupported",LOG_WARNING);
	                */
	            }
	            else if (preg_match('/^ErrorFileIsInfectedWithAVirus/',$result))
	            {
	                $error++;
	                $langs->load("errors");
	                $tmparray=explode(':',$result);
	                setEventMessages($langs->trans('ErrorFileIsInfectedWithAVirus',$tmparray[1]), null, 'errors');
	            }
	            else
	            {
	                $error++;
	                setEventMessages($langs->trans("ErrorFailedToSaveFile"), null, 'errors');
	            }
	        }
	        else
	        {
	            $error++;
	            $langs->load("errors");
	            setEventMessages($langs->trans("ErrorBadImageFormat"), null, 'errors');
	        }
	    }
	}
	
	
	
	$_SESSION["mainmenu"]="";   // Le gestionnaire de menu a pu changer

	header("Location: ".$_SERVER["PHP_SELF"]."?mainmenu=home&leftmenu=setup");
	exit;
}


/*
 * View
 */

$wikihelp='EN:First_setup|FR:Premiers_param&eacute;trages|ES:Primeras_configuraciones';
llxHeader('',$langs->trans("Setup"),$wikihelp);

$form=new Form($db);
$formother=new FormOther($db);
$formadmin=new FormAdmin($db);

print load_fiche_titre($langs->trans("GUISetup"),'','title_setup');

print $langs->trans("DisplayDesc")."<br>\n";
print "<br>\n";


if ($action == 'edit')	// Edit
{
    //WYSIWYG Editor
    require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

    print '<form enctype="multipart/form-data" method="POST" action="'.$_SERVER["PHP_SELF"].'">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="action" value="update">';

    clearstatcache();
    
    print '<br>';
    print '<table summary="edit" class="noborder" width="100%">';
    print '<tr class="liste_titre"><td>'.$langs->trans("Language").'</td><td></td>';
	print '<td width="20">&nbsp;</td>';
	print '</tr>';

    // Default language
    print '<tr class="oddeven"><td class="titlefield">'.$langs->trans("DefaultLanguage").'</td><td>';
    print $formadmin->select_language($conf->global->MAIN_LANG_DEFAULT, 'main_lang_default', 1, 0, 0, 0, 0, 'minwidth300');
    print '</td>';
	print '<td width="20">&nbsp;</td>';
	print '</tr>';

	// Multilingual GUI
    print '<tr class="oddeven"><td class="titlefield">'.$langs->trans("EnableMultilangInterface").'</td><td>';
    print $form->selectyesno('main_multilangs',$conf->global->MAIN_MULTILANGS,1);
    print '</td>';
	print '<td width="20">&nbsp;</td>';
	print '</tr>';

	print '</table><br>'."\n";

    // Themes and themes options
    show_theme(null,1);
    print '<br>';

    // List of permanent supported search box
    if (! empty($searchform))
    {
        print '<table summary="search" class="noborder" width="100%">';
        print '<tr class="liste_titre"><td class="titlefield">'.$langs->trans("PermanentLeftSearchForm").'</td><td colspan="2">'.$langs->trans("Activated").'</td></tr>';
        foreach ($searchform as $key => $value)
        {
            print '<tr><td class="titlefield">'.$searchformtitle[$key].'</td><td colspan="2">';
            print $form->selectyesno($searchform[$key],$searchformconst[$key],1);
            print '</td></tr>';
        }
        print '</table>';
        print '<br>';
    }
    
    // Other
    print '<table summary="edit" class="noborder" width="100%">';
    print '<tr class="liste_titre"><td class="titlefield">'.$langs->trans("Parameters").'</td><td>'.$langs->trans("Value").'</td>';
	print '<td width="20">&nbsp;</td>';
	print '</tr>';

	// Max size of lists
    print '<tr><td>'.$langs->trans("DefaultMaxSizeList").'</td><td><input class="flat" name="main_size_liste_limit" size="4" value="' . $conf->global->MAIN_SIZE_LISTE_LIMIT . '"></td>';
	print '<td width="20">&nbsp;</td>';
	print '</tr>';

	// Max size of short lists on customer card
    print '<tr><td>'.$langs->trans("DefaultMaxSizeShortList").'</td><td><input class="flat" name="main_size_shortliste_limit" size="4" value="' . $conf->global->MAIN_SIZE_SHORTLIST_LIMIT . '"></td>';
	print '<td width="20">&nbsp;</td>';
	print '</tr>';
	
    // show input border
    /*
    print '<tr><td>'.$langs->trans("showInputBorder").'</td><td>';
    print $form->selectyesno('main_showInputBorder',isset($conf->global->THEME_ELDY_SHOW_BORDER_INPUT)?$conf->global->THEME_ELDY_SHOW_BORDER_INPUT:0,1);
    print '</td>';
	print '<td width="20">&nbsp;</td>';
	print '</tr>';
    */
	
	// Disable javascript and ajax
    print '<tr><td>'.$langs->trans("DisableJavascript").'</td><td>';
    print $form->selectyesno('main_disable_javascript',isset($conf->global->MAIN_DISABLE_JAVASCRIPT)?$conf->global->MAIN_DISABLE_JAVASCRIPT:0,1);
    print '</td>';
	print '<td width="20">&nbsp;</td>';
	print '</tr>';

    // First day for weeks
    print '<tr><td class="titlefield">'.$langs->trans("WeekStartOnDay").'</td><td>';
    print $formother->select_dayofweek((isset($conf->global->MAIN_START_WEEK)?$conf->global->MAIN_START_WEEK:'1'),'MAIN_START_WEEK',0);
    print '</td>';
	print '<td width="20">&nbsp;</td>';
	print '</tr>';

    // DefaultWorkingDays
    print '<tr><td class="titlefield">'.$langs->trans("DefaultWorkingDays").'</td><td>';
    print '<input type="text" name="MAIN_DEFAULT_WORKING_DAYS" size="5" value="'.(isset($conf->global->MAIN_DEFAULT_WORKING_DAYS)?$conf->global->MAIN_DEFAULT_WORKING_DAYS:'1-5').'">';
    print '</td>';
	print '<td width="20">&nbsp;</td>';
	print '</tr>';

    // DefaultWorkingHours
    print '<tr><td class="titlefield">'.$langs->trans("DefaultWorkingHours").'</td><td>';
    print '<input type="text" name="MAIN_DEFAULT_WORKING_HOURS" size="5" value="'.(isset($conf->global->MAIN_DEFAULT_WORKING_HOURS)?$conf->global->MAIN_DEFAULT_WORKING_HOURS:'9-18').'">';
    print '</td>';
	print '<td width="20">&nbsp;</td>';
	print '</tr>';

	// Firstname/Name
    print '<tr><td class="titlefield">'.$langs->trans("FirstnameNamePosition").'</td><td>';
	$array=array(0=>$langs->trans("Firstname").' '.$langs->trans("Lastname"),1=>$langs->trans("Lastname").' '.$langs->trans("Firstname"));
    print $form->selectarray('MAIN_FIRSTNAME_NAME_POSITION',$array,(isset($conf->global->MAIN_FIRSTNAME_NAME_POSITION)?$conf->global->MAIN_FIRSTNAME_NAME_POSITION:0));
    print '</td>';
	print '<td width="20">&nbsp;</td>';
	print '</tr>';

	// Hide unauthorized button
	print '<tr><td class="titlefield">'.$langs->trans("ButtonHideUnauthorized").'</td><td>';
	print $form->selectyesno('MAIN_BUTTON_HIDE_UNAUTHORIZED',isset($conf->global->MAIN_BUTTON_HIDE_UNAUTHORIZED)?$conf->global->MAIN_BUTTON_HIDE_UNAUTHORIZED:0,1);
	print '</td>';
	print '<td width="20">&nbsp;</td>';
	print '</tr>';

	// Show logo
	print '<tr><td class="titlefield">'.$langs->trans("EnableShowLogo").'</td><td>';
	print $form->selectyesno('MAIN_SHOW_LOGO',$conf->global->MAIN_SHOW_LOGO,1);
	print '</td>';
	print '<td width="20">&nbsp;</td>';
	print '</tr>';
	
	// Hide version link
	/*
	
	print '<tr><td class="titlefield">'.$langs->trans("HideVersionLink").'</td><td>';
	print $form->selectyesno('MAIN_HIDE_VERSION',$conf->global->MAIN_HIDE_VERSION,1);
	print '</td>';
	print '<td width="20">&nbsp;</td>';
	print '</tr>';
    */
	
	// Show bugtrack link
	print '<tr><td class="titlefield">'.$langs->trans("ShowBugTrackLink", $langs->transnoentitiesnoconv("FindBug")).'</td><td>';
	print $form->selectyesno('MAIN_BUGTRACK_ENABLELINK',$conf->global->MAIN_BUGTRACK_ENABLELINK,1);
	print '</td>';
	print '<td width="20">&nbsp;</td>';
	print '</tr>';

	// Hide wiki link on login page
    print '<tr><td class="titlefield">'.$langs->trans("DisableLinkToHelp",img_picto('',DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/helpdoc.png','',1)).'</td><td>';
    print $form->selectyesno('MAIN_HELP_DISABLELINK', isset($conf->global->MAIN_HELP_DISABLELINK)?$conf->global->MAIN_HELP_DISABLELINK:0,1);
    print '</td>';
	print '<td width="20">&nbsp;</td>';
	print '</tr>';

	// Hide helpcenter link on login page
	print '<tr><td class="titlefield">'.$langs->trans("DisableLinkToHelpCenter").'</td><td>';
	print $form->selectyesno('MAIN_HELPCENTER_DISABLELINK',isset($conf->global->MAIN_HELPCENTER_DISABLELINK)?$conf->global->MAIN_HELPCENTER_DISABLELINK:0,1);
	print '</td>';
	print '<td width="20">&nbsp;</td>';
	print '</tr>';
	
	// Message of the day on home page
    print '<tr><td class="titlefield">'.$langs->trans("MessageOfDay").'</td><td colspan="2">';

    $doleditor = new DolEditor('main_motd', (isset($conf->global->MAIN_MOTD)?$conf->global->MAIN_MOTD:''), '', 142, 'dolibarr_notes', 'In', false, true, true, ROWS_4, '90%');
	$doleditor->Create();

	print '</td></tr>'."\n";

	print '</table>'."\n";

	print '<br>';
	
	// Other
	print '<table summary="edit" class="noborder" width="100%">';
	print '<tr class="liste_titre"><td class="titlefield">'.$langs->trans("LoginPage").'</td><td></td>';
	print '<td width="20">&nbsp;</td>';
	print '</tr>';
	
	// Message on login page
	print '<tr><td>'.$langs->trans("MessageLogin").'</td><td colspan="2">';
	$doleditor = new DolEditor('main_home', (isset($conf->global->MAIN_HOME)?$conf->global->MAIN_HOME:''), '', 142, 'dolibarr_notes', 'In', false, true, true, ROWS_4, '90%');
	$doleditor->Create();
	print '</td></tr>'."\n";
	
	// Background
	
	print '<tr><td><label for="imagebackground">'.$langs->trans("BackgroundImageLogin").' (png,jpg)</label></td><td colspan="2">';
    print '<div class="centpercent inline-block">';
	print '<input type="file" class="flat class=minwidth200" name="imagebackground" id="imagebackground">';
	if (! empty($conf->global->MAIN_LOGIN_BACKGROUND)) {
	    print '<a href="'.$_SERVER["PHP_SELF"].'?action=removebackgroundlogin">'.img_delete($langs->trans("Delete")).'</a>';
	    if (file_exists($conf->mycompany->dir_output.'/logos/'.$conf->global->MAIN_LOGIN_BACKGROUND)) {
	        print ' &nbsp; ';
	        print '<img class="paddingleft valignmiddle" width="100px" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart=mycompany&amp;file='.urlencode('/'.$conf->global->MAIN_LOGIN_BACKGROUND).'">';
	    }
	} else {
	    print '<img class="paddingleft valignmiddle" width="100" src="'.DOL_URL_ROOT.'/public/theme/common/nophoto.png">';
	}
	print '</div>';
	print '</td></tr>';
	
	print '</table>'."\n";
	

    print '<br><div class="center">';
    print '<input class="button" type="submit" name="submit" value="'.$langs->trans("Save").'">';
    print ' &nbsp; ';
    print '<input class="button" type="submit" name="cancel" value="'.$langs->trans("Cancel").'">';
    print '</div>';

    print '</form>';
}
else	// Show
{
    // Language
    print '<table class="noborder" width="100%">';
    print '<tr class="liste_titre"><td>'.$langs->trans("Language").'</td><td></td><td>&nbsp;</td></tr>';

    print '<tr class="oddeven"><td class="titlefield">'.$langs->trans("DefaultLanguage").'</td><td>';
    $s=picto_from_langcode($conf->global->MAIN_LANG_DEFAULT);
    print ($s?$s.' ':'');
    print ($conf->global->MAIN_LANG_DEFAULT=='auto'?$langs->trans("AutoDetectLang"):$langs->trans("Language_".$conf->global->MAIN_LANG_DEFAULT));
    print '</td>';
	print '<td width="20">';
    if ($user->admin && $conf->global->MAIN_LANG_DEFAULT!='auto') print info_admin($langs->trans("SubmitTranslation".($conf->global->MAIN_LANG_DEFAULT=='en_US'?'ENUS':''),$conf->global->MAIN_LANG_DEFAULT),1);
	print '</td>';
	print "</tr>";
    
    print '<tr class="oddeven"><td class="titlefield">'.$langs->trans("EnableMultilangInterface").'</td><td>' . yn($conf->global->MAIN_MULTILANGS) . '</td>';
	print '<td width="20">&nbsp;</td>';
	print "</tr>";

	print '</table><br>'."\n";


	// Themes
    show_theme(null,0);
    print '<br>';


    // List of search forms to show
    if (! empty($searchform))
    {
        print '<table class="noborder" width="100%">';
        print '<tr class="liste_titre"><td class="titlefield">'.$langs->trans("PermanentLeftSearchForm").'</td><td>'.$langs->trans("Activated").'</td><td>&nbsp;</td></tr>';
        foreach ($searchform as $key => $value)
        {
            
            print '<tr class="oddeven"><td class="titlefield">'.$searchformtitle[$key].'</td><td>'.yn($searchformconst[$key]).'</td>';
    		print '<td align="left">';
    		if (! empty($searchformmodule[$key])) print $langs->trans("IfModuleEnabled",$langs->transnoentitiesnoconv($searchformmodule[$key]));
            print '</td></tr>';
        }
        print '</table>';
        print '<br>';
    }

    // Other
    print '<table class="noborder" width="100%">';
    print '<tr class="liste_titre"><td class="titlefield">'.$langs->trans("Parameters").'</td><td colspan="2">'.$langs->trans("Value").'</td></tr>';

    print '<tr class="oddeven"><td>'.$langs->trans("DefaultMaxSizeList").'</td><td>' . $conf->global->MAIN_SIZE_LISTE_LIMIT . '</td>';
	print '<td width="20">&nbsp;</td>';
	print "</tr>";
	
    print '<tr class="oddeven"><td>'.$langs->trans("DefaultMaxSizeShortList").'</td><td>' . $conf->global->MAIN_SIZE_SHORTLIST_LIMIT . '</td>';
	print '<td width="20">&nbsp;</td>';
	print "</tr>";

	/*
    print '<tr><td>'.$langs->trans("showInputBorder").'</td><td>';
    print yn($conf->global->THEME_ELDY_SHOW_BORDER_INPUT)."</td>";
    print '<td width="20">&nbsp;</td>';
    print "</tr>";
	*/
	
    // Disable javascript/ajax
    print '<tr class="oddeven"><td class="titlefield">'.$langs->trans("DisableJavascript").'</td><td>';
    print yn($conf->global->MAIN_DISABLE_JAVASCRIPT)."</td>";
    print '<td width="20">&nbsp;</td>';
    print "</tr>";

	// First day for weeks
    print '<tr class="oddeven"><td class="titlefield">'.$langs->trans("WeekStartOnDay").'</td><td>';
    print $langs->trans("Day".(isset($conf->global->MAIN_START_WEEK)?$conf->global->MAIN_START_WEEK:'1'));
    print '</td>';
	print '<td width="20">&nbsp;</td>';
	print '</tr>';

    // DefaultWorkingDays
    print '<tr class="oddeven"><td class="titlefield">'.$langs->trans("DefaultWorkingDays").'</td><td>';
    print isset($conf->global->MAIN_DEFAULT_WORKING_DAYS)?$conf->global->MAIN_DEFAULT_WORKING_DAYS:'1-5';
    print '</td>';
	print '<td width="20">&nbsp;</td>';
	print '</tr>';

    // DefaultWorkingHours
    print '<tr class="oddeven"><td class="titlefield">'.$langs->trans("DefaultWorkingHours").'</td><td>';
    print isset($conf->global->MAIN_DEFAULT_WORKING_HOURS)?$conf->global->MAIN_DEFAULT_WORKING_HOURS:'9-18';
    print '</td>';
	print '<td width="20">&nbsp;</td>';
	print '</tr>';

	// Firstname / Name position
    print '<tr class="oddeven"><td class="titlefield">'.$langs->trans("FirstnameNamePosition").'</td><td>';
    if (empty($conf->global->MAIN_FIRSTNAME_NAME_POSITION)) { print $langs->trans("Firstname").' '.$langs->trans("Lastname"); }
    else { print $langs->trans("Lastname").' '.$langs->trans("Firstname"); }
    print '</td>';
	print '<td width="20">&nbsp;</td>';
	print '</tr>';

	// Hide unauthorized button
	print '<tr class="oddeven"><td class="titlefield">'.$langs->trans("ButtonHideUnauthorized").'</td><td colspan="2">';
	print yn((isset($conf->global->MAIN_BUTTON_HIDE_UNAUTHORIZED)?$conf->global->MAIN_BUTTON_HIDE_UNAUTHORIZED:0),1);
	print '</td></tr>';

    // Show logo
    print '<tr class="oddeven"><td>'.$langs->trans("EnableShowLogo").'</td><td>' . yn($conf->global->MAIN_SHOW_LOGO) . '</td>';
	print '<td width="20">&nbsp;</td>';
	print "</tr>";

	// Hide version link
	/*
	 print '<tr><td class="titlefield">'.$langs->trans("HideVersionLink").'</td><td>';
	 print yn($conf->global->MAIN_HIDE_VERSION);
	 print '</td>';
	 print '<td width="20">&nbsp;</td>';
	 print '</tr>';
	 */
	
    // Show bugtrack link
	print '<tr class="oddeven"><td class="titlefield">'.$langs->trans("ShowBugTrackLink", $langs->transnoentitiesnoconv("FindBug")).'</td><td>';
	print yn($conf->global->MAIN_BUGTRACK_ENABLELINK)."</td>";
	print '<td width="20">&nbsp;</td>';
	print "</tr>";

    // Link to wiki help
    print '<tr class="oddeven"><td class="titlefield">'.$langs->trans("DisableLinkToHelp",img_picto('',DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/helpdoc.png','',1)).'</td><td colspan="2">';
    print yn((isset($conf->global->MAIN_HELP_DISABLELINK)?$conf->global->MAIN_HELP_DISABLELINK:0),1);
    print '</td></tr>';

	// Link to help center
    print '<tr class="oddeven"><td class="titlefield">'.$langs->trans("DisableLinkToHelpCenter").'</td><td colspan="2">';
    print yn((isset($conf->global->MAIN_HELPCENTER_DISABLELINK)?$conf->global->MAIN_HELPCENTER_DISABLELINK:0),1);
    print '</td></tr>';

    // Message of the day
    print '<tr class="oddeven"><td class="titlefield">'.$langs->trans("MessageOfDay").'</td><td colspan="2">';
    if (isset($conf->global->MAIN_MOTD)) print dol_htmlcleanlastbr($conf->global->MAIN_MOTD);
    else print '&nbsp;';
    print '</td></tr>'."\n";

    print '</table>'."\n";

    print '<br>';
    
    // Login page
    print '<table class="noborder" width="100%">';
    print '<tr class="liste_titre"><td>'.$langs->trans("LoginPage").'</td><td></td><td>&nbsp;</td></tr>';

    // Message login
    print '<tr class="oddeven"><td class="titlefield">'.$langs->trans("MessageLogin").'</td><td colspan="2">';
    if (isset($conf->global->MAIN_HOME)) print dol_htmlcleanlastbr($conf->global->MAIN_HOME);
    else print '&nbsp;';
    print '</td></tr>'."\n";
    
    // Background login
    
    print '<tr class="oddeven"><td>'.$langs->trans("BackgroundImageLogin").'</td><td colspan="2">';
    print '<div class="centpercent inline-block">';
    print $conf->global->MAIN_LOGIN_BACKGROUND;
    if ($conf->global->MAIN_LOGIN_BACKGROUND && is_file($conf->mycompany->dir_output.'/logos/'.$conf->global->MAIN_LOGIN_BACKGROUND))
    {
        print '<img class="img_logo paddingleft valignmiddle" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart=mycompany&amp;file='.urlencode($conf->global->MAIN_LOGIN_BACKGROUND).'">';
    }
    else
    {
        print '<img class="img_logo paddingleft valignmiddle" src="'.DOL_URL_ROOT.'/public/theme/common/nophoto.png">';
    }
    print '</div>';
    print '</td></tr>';
    
    print '</table>'."\n";
    
    print '<div class="tabsAction">';
    print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?action=edit">'.$langs->trans("Modify").'</a>';
    print '</div>';
}


llxFooter();
$db->close();
