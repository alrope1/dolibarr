<?php
/* Copyright (C) 2003-2004 Rodolphe Quiedeville  <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2009 Laurent Destailleur   <eldy@users.sourceforge.net>
 * Copyright (C) 2005      Marc Barilley / Ocebo <marc@ocebo.com>
 * Copyright (C) 2005-2012 Regis Houssin         <regis.houssin@capnetworks.com>
 * Copyright (C) 2013      Cédric Salvador       <csalvador@gpcsolutions.fr>
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
 *       \file       htdocs/comm/propal/document.php
 *       \ingroup    propal
 *       \brief      Management page of documents attached to a business proposal
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/propal.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

$langs->load('compta');
$langs->load('other');
$langs->load('companies');

$action		= GETPOST('action','alpha');
$confirm	= GETPOST('confirm','alpha');
$id			= GETPOST('id','int');
$ref		= GETPOST('ref','alpha');

// Security check
$socid='';
if (! empty($user->societe_id))
{
	$action='';
	$socid = $user->societe_id;
}
$result = restrictedArea($user, 'propal', $id);

// Get parameters
$sortfield = GETPOST("sortfield",'alpha');
$sortorder = GETPOST("sortorder",'alpha');
$page = GETPOST("page",'int');
if ($page == -1) { $page = 0; }
$offset = $conf->liste_limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (! $sortorder) $sortorder="ASC";
if (! $sortfield) $sortfield="name";

$object = new Propal($db);
$object->fetch($id,$ref);
if ($object->id > 0)
{
	$object->fetch_thirdparty();
	$upload_dir = $conf->propal->dir_output.'/'.dol_sanitizeFileName($object->ref);
	include_once DOL_DOCUMENT_ROOT . '/core/actions_linkedfiles.inc.php';
}

/*
 * Actions
 */



/*
 * View
 */

llxHeader('',$langs->trans('Proposal'),'EN:Commercial_Proposals|FR:Proposition_commerciale|ES:Presupuestos');

$form = new Form($db);

if ($object->id > 0)
{
	$upload_dir = $conf->propal->dir_output.'/'.dol_sanitizeFileName($object->ref);

	$head = propal_prepare_head($object);
	dol_fiche_head($head, 'document', $langs->trans('Proposal'), -1, 'propal');

	// Construit liste des fichiers
	$filearray=dol_dir_list($upload_dir,"files",0,'','(\.meta|_preview.*\.png)$',$sortfield,(strtolower($sortorder)=='desc'?SORT_DESC:SORT_ASC),1);
	$totalsize=0;
	foreach($filearray as $key => $file)
	{
		$totalsize+=$file['size'];
	}


	// Proposal card
	
	$linkback = '<a href="' . DOL_URL_ROOT . '/comm/propal/list.php' . (! empty($socid) ? '?socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';
	
	
	$morehtmlref='<div class="refidno">';
	// Ref customer
	$morehtmlref.=$form->editfieldkey("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', 0, 1);
	$morehtmlref.=$form->editfieldval("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', null, null, '', 1);
	// Thirdparty
	$morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . $object->thirdparty->getNomUrl(1);
	// Project
	if (! empty($conf->projet->enabled))
	{
	    $langs->load("projects");
	    $morehtmlref.='<br>'.$langs->trans('Project') . ' ';
	    if ($user->rights->propal->creer)
	    {
	        if ($action != 'classify')
	            //$morehtmlref.='<a href="' . $_SERVER['PHP_SELF'] . '?action=classify&amp;id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetProject')) . '</a>';
	            $morehtmlref.=' : ';
	            if ($action == 'classify') {
	                //$morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'projectid', 0, 0, 1, 1);
	                $morehtmlref.='<form method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'">';
	                $morehtmlref.='<input type="hidden" name="action" value="classin">';
	                $morehtmlref.='<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	                $morehtmlref.=$formproject->select_projects($object->socid, $object->fk_project, 'projectid', $maxlength, 0, 1, 0, 1, 0, 0, '', 1);
	                $morehtmlref.='<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
	                $morehtmlref.='</form>';
	            } else {
	                $morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'none', 0, 0, 0, 1);
	            }
	    } else {
	        if (! empty($object->fk_project)) {
	            $proj = new Project($db);
	            $proj->fetch($object->fk_project);
	            $morehtmlref.='<a href="'.DOL_URL_ROOT.'/projet/card.php?id=' . $object->fk_project . '" title="' . $langs->trans('ShowProject') . '">';
	            $morehtmlref.=$proj->ref;
	            $morehtmlref.='</a>';
	        } else {
	            $morehtmlref.='';
	        }
	    }
	}
	$morehtmlref.='</div>';
	
	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);
	
	
	print '<div class="fichecenter">';
	print '<div class="underbanner clearboth"></div>';
	
	print '<table class="border" width="100%">';
	
	// Files infos
	print '<tr><td class="titlefield">'.$langs->trans("NbOfAttachedFiles").'</td><td>'.count($filearray).'</td></tr>';
	print '<tr><td>'.$langs->trans("TotalSizeOfAttachedFiles").'</td><td>'.$totalsize.' '.$langs->trans("bytes").'</td></tr>';
	
	print "</table>\n";
	
	print '</div>';
	
	
	dol_fiche_end();

	$modulepart = 'propal';
	$permission = $user->rights->propal->creer;
	$permtoedit = $user->rights->propal->creer;
	$param = '&id=' . $object->id;
	include_once DOL_DOCUMENT_ROOT . '/core/tpl/document_actions_post_headers.tpl.php';
}
else
{
	print $langs->trans("ErrorUnknown");
}

llxFooter();
$db->close();
