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
	$annee = 0;
	$courseid=0;
	$offlinequizid=0;
	
	if (isset($_REQUEST['annee'])) $annee = $_REQUEST['annee'];
	if (isset($_REQUEST['courseid'])) $courseid = $_REQUEST['courseid'];
	if (isset($_REQUEST['offlinequizid'])) $offlinequizid = $_REQUEST['offlinequizid'];
	if (!$cm = get_coursemodule_from_instance("offlinequiz", $offlinequizid, $courseid)) {
            print_error('invalidcoursemodule');
    	}
    	if (!$course = $DB->get_record('course', array('id' => $courseid))) {
            print_error('invalidcourseid');
    	}
	require_login($course, false, $cm);
	$context = context_module::instance($cm->id);
	require_capability('mod/offlinequiz:viewreports', $context);
	
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
	$form = <<< EOF
	<h3>Ajouter le fichier TXT provenant d'Apogee</h3>
	<form method="POST" action="upload_form.php" enctype="multipart/form-data">
	<input type="hidden" name="annee" value ="$annee">
	<input type="hidden" name="courseid" value ="$courseid">
	<input type="hidden" name="offlinequizid" value ="$offlinequizid">
		<table width="100%" border="0" cellspacing="0" cellpadding="5">
			<tr>
				<td width="61%"><input type="file" name="fichier">
				<input type="hidden" name="MAX_FILE_SIZE" value="900000" />
				<input type="submit" name="envoyer" value="Envoyer le fichier">
				</td>
				<td width="39%"></td>
			</tr>
		</table>
	</form>
EOF;
	
	if(isset($_FILES['fichier'])) {
		$dossier = $CFG->dataroot . '/offlinequiz2Apogee/';
		$fichier = basename($_FILES['fichier']['name']);
		if(move_uploaded_file($_FILES['fichier']['tmp_name'], $dossier . $fichier)) {
			echo "<div class='BoiteJ'>Upload effectué avec succés !</div>";
			echo '
				<form method="POST" action="remplir.php" enctype="multipart/form-data">
					<input type="hidden" name="annee" value ="'.$annee.'">
					<input type="hidden" name="courseid" value ="'.$courseid.'">
					<input type="hidden" name="offlinequizid" value ="'.$offlinequizid.'">
					<input type="hidden" name="fiename" value="'.$dossier . $fichier.'">
					<input type="submit" name="envoyer" value="Remplir et télécharger le fichier">
				</form>';
							
		} else  {
			echo "<div class='BoiteJ'>Echec de l\'upload !</div>";
		} 
	} else {
		echo $form;
	}


echo $OUTPUT->box_end();
echo $OUTPUT->footer(); 
