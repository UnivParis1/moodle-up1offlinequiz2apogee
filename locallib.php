<?php
function is_userauthorized($id) {
	global $CFG;
	if (in_array($id, $CFG->up1offlinequiz2apogeeauthorized)) return true;
	return false;
}

function getOfflinequizzes($idcat) {
	global $DB;
    $result = array();
    $data = array();
    // select UFRS
    $select_ufr = "  SELECT id, name, path 
                 FROM {course_categories}
                 WHERE depth = 3
                 AND path like ?";
    $obj_ufr =  $DB->get_records_sql($select_ufr, array('/'.$idcat.'/%'));
    $liste_offlinequiz = '0';
    $nb_examens = 0;
    $nb_copies = 0;
    foreach ($obj_ufr as $i=>$row_ufr) {
        $select = "	
			SELECT  O.id as idquiz, C.id as courseid,C.fullname as coursename, O.name as quizname, O.intro as intro , CC.path
			FROM {course} C
			INNER JOIN {offlinequiz} O on (C.id = O.course)
        	INNER JOIN {course_categories} CC on ( C.category =  CC.id ) 
        	 WHERE CC.path LIKE  ?
        	order by O.id";
        $obj = $DB->get_records_sql($select, array($row_ufr->path.'/%'));
        $i=0;
        foreach ($obj as $j=>$row) {
            $result[$i]['coursename'] = $row->coursename;
            $result[$i]['quizname'] = $row->quizname;
            $result[$i]['intro'] = $row->intro;
            $result[$i]['go'] = '<a href="upload_form.php?courseid=' . $row->courseid . '&offlinequizid=' . $row->idquiz . '&annee=' . $idcat . '">Noter</a>';
            $i++;
            $liste_offlinequiz .= ','.$row->idquiz;
        }
        $nb_examens+=count($result);
        $data['UFRS'][$row_ufr->id]['name'] = $row_ufr->name . ' ('.count($result).')';
        $data['UFRS'][$row_ufr->id]['data'] = $result;
        unset($obj);
        unset($row);
        unset($result);
    }
    // calcul du nombres de copies
    $select = " SELECT count(*) as nb
                FROM {offlinequiz_results}
                WHERE offlinequizid in ($liste_offlinequiz)";
    $obj = $DB->get_record_sql($select,array());
    if (!empty($obj->nb)) $nb_copies=$obj->nb;
    $data['nb_copies'] = $nb_copies;
    $data['nb_examens'] = $nb_examens;
	return $data;
}

function getBareme($offlinequizid) {
	global $DB;
	$select = "	
		SELECT grade 
		FROM {offlinequiz}
		WHERE id = ?
	";
	$obj = $DB->get_record_sql($select, array($offlinequizid));
	if (!empty($obj->grade)) return round($obj->grade,2);
	return '';
}

function getResult($userid,$offlinquizid,$bareme = 0) {
	global $DB;
	$select = "	
		SELECT r.sumgrades as note_total, g.sumgrades as bareme
		FROM {offlinequiz_results} as r 
		INNER JOIN {offlinequiz_groups} g on (g.id = r.offlinegroupid)
		WHERE r.userid = ?
		AND r.offlinequizid = ?
	";
	$note = '';
	$obj = $DB->get_record_sql($select, array($userid,$offlinquizid));
	if (!empty($obj->note_total)) {

        $note_total =  round($obj->note_total,2);
        $bareme_group =  round($obj->bareme,2);
        $note =  round($bareme * $note_total / $bareme_group,2);
    }

	return $note;
}

function getUseridByCodEtu ($cod_etu) {
	global $DB;
	$select = "	
		SELECT id 
		FROM {user}
		WHERE idnumber = ?
	";
	$obj = $DB->get_record_sql($select, array($cod_etu));
	if (!empty($obj->id)) return $obj->id;
	return 0;
}

function recherche_champs_a_remplir($tab) {
	$rang_note = 1;
	$rang_admission = 0;
	$rang_bareme=2;
	$debut = 0;
	for ($i=0;$i<count($tab);$i++) {
		if ($debut==0) {
			if ($tab[$i]=='APO_COL_VAL_DEB') 
				$debut = 1;
		} else {
			if ($tab[$i]=='APO_COL_VAL_FIN') {
				$debut = 0;
			} else {
				
				$exp = explode('	',$tab[$i]);
				switch ($exp[7]) {
					case 'N' : {
						$rang_note = $debut;
						$debut++;
						break;
					}
					case 'B' : {
						$rang_bareme = $debut;
						$debut++;
						break;
					}
					case 'A' : {
						$rang_admission = $debut;
						$debut++;
						break;
					}
					default: {
						break;
					}
				}
			}
		}
		
	}
	return array(
		'rang_note'=>$rang_note,
		'rang_admission'=>$rang_admission,
		'rang_bareme'=>$rang_bareme,
	);
	
}
function recherche_debut_notation($tab) {
	for ($i=0;$i<count($tab);$i++) {
		if (substr($tab[$i],0,17)=='XX-APO_VALEURS-XX') return $i;
	}
}

function retour_implode_avant($tab,$debut_notation) {
    for ($i=0;$i<$debut_notation;$i++) {

    }
}

function remplir($courseid,$offlinequizid,$filename) {
	global $DB;
	$rang= array();
	$data = file_get_contents($filename);
	// on le transforme en tableau
		$tab= explode('
',$data);

	
	// on recherche le bareme du devoir
	$bareme = getBareme($offlinequizid);
	// on cherche la position du bareme, devoir, et admission dans le remplissage
	$rang = recherche_champs_a_remplir($tab);
	$rang_note = $rang['rang_note'];
	$rang_admission =  $rang['rang_admission'];
	$rang_bareme= $rang['rang_bareme'];
	// Repéré début des notes
	$debut_notation = recherche_debut_notation($tab) +2;
	for($i=$debut_notation;$i<count($tab);$i++) {
        $ligne = explode('	', $tab[$i]);
        $userid = getUseridByCodEtu($ligne[0]);
        $note = getResult($userid, $offlinequizid,$bareme);
        //echo "$userid -  $note - $bareme<br />" ;
        if ($note<0) $note=0;
        if ($bareme && $rang_bareme) {
            if ($note!=='')
                $ligne[$rang_bareme + 3] = $bareme . "\r";
            else
                $ligne[$rang_bareme + 3] = "\r";
        }
		if ($rang_note) $ligne[ $rang_note+3 ] = $note;
		$tab[$i] = implode('	', $ligne);
		unset($ligne);
	}
	$retour= implode("\n", $tab);

	return $retour;
}
function download_send_headers($filename) {
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
}