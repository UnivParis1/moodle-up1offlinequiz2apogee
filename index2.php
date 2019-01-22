<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('../../lib/accesslib.php');
require_once('locallib.php');
require_login();

ini_set('max_execution_time', 600);
ini_set('memory_limit', '2048M');
$idcategorie=0;
$url = new moodle_url('/local/up1offlinequiz2apogee/index.php');
$PAGE->set_url($url);
$context = context_user::instance($USER->id);
$PAGE->set_context($context);
$PAGE->requires->css(new moodle_url('/local/up1offlinequiz2apogee/css/up1offlinequiz2apogee.css'));

/**
 * vérification que l'utilisateur est un administrateur
 */
if (is_userauthorized($USER->id) || is_siteadmin()) {
	$annee = 0;
	$courseid=0;
	$offlinequizid=0;
	
	if (isset($_REQUEST['annee'])) $annee = $_REQUEST['annee'];
	if (isset($_REQUEST['courseid'])) $courseid = $_REQUEST['courseid'];
	if (isset($_REQUEST['offlinequizid'])) $offlinequizid = $_REQUEST['offlinequizid'];
	
	$PAGE->set_pagelayout('report');
	if (is_siteadmin()) admin_externalpage_setup('local_up1offlinequiz2apogee', '', null, '', array('pagelayout'=>'report'));

	$PAGE->set_heading(get_string('heading', 'local_up1offlinequiz2apogee'));
	$PAGE->set_heading(get_string('heading', 'local_up1offlinequiz2apogee'));
	$PAGE->set_title(get_string('title_index', 'local_up1offlinequiz2apogee'));
	
	if ($annee!=0) {
		$select_annee = "SELECT name from {course_categories} where id=? ;";
		$obj = $DB->get_record_sql($select_annee, array($annee));
		$libelle_annee = '';
		if (!empty($obj->name)) $libelle_annee = $obj->name;
	}
	echo $OUTPUT->header();
	echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
	///// TEST
	$chaine ="ADM : Admis	ADMH : Admis par Homologation - DÈcret 2/8/1960	ADMI : Admissible	ADMV : Admis par VAE	AJ : AjournÈ	AVAA : Attente validation de l'annÈe antÈrieure	DEF : DÈfaillant	
ADM : Admis	ADMI : Admissible	ADMV : Admis par VAE	AJ : AjournÈ	AJAC : AjournÈ mais accËs autorisÈ ‡ Ètape sup.	AVAA : Attente validation de l'annÈe antÈrieure	DEF : DÈfaillant	
ABI : Absent	ABJ : Absence justifiÈe	ADM : Admis	ADMI : Admissible	AJ : AjournÈ	CMP : ValidÈ par compensation	COMP : Compensable	DEF : DÈfaillant	NCOM : Non compensable	NVAL : Non validÈ	VAL : ValidÈ	
ABI : Absent	ABJ : Absence justifiÈe	DEF : DÈfaillant	DIS : Dispense examen	
							
XX-APO_COLONNES-XX
apoL_a01_code	Type Objet	Code	Version	AnnÈe	Session	Admission/AdmissibilitÈ	Type RÈs.			Etudiant	NumÈro
apoL_a02_nom											Nom
apoL_a03_prenom											PrÈnom
apoL_a04_naissance									Session	AdmissibilitÈ	Naissance
APO_COL_VAL_DEB
apoL_c0001	EPR	B1010714E1		2018	1	1	N	B1010714E1 - Stat	1	1	Note
apoL_c0002	EPR	B1010714E1		2018	1	1	B		1	1	BarËme
APO_COL_VAL_FIN								  	
							
XX-APO_VALEURS-XX					  	
		";
	
	
	$pattern = '`\{APO_COL_VAL_DEB\}(.*)\{/APO_COL_VAL_FIN\}`U';
	preg_match_all($pattern, $chaine, $matches);
	foreach ($matches as $match) {
		echo $match[1];
	}
	$exp = explode('
',$chaine);
	$t = recherche_champs_a_remplir($exp);
}
echo $OUTPUT->box_end();
echo $OUTPUT->footer(); 
