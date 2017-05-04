<?php
/* Copyright (C) 2003-2004	Rodolphe Quiedeville	<rodolphe@quiedeville.org>
 * Copyright (C) 2004-2013	Laurent Destailleur		<eldy@users.sourceforge.net>
 * Copyright (C) 2004		Sebastien Di Cintio		<sdicintio@ressource-toi.org>
 * Copyright (C) 2004		Benoit Mortier			<benoit.mortier@opensides.be>
 * Copyright (C) 2005-2012	Regis Houssin			<regis.houssin@capnetworks.com>
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
 * 		\defgroup   facture     Module invoices
 *      \brief      Module pour gerer les factures clients et/ou fournisseurs
 *      \file       htdocs/core/modules/modFacture.class.php
 *		\ingroup    facture
 *		\brief      Fichier de la classe de description et activation du module Facture
 */
include_once DOL_DOCUMENT_ROOT .'/core/modules/DolibarrModules.class.php';


/**
 *  Class to describe module customer invoices
 */
class modFacture extends DolibarrModules
{

	/**
	 *   Constructor. Define names, constants, directories, boxes, permissions
	 *
	 *   @param      DoliDB		$db      Database handler
	 */
	function __construct($db)
	{
		global $conf, $user;

		$this->db = $db;
		$this->numero = 30;

		$this->family = "financial";
		$this->module_position = 10;
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i','',get_class($this));
		$this->description = "Gestion des factures";

		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = 'dolibarr';

		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		$this->special = 0;
		$this->picto='bill';

		// Data directories to create when module is enabled
		$this->dirs = array("/facture/temp");

		// Dependencies
		$this->depends = array('always'=>"modSociete", 'FR'=>'modBlockedLog');
		$this->requiredby = array("modComptabilite","modAccounting");
		$this->conflictwith = array();
		$this->langfiles = array("bills","companies","compta","products");
		$this->warnings_activation = array('FR'=>'WarningNoteModuleInvoiceForFrenchLaw');                              // Warning to show when we activate module. array('always'='text') or array('FR'='text')
		$this->warnings_activation = array();
		$this->warnings_activation_ext = array('FR'=>'WarningInstallationMayBecomeNotCompliantWithLaw');  // Warning to show when we activate an external module. array('always'='text') or array('FR'='text')
		
		// Config pages
		$this->config_page_url = array("facture.php");

		// Constants
		$this->const = array();
		$r=0;

		$this->const[$r][0] = "FACTURE_ADDON_PDF";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = "crabe";
		$this->const[$r][3] = 'Name of PDF model of invoice';
		$this->const[$r][4] = 0;
		$r++;

		$this->const[$r][0] = "FACTURE_ADDON";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = "mod_facture_terre";
		$this->const[$r][3] = 'Name of numbering numerotation rules of invoice';
		$this->const[$r][4] = 0;
		$r++;

		$this->const[$r][0] = "FACTURE_ADDON_PDF_ODT_PATH";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = "DOL_DATA_ROOT/doctemplates/invoices";
		$this->const[$r][3] = "";
		$this->const[$r][4] = 0;
		$r++;
		
		/*$this->const[$r][0] = "FACTURE_DRAFT_WATERMARK";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = "__(Draft)__";
		$this->const[$r][3] = 'Watermark to show on draft invoices';
		$this->const[$r][4] = 0;
		$r++;*/

		
		// Boxes
		//$this->boxes = array(0=>array(1=>'box_factures_imp.php'),1=>array(1=>'box_factures.php'));
		$this->boxes = array(
				0=>array('file'=>'box_factures_imp.php','enabledbydefaulton'=>'Home'),
				1=>array('file'=>'box_factures.php','enabledbydefaulton'=>'Home'),
				2=>array('file'=>'box_graph_invoices_permonth.php','enabledbydefaulton'=>'Home')
		);

        // Cronjobs 
        $this->cronjobs = array(
            0=>array('label'=>'RecurringInvoices', 'jobtype'=>'method', 'class'=>'compta/facture/class/facture-rec.class.php', 'objectname'=>'FactureRec', 'method'=>'createRecurringInvoices', 'parameters'=>'', 'comment'=>'Generate recurring invoices', 'frequency'=>1, 'unitfrequency'=>3600*24), 
            // 1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'', 'comment'=>'Comment', 'frequency'=>3600, 'unitfrequency'=>3600)
        ); 
        // List of cron jobs entries to add 
        // Example: 
        // $this->cronjobs=array(
        //              0=>array('label'=>'My label', 'jobtype'=>'method', 'class'=>'MyClass', 'method'=>'myMethod', 'parameters'=>'', 'comment'=>'Comment', 'frequency'=>1, 'unitfrequency'=>3600), 
        //              1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'', 'comment'=>'Comment', 'frequency'=>1, 'unitfrequency'=>3600) 
        // );

        // Permissions
		$this->rights = array();
		$this->rights_class = 'facture';
		$r=0;

		$r++;
		$this->rights[$r][0] = 11;
		$this->rights[$r][1] = 'Lire les factures';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'lire';

		$r++;
		$this->rights[$r][0] = 12;
		$this->rights[$r][1] = 'Creer/modifier les factures';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'creer';

		// There is a particular permission for unvalidate because this may be not forbidden by some laws
		$r++;
		$this->rights[$r][0] = 13;
		$this->rights[$r][1] = 'Dévalider les factures';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 0;
        $this->rights[$r][4] = 'invoice_advance';
		$this->rights[$r][5] = 'unvalidate';

		$r++;
		$this->rights[$r][0] = 14;
		$this->rights[$r][1] = 'Valider les factures';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'invoice_advance';
		$this->rights[$r][5] = 'validate';

		$r++;
		$this->rights[$r][0] = 15;
		$this->rights[$r][1] = 'Envoyer les factures par mail';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'invoice_advance';
        $this->rights[$r][5] = 'send';

		$r++;
		$this->rights[$r][0] = 16;
		$this->rights[$r][1] = 'Emettre des paiements sur les factures';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'paiement';

		$r++;
		$this->rights[$r][0] = 19;
		$this->rights[$r][1] = 'Supprimer les factures';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'supprimer';

		$r++;
		$this->rights[$r][0] = 1321;
		$this->rights[$r][1] = 'Exporter les factures clients, attributs et reglements';
		$this->rights[$r][2] = 'r';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'facture';
		$this->rights[$r][5] = 'export';

		$r++;
		$this->rights[$r][0] = 1322;
		$this->rights[$r][1] = 'Rouvrir une facture totalement réglée';
		$this->rights[$r][2] = 'r';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'invoice_advance';
		$this->rights[$r][5] = 'reopen';


		// Menus
		//-------
		$this->menu = 1;        // This module add menu entries. They are coded into menu manager.
		
		
		// Exports
		//--------
		$r=1;

		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='CustomersInvoicesAndInvoiceLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->export_icon[$r]='invoice';
		$this->export_permission[$r]=array(array("facture","facture","export","other"));
		$this->export_fields_array[$r]=array('s.rowid'=>"IdCompany",'s.nom'=>'CompanyName','s.address'=>'Address','s.zip'=>'Zip','s.town'=>'Town','c.code'=>'CountryCode','s.phone'=>'Phone','s.siren'=>'ProfId1','s.siret'=>'ProfId2','s.ape'=>'ProfId3','s.idprof4'=>'ProfId4','s.code_compta'=>'CustomerAccountancyCode','s.code_compta_fournisseur'=>'SupplierAccountancyCode','s.tva_intra'=>'VATIntra','f.rowid'=>"InvoiceId",'f.facnumber'=>"InvoiceRef",'f.type'=>"Type",'f.datec'=>"InvoiceDateCreation",'f.datef'=>"DateInvoice",'f.date_lim_reglement'=>"DateDue",'f.total'=>"TotalHT",'f.total_ttc'=>"TotalTTC",'f.tva'=>"TotalVAT",'none.rest'=>'Rest','f.paye'=>"InvoicePaid",'f.fk_statut'=>'InvoiceStatus','f.note_private'=>"NotePrivate",'f.note_public'=>"NotePublic",'f.fk_user_author'=>'CreatedById','uc.login'=>'CreatedByLogin','f.fk_user_valid'=>'ValidatedById','uv.login'=>'ValidatedByLogin', 'pj.ref'=>'ProjectRef', 'fd.rowid'=>'LineId','fd.description'=>"LineDescription",'fd.subprice'=>"LineUnitPrice",'fd.tva_tx'=>"LineVATRate",'fd.qty'=>"LineQty",'fd.total_ht'=>"LineTotalHT",'fd.total_tva'=>"LineTotalVAT",'fd.total_ttc'=>"LineTotalTTC",'fd.date_start'=>"DateStart",'fd.date_end'=>"DateEnd",'fd.special_code'=>'SpecialCode','fd.product_type'=>"TypeOfLineServiceOrProduct",'fd.fk_product'=>'ProductId','p.ref'=>'ProductRef','p.label'=>'ProductLabel','p.accountancy_code_sell'=>'ProductAccountancySellCode');
		$this->export_TypeFields_array[$r]=array('s.rowid'=>'Numeric','s.nom'=>'Text','s.address'=>'Text','s.zip'=>'Text','s.town'=>'Text','c.code'=>'Text','s.phone'=>'Text','s.siren'=>'Text','s.siret'=>'Text','s.ape'=>'Text','s.idprof4'=>'Text','s.code_compta'=>'Text','s.code_compta_fournisseur'=>'Text','s.tva_intra'=>'Text','f.rowid'=>'Numeric','f.facnumber'=>"Text",'f.type'=>"Numeric",'f.datec'=>"Date",'f.datef'=>"Date",'f.date_lim_reglement'=>"Date",'f.total'=>"Numeric",'f.total_ttc'=>"Numeric",'f.tva'=>"Numeric",'none.rest'=>"NumericCompute",'f.paye'=>"Boolean",'f.fk_statut'=>'Numeric','f.note_private'=>"Text",'f.note_public'=>"Text",'f.fk_user_author'=>'Numeric','uc.login'=>'Text','f.fk_user_valid'=>'Numeric','uv.login'=>'Text','pj.ref'=>'Text','fd.rowid'=>'Numeric','fd.label'=>'Text','fd.description'=>"Text",'fd.subprice'=>"Numeric",'fd.tva_tx'=>"Numeric",'fd.qty'=>"Numeric",'fd.total_ht'=>"Numeric",'fd.total_tva'=>"Numeric",'fd.total_ttc'=>"Numeric",'fd.date_start'=>"Date",'fd.date_end'=>"Date",'fd.special_code'=>'Numeric','fd.product_type'=>"Numeric",'fd.fk_product'=>'List:product:label','p.ref'=>'Text','p.label'=>'Text','p.accountancy_code_sell'=>'Text');
		$this->export_entities_array[$r]=array('s.rowid'=>"company",'s.nom'=>'company','s.address'=>'company','s.zip'=>'company','s.town'=>'company','c.code'=>'company','s.phone'=>'company','s.siren'=>'company','s.siret'=>'company','s.ape'=>'company','s.idprof4'=>'company','s.code_compta'=>'company','s.code_compta_fournisseur'=>'company','s.tva_intra'=>'company', 'pj.ref'=>'project', 'fd.rowid'=>'invoice_line','fd.label'=>"invoice_line",'fd.description'=>"invoice_line",'fd.subprice'=>"invoice_line",'fd.total_ht'=>"invoice_line",'fd.total_tva'=>"invoice_line",'fd.total_ttc'=>"invoice_line",'fd.tva_tx'=>"invoice_line",'fd.qty'=>"invoice_line",'fd.date_start'=>"invoice_line",'fd.date_end'=>"invoice_line",'fd.special_code'=>'invoice_line','fd.product_type'=>'invoice_line','fd.fk_product'=>'product','p.ref'=>'product','p.label'=>'product','p.accountancy_code_sell'=>'product','f.fk_user_author'=>'user','uc.login'=>'user','f.fk_user_valid'=>'user','uv.login'=>'user');
		$this->export_special_array[$r]=array('none.rest'=>'getRemainToPay');
		$this->export_dependencies_array[$r]=array('invoice_line'=>'fd.rowid', 'product'=>'fd.rowid', 'none.rest'=>array('f.rowid','f.total_ttc')); // To add unique key if we ask a field of a child to avoid the DISTINCT to discard them
		$keyforselect='facture'; $keyforelement='invoice'; $keyforaliasextra='extra';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		$keyforselect='facturedet'; $keyforelement='invoice_line'; $keyforaliasextra='extra2';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		$keyforselect='product'; $keyforelement='product'; $keyforaliasextra='extra3';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'societe as s';
		if (empty($user->rights->societe->client->voir)) $this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'societe_commerciaux as sc ON sc.fk_soc = s.rowid';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'c_country as c on s.fk_pays = c.rowid,';
		$this->export_sql_end[$r] .=' '.MAIN_DB_PREFIX.'facture as f';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'projet as pj ON f.fk_projet = pj.rowid';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'user as uc ON f.fk_user_author = uc.rowid';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'user as uv ON f.fk_user_valid = uv.rowid';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'facture_extrafields as extra ON f.rowid = extra.fk_object';
		$this->export_sql_end[$r] .=' , '.MAIN_DB_PREFIX.'facturedet as fd';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'facturedet_extrafields as extra2 on fd.rowid = extra2.fk_object';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'product as p on (fd.fk_product = p.rowid)';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'product_extrafields as extra3 on p.rowid = extra3.fk_object';
		$this->export_sql_end[$r] .=' WHERE f.fk_soc = s.rowid AND f.rowid = fd.fk_facture';
		$this->export_sql_end[$r] .=' AND f.entity IN ('.getEntity('facture',1).')';
		if(isset($user) && empty($user->rights->societe->client->voir)) $this->export_sql_end[$r] .=' AND sc.fk_user = '.$user->id;
		$r++;

		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='CustomersInvoicesAndPayments';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->export_icon[$r]='invoice';
		$this->export_permission[$r]=array(array("facture","facture","export"));
		$this->export_fields_array[$r]=array('s.rowid'=>"IdCompany",'s.nom'=>'CompanyName','s.address'=>'Address','s.zip'=>'Zip','s.town'=>'Town','c.code'=>'CountryCode','s.phone'=>'Phone','s.siren'=>'ProfId1','s.siret'=>'ProfId2','s.ape'=>'ProfId3','s.idprof4'=>'ProfId4','s.code_compta'=>'CustomerAccountancyCode','s.code_compta_fournisseur'=>'SupplierAccountancyCode','s.tva_intra'=>'VATIntra','f.rowid'=>"InvoiceId",'f.facnumber'=>"InvoiceRef",'f.type'=>"Type",'f.datec'=>"InvoiceDateCreation",'f.datef'=>"DateInvoice",'f.date_lim_reglement'=>"DateDue",'f.total'=>"TotalHT",'f.total_ttc'=>"TotalTTC",'f.tva'=>"TotalVAT",'none.rest'=>'Rest','f.paye'=>"InvoicePaid",'f.fk_statut'=>'InvoiceStatus','f.note_private'=>"NotePrivate",'f.note_public'=>"NotePublic",'f.fk_user_author'=>'CreatedById','uc.login'=>'CreatedByLogin','f.fk_user_valid'=>'ValidatedById','uv.login'=>'ValidatedByLogin','pj.ref'=>'ProjectRef','p.rowid'=>'PaymentId','p.ref'=>'PaymentRef','p.amount'=>'AmountPayment','pf.amount'=>'AmountPaymentDistributedOnInvoice','p.datep'=>'DatePayment','p.num_paiement'=>'PaymentNumber','pt.code'=>'CodePaymentMode','pt.libelle'=>'LabelPaymentMode','p.note'=>'PaymentNote','p.fk_bank'=>'IdTransaction','ba.ref'=>'AccountRef');
		$this->export_TypeFields_array[$r]=array('s.rowid'=>'Numeric','s.nom'=>'Text','s.address'=>'Text','s.zip'=>'Text','s.town'=>'Text','c.code'=>'Text','s.phone'=>'Text','s.siren'=>'Text','s.siret'=>'Text','s.ape'=>'Text','s.idprof4'=>'Text','s.code_compta'=>'Text','s.code_compta_fournisseur'=>'Text','s.tva_intra'=>'Text','f.rowid'=>"Numeric",'f.facnumber'=>"Text",'f.type'=>"Numeric",'f.datec'=>"Date",'f.datef'=>"Date",'f.date_lim_reglement'=>"Date",'f.total'=>"Numeric",'f.total_ttc'=>"Numeric",'f.tva'=>"Numeric",'none.rest'=>'NumericCompute','f.paye'=>"Boolean",'f.fk_statut'=>'Status','f.note_private'=>"Text",'f.note_public'=>"Text",'f.fk_user_author'=>'Numeric','uc.login'=>'Text','f.fk_user_valid'=>'Numeric','uv.login'=>'Text','pj.ref'=>'Text','p.amount'=>'Numeric','pf.amount'=>'Numeric','p.rowid'=>'Numeric','p.ref'=>'Text','p.datep'=>'Date','p.num_paiement'=>'Numeric','p.fk_bank'=>'Numeric','p.note'=>'Text','pt.code'=>'Text','pt.libelle'=>'text','ba.ref'=>'Text');
		$this->export_entities_array[$r]=array('s.rowid'=>"company",'s.nom'=>'company','s.address'=>'company','s.zip'=>'company','s.town'=>'company','c.code'=>'company','s.phone'=>'company','s.siren'=>'company','s.siret'=>'company','s.ape'=>'company','s.idprof4'=>'company','s.code_compta'=>'company','s.code_compta_fournisseur'=>'company','s.tva_intra'=>'company','pj.ref'=>'project','p.rowid'=>'payment','p.ref'=>'payment','p.amount'=>'payment','pf.amount'=>'payment','p.datep'=>'payment','p.num_paiement'=>'payment','pt.code'=>'payment','pt.libelle'=>'payment','p.note'=>'payment','f.fk_user_author'=>'user','uc.login'=>'user','f.fk_user_valid'=>'user','uv.login'=>'user','p.fk_bank'=>'account','ba.ref'=>'account');
		$this->export_special_array[$r]=array('none.rest'=>'getRemainToPay');
		$this->export_dependencies_array[$r]=array('payment'=>'p.rowid', 'none.rest'=>array('f.rowid','f.total_ttc')); // To add unique key if we ask a field of a child to avoid the DISTINCT to discard them
		$keyforselect='facture'; $keyforelement='invoice'; $keyforaliasextra='extra';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'societe as s';
		if (empty($user->rights->societe->client->voir)) $this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'societe_commerciaux as sc ON sc.fk_soc = s.rowid';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'c_country as c on s.fk_pays = c.rowid,';
		$this->export_sql_end[$r] .=' '.MAIN_DB_PREFIX.'facture as f';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'projet as pj ON f.fk_projet = pj.rowid';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'user as uc ON f.fk_user_author = uc.rowid';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'user as uv ON f.fk_user_valid = uv.rowid';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'facture_extrafields as extra ON f.rowid = extra.fk_object';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'paiement_facture as pf ON pf.fk_facture = f.rowid';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'paiement as p ON pf.fk_paiement = p.rowid';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'c_paiement as pt ON pt.id = p.fk_paiement';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'bank as b ON b.rowid = p.fk_bank';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'bank_account as ba ON ba.rowid = b.fk_account';
		$this->export_sql_end[$r] .=' WHERE f.fk_soc = s.rowid';
		$this->export_sql_end[$r] .=' AND f.entity IN ('.getEntity('facture',1).')';
		if (isset($user) && empty($user->rights->societe->client->voir)) $this->export_sql_end[$r] .=' AND sc.fk_user = '.$user->id;
		$r++;
	}


	/**
	 *		Function called when module is enabled.
	 *		The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *		It also creates data directories
	 *
     *      @param      string	$options    Options when enabling module ('', 'newboxdefonly', 'noboxes')
	 *      @return     int             	1 if OK, 0 if KO
	 */
	function init($options='')
	{
		global $conf,$langs;

		// Remove permissions and default values
		$this->remove($options);

		//ODT template
		$src=DOL_DOCUMENT_ROOT.'/install/doctemplates/invoices/template_invoice.odt';
		$dirodt=DOL_DATA_ROOT.'/doctemplates/invoices';
		$dest=$dirodt.'/template_invoice.odt';

		if (file_exists($src) && ! file_exists($dest))
		{
			require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
			dol_mkdir($dirodt);
			$result=dol_copy($src,$dest,0,0);
			if ($result < 0)
			{
				$langs->load("errors");
				$this->error=$langs->trans('ErrorFailToCopyFile',$src,$dest);
				return 0;
			}
		}

		$sql = array(
				"DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = '".$this->const[0][2]."' AND type = 'invoice' AND entity = ".$conf->entity,
				"INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('".$this->const[0][2]."','invoice',".$conf->entity.")"
		);

		return $this->_init($sql,$options);
	}
}
