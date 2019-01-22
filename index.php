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
	if (isset($_REQUEST['annee'])) $annee = $_REQUEST['annee'];
	
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
	
	$sql= "SELECT id, name from mdl_course_categories where parent=0 and name like'%20%';";
	$cats = $DB->get_records_sql($sql);
	$select = '<select name="annee" id="annee">';
	if ($annee == 0) $select .= '<option value="0" selected>--</option>'; else $select .= '<option value="0">--</option>';
	foreach($cats as $i=>$row) {
		if ($annee == $row->id) $select .= '<option value="'.$row->id.'" selected>'.$row->name.'</option>'; else $select .= '<option value="'.$row->id.'">'.$row->name.'</option>';
	}
	$libelle_choose_cat = get_string('choose_cat', 'local_up1offlinequiz2apogee');
	$libelle_valider = get_string('ok', 'local_up1offlinequiz2apogee');
	$select .= '</select>';
$form = <<< EOF
<form action="index.php" method="GET" >
	<h3> $libelle_choose_cat $select<input type="submit" value="$libelle_valider"></h3>
</form>
EOF;
	echo $form; // insertion du formulaire dans la page
    $cpt =0;
    echo '<h3>'.get_string('liste_EPI', 'local_up1offlinequiz2apogee').'</h3>';
	if (!empty($annee)) {
        $data = getOfflinequizzes($annee);
        echo '<p style="color:green">Total des tests hors lignes: '.$data['nb_examens'].'</p>';
        echo '<p style="color:green">Total des copies corrigées hors lignes: '.$data['nb_copies'].'</p>';

		$table = new html_table();

		foreach ($data['UFRS'] as $i=>$row) {
            $table->head = array(
                'EPI',
                'quizz name',
                'intro',
                'Noter'
            );
            $table->data = $row['data'];
            echo '<h4>'.$row['name'].'</h4>';
            echo html_writer::table($table);
            $cpt+=count($row['data']);
		}

	}
	
}
echo $OUTPUT->box_end();
echo $OUTPUT->footer(); 
