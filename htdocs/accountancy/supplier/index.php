<?php
/* Copyright (C) 2013-2014 Olivier Geffroy		<jeff@jeffinfo.com>
 * Copyright (C) 2013-2014 Florian Henry		<florian.henry@open-concept.pro>
 * Copyright (C) 2013-2015 Alexandre Spangaro	<aspangaro.dolibarr@gmail.com>
 * Copyright (C) 2014	   Juanjo Menent		<jmenent@2byte.es>
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
 * \file		htdocs/accountancy/supplier/index.php
 * \ingroup		Advanced accountancy
 * \brief		Home supplier ventilation
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/accounting.lib.php';

// Langs
$langs->load("compta");
$langs->load("bills");
$langs->load("other");
$langs->load("main");
$langs->load("accountancy");

// Security check
if (empty($conf->accounting->enabled)) {
    accessforbidden();
}
if ($user->societe_id > 0)
	accessforbidden();
if (! $user->rights->accounting->bind->write)
	accessforbidden();

// Filter
$year = GETPOST("year",'int');
if ($year == 0) {
	$year_current = strftime("%Y", time());
	$year_start = $year_current;
} else {
	$year_current = $year;
	$year_start = $year;
}

// Validate History
$action = GETPOST('action', 'alpha');


/*
 * Actions
 */

if ($action == 'validatehistory') {

	$error = 0;
	$db->begin();

	// First clean corrupted data
	$sqlclean = "UPDATE " . MAIN_DB_PREFIX . "facturedet as fd";
	$sqlclean .= " SET fd.fk_code_ventilation = 0";
	$sqlclean .= ' WHERE fd.fk_code_ventilation NOT IN ';
	$sqlclean .= '	(SELECT accnt.rowid ';
	$sqlclean .= '	FROM ' . MAIN_DB_PREFIX . 'accounting_account as accnt';
	$sqlclean .= '	INNER JOIN ' . MAIN_DB_PREFIX . 'accounting_system as syst';
	$sqlclean .= '	ON accnt.fk_pcg_version = syst.pcg_version AND syst.rowid=' . $conf->global->CHARTOFACCOUNTS . ')';
	$resql = $db->query($sqlclean);
	
	// Now make the binding. Bind automatically only for product with a dedicated account that exists into chart of account, others need a manual bind
	if ($db->type == 'pgsql') {
		$sql1 = "UPDATE " . MAIN_DB_PREFIX . "facture_fourn_det";
		$sql1 .= " SET fk_code_ventilation = accnt.rowid";
		$sql1 .= " FROM " . MAIN_DB_PREFIX . "product as p, " . MAIN_DB_PREFIX . "accounting_account as accnt , " . MAIN_DB_PREFIX . "accounting_system as syst";
		$sql1 .= " WHERE " . MAIN_DB_PREFIX . "facture_fourn_det.fk_product = p.rowid  AND accnt.fk_pcg_version = syst.pcg_version AND syst.rowid=" . $conf->global->CHARTOFACCOUNTS;
		$sql1 .= " AND accnt.active = 1 AND p.accountancy_code_buy=accnt.account_number";
		$sql1 .= " AND " . MAIN_DB_PREFIX . "facture_fourn_det.fk_code_ventilation = 0";
	} else {
		$sql1 = "UPDATE " . MAIN_DB_PREFIX . "facture_fourn_det as fd, " . MAIN_DB_PREFIX . "product as p, " . MAIN_DB_PREFIX . "accounting_account as accnt , " . MAIN_DB_PREFIX . "accounting_system as syst";
		$sql1 .= " SET fd.fk_code_ventilation = accnt.rowid";
		$sql1 .= " WHERE fd.fk_product = p.rowid AND accnt.fk_pcg_version = syst.pcg_version AND syst.rowid=" . $conf->global->CHARTOFACCOUNTS;
		$sql1 .= " AND accnt.active = 1 AND p.accountancy_code_buy=accnt.account_number";
		$sql1 .= " AND fd.fk_code_ventilation = 0";
	}

	$resql1 = $db->query($sql1);
	if (! $resql1) {
		$error ++;
		$db->rollback();
		setEventMessages($db->lasterror(), null, 'errors');
	} else {
		$db->commit();
		setEventMessages($langs->trans('AutomaticBindingDone'), null, 'mesgs');
	}
} elseif ($action == 'fixaccountancycode') {
	$error = 0;
	$db->begin();

	$sql1 = "UPDATE " . MAIN_DB_PREFIX . "facture_fourn_det as fd";
	$sql1 .= " SET fd.fk_code_ventilation = 0";
	$sql1 .= ' WHERE fd.fk_code_ventilation NOT IN ';
	$sql1 .= '	(SELECT accnt.rowid ';
	$sql1 .= '	FROM ' . MAIN_DB_PREFIX . 'accounting_account as accnt';
	$sql1 .= '	INNER JOIN ' . MAIN_DB_PREFIX . 'accounting_system as syst';
	$sql1 .= '	ON accnt.fk_pcg_version = syst.pcg_version AND syst.rowid=' . $conf->global->CHARTOFACCOUNTS . ')';

	dol_syslog("htdocs/accountancy/customer/index.php fixaccountancycode", LOG_DEBUG);

	$resql1 = $db->query($sql1);
	if (! $resql1) {
		$error ++;
		$db->rollback();
		setEventMessage($db->lasterror(), 'errors');
	} else {
		$db->commit();
		setEventMessage($langs->trans('Done'), 'mesgs');
	}
} elseif ($action == 'cleanaccountancycode') {
	$error = 0;
	$db->begin();

	$sql1 = "UPDATE " . MAIN_DB_PREFIX . "facture_fourn_det as fd";
	$sql1.= " SET fd.fk_code_ventilation = 0";
	$sql1.= " WHERE fd.fk_facture_fourn IN ( SELECT f.rowid FROM " . MAIN_DB_PREFIX . "facture_fourn as f";
	$sql1.= " WHERE f.datef >= '" . $db->idate(dol_get_first_day($year_current, 1, false)) . "'";
	$sql1.= " AND f.datef <= '" . $db->idate(dol_get_last_day($year_current, 12, false)) . "'";
	$sql1.= " AND f.entity IN (" . getEntity("accountancy", 1) . ")";
	$sql1.= ")";
	
	dol_syslog("htdocs/accountancy/customer/index.php fixaccountancycode", LOG_DEBUG);

	$resql1 = $db->query($sql1);
	if (! $resql1) {
		$error ++;
		$db->rollback();
		setEventMessage($db->lasterror(), 'errors');
	} else {
		$db->commit();
		setEventMessage($langs->trans('Done'), 'mesgs');
	}
}

/*
 * View
 */

llxHeader('', $langs->trans("SuppliersVentilation"));

$textprevyear = '<a href="' . $_SERVER["PHP_SELF"] . '?year=' . ($year_current - 1) . '">' . img_previous() . '</a>';
$textnextyear = '&nbsp;<a href="' . $_SERVER["PHP_SELF"] . '?year=' . ($year_current + 1) . '">' . img_next() . '</a>';

print load_fiche_titre($langs->trans("SuppliersVentilation") . "&nbsp;" . $textprevyear . "&nbsp;" . $langs->trans("Year") . "&nbsp;" . $year_start . "&nbsp;" . $textnextyear, '', 'title_accountancy');

print $langs->trans("DescVentilSupplier") . '<br>';
print $langs->trans("DescVentilMore", $langs->transnoentitiesnoconv("ValidateHistory"), $langs->transnoentitiesnoconv("ToBind")) . '<br>';
print '<br>';

//print '<div class="inline-block divButAction">';
// TODO Remove this. Should be done always.
if ($conf->global->MAIN_FEATURES_LEVEL > 1) print '<a class="butActionDelete" href="' . $_SERVER['PHP_SELF'] . '?year=' . $year_current . '&action=fixaccountancycode">' . $langs->trans("CleanFixHistory", $year_current) . '</a>';
//print '</div>';

$buttonbind = '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?year=' . $year_current . '&action=validatehistory">' . $langs->trans("ValidateHistory") . '</a>';
$buttonreset = '<a class="butActionDelete" href="' . $_SERVER['PHP_SELF'] . '?year=' . $year_current . '&action=cleanaccountancycode">' . $langs->trans("CleanHistory", $year_current) . '</a>';



$y = $year_current;

$var = true;


print '<br>';

print_fiche_titre($langs->trans("OverviewOfAmountOfLinesNotBound"), $buttonbind, '');

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width="200" align="left">' . $langs->trans("Account") . '</td>';
print '<td width="200" align="left">' . $langs->trans("Label") . '</td>';
for($i = 1; $i <= 12; $i ++) {
	print '<td width="60" align="right">' . $langs->trans('MonthShort' . str_pad($i, 2, '0', STR_PAD_LEFT)) . '</td>';
}
print '<td width="60" align="right"><b>' . $langs->trans("Total") . '</b></td></tr>';

$sql = "SELECT  ".$db->ifsql('aa.account_number IS NULL', "'".$langs->trans('NotMatch')."'", 'aa.account_number') ." AS codecomptable,";
$sql .= "  " . $db->ifsql('aa.label IS NULL', "'".$langs->trans('NotMatch')."'", 'aa.label') . " AS intitule,";
for($i = 1; $i <= 12; $i ++) {
	$sql .= "  SUM(" . $db->ifsql('MONTH(ff.datef)=' . $i, 'ffd.total_ht', '0') . ") AS month" . str_pad($i, 2, '0', STR_PAD_LEFT) . ",";
}
$sql .= "  ROUND(SUM(ffd.total_ht),2) as total";
$sql .= " FROM " . MAIN_DB_PREFIX . "facture_fourn_det as ffd";
$sql .= "  LEFT JOIN " . MAIN_DB_PREFIX . "facture_fourn as ff ON ff.rowid = ffd.fk_facture_fourn";
$sql .= "  LEFT JOIN " . MAIN_DB_PREFIX . "accounting_account as aa ON aa.rowid = ffd.fk_code_ventilation";
$sql .= " WHERE ff.datef >= '" . $db->idate(dol_get_first_day($y, 1, false)) . "'";
$sql .= "  AND ff.datef <= '" . $db->idate(dol_get_last_day($y, 12, false)) . "'";
$sql .= "  AND ff.fk_statut > 0 ";
$sql .= " AND ff.entity IN (" . getEntity("facture_fourn", 0) . ")";     // We don't share object for accountancy
$sql .= " AND aa.account_number IS NULL";
$sql .= " GROUP BY ffd.fk_code_ventilation,aa.account_number,aa.label";

dol_syslog('/accountancy/supplier/index.php:: sql=' . $sql);
$resql = $db->query($sql);
if ($resql) {
	$num = $db->num_rows($resql);

	while ( $row = $db->fetch_row($resql)) {

		print '<tr class="oddeven"><td>' . length_accountg($row[0]) . '</td>';
		print '<td align="left">' . $row[1] . '</td>';
		for($i = 2; $i <= 12; $i ++) {
			print '<td align="right">' . price($row[$i]) . '</td>';
		}
		print '<td align="right">' . price($row[13]) . '</td>';
		print '<td align="right"><b>' . price($row[14]) . '</b></td>';
		print '</tr>';
	}
	$db->free($resql);
} else {
	print $db->lasterror(); // Show last sql error
}
print "</table>\n";




print '<br>';


print_fiche_titre($langs->trans("OverviewOfAmountOfLinesBound"), $buttonreset, '');

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width="200" align="left">' . $langs->trans("Account") . '</td>';
print '<td width="200" align="left">' . $langs->trans("Label") . '</td>';
for($i = 1; $i <= 12; $i ++) {
    print '<td width="60" align="right">' . $langs->trans('MonthShort' . str_pad($i, 2, '0', STR_PAD_LEFT)) . '</td>';
}
print '<td width="60" align="right"><b>' . $langs->trans("Total") . '</b></td></tr>';

$sql = "SELECT  ".$db->ifsql('aa.account_number IS NULL', "'".$langs->trans('NotMatch')."'", 'aa.account_number') ." AS codecomptable,";
$sql .= "  " . $db->ifsql('aa.label IS NULL', "'".$langs->trans('NotMatch')."'", 'aa.label') . " AS intitule,";
for($i = 1; $i <= 12; $i ++) {
    $sql .= "  SUM(" . $db->ifsql('MONTH(ff.datef)=' . $i, 'ffd.total_ht', '0') . ") AS month" . str_pad($i, 2, '0', STR_PAD_LEFT) . ",";
}
$sql .= "  ROUND(SUM(ffd.total_ht),2) as total";
$sql .= " FROM " . MAIN_DB_PREFIX . "facture_fourn_det as ffd";
$sql .= "  LEFT JOIN " . MAIN_DB_PREFIX . "facture_fourn as ff ON ff.rowid = ffd.fk_facture_fourn";
$sql .= "  LEFT JOIN " . MAIN_DB_PREFIX . "accounting_account as aa ON aa.rowid = ffd.fk_code_ventilation";
$sql .= " WHERE ff.datef >= '" . $db->idate(dol_get_first_day($y, 1, false)) . "'";
$sql .= "  AND ff.datef <= '" . $db->idate(dol_get_last_day($y, 12, false)) . "'";
$sql .= "  AND ff.fk_statut > 0 ";
$sql .= " AND ff.entity IN (" . getEntity("facture_fourn", 0) . ")";     // We don't share object for accountancy
$sql .= " AND aa.account_number IS NOT NULL";
$sql .= " GROUP BY ffd.fk_code_ventilation,aa.account_number,aa.label";

dol_syslog('/accountancy/supplier/index.php:: sql=' . $sql);
$resql = $db->query($sql);
if ($resql) {
    $num = $db->num_rows($resql);

    while ( $row = $db->fetch_row($resql)) {

        print '<tr class="oddeven"><td>' . length_accountg($row[0]) . '</td>';
        print '<td align="left">' . $row[1] . '</td>';
        for($i = 2; $i <= 12; $i ++) {
            print '<td align="right">' . price($row[$i]) . '</td>';
        }
        print '<td align="right">' . price($row[13]) . '</td>';
        print '<td align="right"><b>' . price($row[14]) . '</b></td>';
        print '</tr>';
    }
    $db->free($resql);
} else {
    print $db->lasterror(); // Show last sql error
}
print "</table>\n";




if ($conf->global->MAIN_FEATURES_LEVEL > 0) // This part of code looks strange. Why showing a report that should rely on result of this step ?
{
    print '<br>';
    print '<br>';
    
    print_fiche_titre($langs->trans("OtherInfo"), '', '');
    
    print "<br>\n";
    print '<table class="noborder" width="100%">';
    print '<tr class="liste_titre"><td width="400" align="left">' . $langs->trans("Total") . '</td>';
    for($i = 1; $i <= 12; $i ++) {
    	print '<td width="60" align="right">' . $langs->trans('MonthShort' . str_pad($i, 2, '0', STR_PAD_LEFT)) . '</td>';
    }
    print '<td width="60" align="right"><b>' . $langs->trans("Total") . '</b></td></tr>';
    
    $sql = "SELECT '" . $langs->trans("CAHTF") . "' AS label,";
    for($i = 1; $i <= 12; $i ++) {
    	$sql .= "  SUM(" . $db->ifsql('MONTH(ff.datef)=' . $i, 'ffd.total_ht', '0') . ") AS month" . str_pad($i, 2, '0', STR_PAD_LEFT) . ",";
    }
    $sql .= "  ROUND(SUM(ffd.total_ht),2) as total";
    $sql .= " FROM " . MAIN_DB_PREFIX . "facture_fourn_det as ffd";
    $sql .= "  LEFT JOIN " . MAIN_DB_PREFIX . "facture_fourn as ff ON ff.rowid = ffd.fk_facture_fourn";
    $sql .= " WHERE ff.datef >= '" . $db->idate(dol_get_first_day($y, 1, false)) . "'";
    $sql .= "  AND ff.datef <= '" . $db->idate(dol_get_last_day($y, 12, false)) . "'";
    $sql .= "  AND ff.fk_statut > 0 ";
    $sql .= " AND ff.entity IN (" . getEntity("facture_fourn", 0) . ")";     // We don't share object for accountancy
    
    dol_syslog('/accountancy/supplier/index.php:: sql=' . $sql);
    $resql = $db->query($sql);
    if ($resql) {
    	$num = $db->num_rows($resql);
    
    	while ( $row = $db->fetch_row($resql)) {
    
    
    		print '<tr><td>' . $row[0] . '</td>';
    			for($i = 1; $i <= 12; $i ++) {
    			print '<td align="right">' . price($row[$i]) . '</td>';
    		}
    		print '<td align="right"><b>' . price($row[13]) . '</b></td>';
    		print '</tr>';
    	}
    
    	$db->free($resql);
    } else {
    	print $db->lasterror(); // Show last sql error
    }
    print "</table>\n";
}


llxFooter();
$db->close();
