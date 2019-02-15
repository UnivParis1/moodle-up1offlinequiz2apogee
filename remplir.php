<?php
require_once(__DIR__ . '/../../config.php');
require_once('locallib.php');

ini_set('max_execution_time', 600);
ini_set('memory_limit', '2048M');

/**
 * vérification que l'utilisateur est un administrateur
 */

	$annee = 0;
	$courseid=0;@
	$offlinequizid=0;
	
	if (isset($_REQUEST['annee'])) $annee = $_REQUEST['annee'];
	if (isset($_REQUEST['courseid'])) $courseid = $_REQUEST['courseid'];
	if (isset($_REQUEST['offlinequizid'])) $offlinequizid = $_REQUEST['offlinequizid'];
	if (isset($_REQUEST['fiename'])) $fiename= $_REQUEST['fiename'];
        if (!$cm = get_coursemodule_from_instance("offlinequiz", $offlinequizid, $courseid)) {
            print_error('invalidcoursemodule');
        }
    	if (!$course = $DB->get_record('course', array('id' => $courseid))) {
            print_error('invalidcourseid');
    	}
	require_login($course, false, $cm);
	$context = context_module::instance($cm->id);
	require_capability('mod/offlinequiz:viewreports', $context);


	$tab_filename = explode('/',$filename);

	///// TEST
	$retour = remplir($courseid,$offlinequizid,$fiename);
    $nom_fichier = basename($fiename);
    download_send_headers($nom_fichier);
    echo $retour;
    exit;

