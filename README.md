# moodle-up1offlinequiz2apogee

- Installation


	* Se placer dans le dossier /local de votre moodle
	
	* taper la commande suivante :
	
		git clone https://github.com/UnivParis1/moodle-up1offlinequiz2apogee up1offlinequiz2apogee
		
	* Créer le repertoire offlinequiz2Apogee dans le dossier moodledata
	
	
Ce plugin est exclusivement compatible avec la structure de l'organisation des formations des plateformes Moodle de l'université Panthéon-Sorbonne. 

- Ajout d'un lien au module offlinequiz 

Il est toutefois possible de l'utiliser dans son usage principal en ajoutant ce qui suit dans le fichier report.php du module offignequiz.

$urlApogee = $CFG->wwwroot.'/local/up1offlinequiz2apogee/upload_form.php?courseid='.$course->id.'&offlinequizid='. $offlinequiz->id;

echo '<a href="'.$urlApogee.'" target="_blank">Exporter les résultats dans un fichier d\'import APOGEE</a>';
