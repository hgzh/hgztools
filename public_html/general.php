<?php
	/**
	 * ##### general.php #####
	 * Allgemeine Klassen und Methoden
	 *
	 */
	error_reporting(E_ALL);
		
	/**
	 * ##### CLASS HtmlPage CLASS #####
	 * Klasse für Seitenaufbau
	 */
	class HtmlPage {
		
		/**
		 * [string]
		 * angezeigter Seiteninhalt
		 */
		protected static $content = '';
		
		/**
		 * [string]
		 * Auszeichnungen im Kopfbereich
		 */
		private $head = '';
		
		/**
		 * [string]
		 * Definition der Navigationsleiste
		 */
		private $navigation = '';
		
		/**
		 * [string]
		 * Systemnachricht(en)
		 */
		private $message = '';
		
		/**
		 * [string]
		 * Auszeichnungen im Fußbereich
		 */
		private $foot = '';

		/**
		 * [SplStack]
		 * Stack, der zum Schließen geöffneter HTML-Tags benötigt wird
		 */		
		private $stack;

		/**
		 * [int]
		 * Anzahl der Elemente im Stack
		 */		
		private $stackNo;
		
        /**
         * Klassenkonstruktor
		 * Initialisiert die Ausgabevariablen mit Standardtext
		 *
		 * @parameter
		 * - title : optionaler Seitentitel
		 *
		 */
		public function __construct($title = '') {			
			// gesamten head-Bereich innerhalb des <html>-Tags setzen
			$this->head .= '<!DOCTYPE html>';
			$this->head .= '<html>';
			$this->head .= '<head>';
			$this->head .= '<meta http-equiv="Content-Type" content="text/html" charset="UTF-8">';
			$this->head .= '<link rel="stylesheet" type="text/css" href="//tools.wmflabs.org/hgztools/main.css">';
			$this->head .= '<title>' . $title . '</title>';
			$this->head .= '</head>';
			$this->head .= '<body>';
			$this->head .= '<div class="iw-wrapper">';
			$this->head .= '<div class="iw-headline">' . $title . '</div>';
			
			// footer setzen, Dokument beenden
			$this->foot .= '</div><div class="iw-footer">';
			$this->foot .= '<a href="https://tools.wmflabs.org/hgztools">hgztools</a> powered by <a href="https://wikitech.wikimedia.org/wiki/Portal:Toolforge">Toolforge</a>.';
			$this->foot .= '</div></div>';
			$this->foot .= '</body>';
			$this->foot .= '</html>';
			
			$this->stack = new \SplStack;
		}
		
		/**
		 * erstellt die Navigationsleiste.
		 *
		 * @parameter
		 * - navInfo    : assoziatives Array der Form 'Anzeigetext' => 'URL'
		 *
		 */
		public function setNavigation(array $navInfo) {			
			$k1 = ''; // [string] Key 1
			$v1 = ''; // [string] Value 1
			
			$this->navigation  = '<div class="iw-navigation">';
			$this->navigation .= '<ul class="iw-navigation">';
			foreach ($navInfo as $k1 => $v1) {
				$this->navigation .= '<li><a href="' . $v1 . '" title="' . $k1 . '">' . $k1 . '</a></li>';
			}
			$this->navigation .= '</ul>';
			$this->navigation .= '</div>';
		}
		
		/**
		 * setzt den benutzerdefinierten Inhaltsbereich
		 *
		 * @parameter
		 * - content : Seiteninhalt
		 */
		public function setContent($content) {
			self::$content .= $content;
		}
		
		/**
		 * setzt eine Systemnachricht an die dafür vorgesehene Position mit entsprechendem Styling.
		 * 
		 * @parameter
		 * - id       : Kennung der Systemnachricht
		 * - endAfter : Seite nach der Nachricht beenden
		 */
		public function setMessage($message, $endAfter = false) {
			$this->message .= '<div class="iw-info">';
			$this->message .= $message;
			$this->message .= '</div>';
			
			if ($endAfter == true) {
				$this->closeBlock(100);
				$this->output();
				die;
			}
		}
		
		/**
		 * fügt ein HTML-Element mit der Möglichkeit ein, weitere darinliegende Elemente zu definieren.
		 *
		 * @parameter
		 * - tag   : Name des Tags
		 * - class : Klassenangaben
		 * - style : CSS-Style-Angaben
		 */
		public function openBlock($tag, $class = '', $style = '') {
			self::$content .= '<' . $tag;
			if (isset($class) && $class != '') {
				self::$content .= ' class="' . $class . '"';
			}
			if (isset($style) && $style != '') {
				self::$content .= ' style="' . $style . '"';
			}
			self::$content .= '>';
			$this->stack->push($tag);
			$this->stackNo++;
		}

		/**
		 * beendet ein mit openBlock() geöffnetes Element. Diese Funktion benutzt einen Stack, das zuletzt
		 * geöffnete Tag wird als erstes wieder geschlossen.
		 *
		 * @parameter
		 * - nr : Anzahl der mit einem Aufruf zu schließenden Tags
		 */
		public function closeBlock($nr = 1) {
			$tag = ''; // [string] Tag aus dem Stack
			$i   = ''; // [value] Hochzählen
			
			for ($i = 0; $i < $nr; $i++) {
				if ($this->stackNo == 0) {
					break;
				}
				$tag = $this->stack->pop();
				$this->stackNo = $this->stackNo - 1;
				self::$content .= '</' . $tag . '>';
			}
		}

		/**
		 * fügt ein HTML-Element ohne Möglichkeit weiter Definition ein.
		 *
		 * @parameter
		 * - tag     : Name des Tags
		 * - content : Inhalt des Tags
		 * - class   : Klassenangaben
		 * - style   : CSS-Style-Angaben
		 */		
		public function addInline($tag, $content, $class = '', $style = '') {
			self::$content .= '<' . $tag;
			if (isset($class) && $class != '') {
				self::$content .= ' class="' . $class . '"';
			}
			if (isset($style) && $style != '') {
				self::$content .= ' style="' . $style . '"';
			}
			self::$content .= '>';
			self::$content .= $content;
			self::$content .= '</' . $tag . '>';
		}
		
		/**
		 * fügt beliebigen HTML-Code an der aktuellen Position ein
		 *
		 * @parameter
		 * - code : einzufügender Code
		 */
		public function addHTML($code) {
			self::$content .= $code;
		}
		
		/**
		 * gibt die komplette HTML-Seite aus
		 */
		public function output() {
			echo $this->head;
			echo $this->navigation;
			if ($this->message != '') {
				echo '<div class="iw-content">';
				echo $this->message;
				echo '</div>';
			}
			echo self::$content;
			echo $this->foot;
		}
		
	}
	
	/**
	 * ##### CLASS HtmlForm CLASS #####
	 * Klasse für HTML-Form-Elemente
	 *
	 * @erweitert
	 * - HtmlPage
	 */
	class HtmlForm extends HtmlPage {
		
		/**
		 * [string]
		 * Inhalt des HtmlForms
		 */
		private $form = '';
		
		/**
		 * Klassenkonstruktor
		 *
		 * @parameter
		 * - action : Zielseite und -parameter
		 * - method : HTTP-Request-Methode
		 */
		public function __construct($action, $method) {
			$this->form = '<form action="' . $action . '" method="' . $method . '">';
		}
		
		/**
		 * fügt ein Beschriftungsfeld hinzu
		 *
		 * @parameter
		 * - for  : Name des beschrifteten Feldes
		 * - text : Beschriftungstext
		 */
		public function addLabel($for, $text) {
			$this->form .= '<label for="' . $for . '">' . $text . ': </label>';
		}
		
		/**
		 * fügt ein Optionselement hinzu
		 *
		 * @parameter
		 * - name  : Bezeichner
		 * - value : Wert des Elements
		 * - text  : Beschriftung
		 * - corr  : korrespondierende Variable
		 * - break : Umbruch nach Element
		 */
		public function addRadio($name, $value, $text, $corr, $break = true) {
			$this->form .= '<input type="radio" ';
			$this->form .= 'name="' . $name . '" value="' . $value . '"';
			if( $value == $corr ) {
				$this->form .= ' checked="checked" ';
			}
			$this->form .= '/> ' . $text;
			if( $break == true ) {
				$this->form .= '<br />';
			}
		}
		
		/**
		 * fügt ein Auswahlelement hinzu
		 *
		 * @parameter
		 * - name  : Bezeichner
		 * - value : Wert des Elements
		 * - text  : Beschriftung
		 * - corr  : korrespondierende Variable
		 * - break : Umbruch nach Element
		 */
		public function addCheckbox($name, $value, $text, $corr, $break = true) {
			$this->form .= '<input type="checkbox" ';
			$this->form .= 'name="' . $name . '" value="' . $value . '"';
			if( $value == $corr ) {
				$this->form .= ' checked="checked" ';
			}
			$this->form .= '/> ' . $text;
			if( $break == true ) {
				$this->form .= '<br />';
			}
		}
		
		/**
		 * fügt ein Eingabefeld (String) hinzu
		 *
		 * @parameter
		 * - name      : Bezeichner
		 * - value     : Voreingetragener Text
		 * - info      : zusätzliche Beschriftung
		 * - maxlength : maximal erlaubte Zeichenanzahl bei Eingabe
		 * - required  : benötigt, um Formular absenden zu können
		 * - type      : Typ des Eingabefelds (z.B. text, password)
		 */
		public function addInput($name, $value, $info = '', $maxlength = 0, $required = false, $type='text') {
			$this->form .= '<input type="' . $type . '" ';
			$this->form .= 'name="' . $name . '" id="' . $name . '" value="' . $value . '"';
			if( $maxlength != 0 ) {
				$this->form .= ' maxlenghth="' . $maxlength . '" ';
			}
			if( $required == true ) {
				$this->form .= ' required="required" ';
			}
			$this->form .= '/>';
			if( $info != '' ) {
				$this->form .= ' <span class="iw-info">' . $info . '</span>';
			}
		}
		
		/**
		 * fügt einen Textbereich (textarea) hinzu.
		 *
		 * @parameter
		 * - name      : Bezeichner
		 * - value     : Voreingetragener Text
		 * - info      : zusätzliche Beschriftung
		 * - maxlength : maximal erlaubte Zeichenanzahl bei Eingabe
		 * - required  : benötigt, um Formular absenden zu können
		 * - type      : Typ des Eingabefelds (z.B. text, password)
		 */
		public function addTextarea($name, $value, $info = '', $rows = 3, $cols = 20, $required = false) {
			$this->form .= '<textarea name="' . $name . '" id="' . $name . '" value="' . $value . '"';
			$this->form .= ' rows="' . $rows . '" cols="' . $cols . '"';
			if( $required == true ) {
				$this->form .= ' required="required" ';
			}
			$this->form .= '/>';
			if( $info != '' ) {
				$this->form .= ' <span class="iw-info">' . $info . '</span>';
			}
		}
		
		/**
		 * fügt einen Button hinzu
		 *
		 * @parameter
		 * - type  : Buttontyp
		 * - value : Beschriftung
		 */
		public function addButton($type, $value) {
			$this->form .= '<input type="' . $type . '" value="' . $value . '" />';
		}
		
		/**
		 * fügt an aktueller Stelle beliebigen Code ein
		 *
		 * @parameter
		 * - code  : einzufügender Code
		 */
		public function addHTML($code) {
			$this->form .= $code;
		}
		
		/**
		 * beendet das Formular gibt dessen kompletten HTML-Code zurück
		 *
		 * @parameter
		 * - return : wenn true, wird Code in Variable zurückgegeben, ansonsten in content des HtmlPage-Objektes
		 *
		 * @rückgabe
		 * - HTML-Code
		 */
		public function output($return = false) {
			$this->form .= '</form>';
			if ($return == false) {
				parent::$content .= $this->form;
			} else {
				return $this->form;
			}
		}
		
	}
	
	/**
	 * ##### CLASS Database CLASS #####
	 * Klasse für Datenbankfunktionen
	 *
	 * @erweitert
	 * - mysqli
	 */
	class Database extends mysqli {
		
		/**
		 * Verbindung mit Datenbank herstellen.
		 *
		 * @parameter
		 * - database    : Name der Datenbank
		 *
		 */
		public function replicaConnect($database) {
			
			$mycnf = parse_ini_file( "/data/project/hgztools/replica.my.cnf" );
			$cluster = ( preg_match( '/[-_]p$/', $database ) ) ? substr( $database, 0, -2 ) : $database;
			parent::connect($cluster . '.analytics.db.svc.eqiad.wmflabs', $mycnf['user'], $mycnf['password']);
			unset($mycnf);
			
			if( $this->connect_error ) {
				die( '<p><strong>Database server login failed.</strong> '
				. ' This is probably a temporary problem with the server and will be fixed soon. '
				. ' The server returned error code ' . $this->connect_errno . '.</p>' );
			}
			
			$res = $this->select_db(str_replace('-', '_', $database));
			
			if( $res === false ){
				die( '<p><strong>Database selection failed.</strong> '
				. ' This is probably a temporary problem with the server and will be fixed soon.</p>' );
			}
			
		}

		/**
		 * Datenbanknamen ermitteln
		 *
		 * @parameter
		 * - lang      : Sprache
		 * - project   : Projekt
		 * - separator : Separator
		 *
		 */		
		public static function getName($lang, $project, $separator = '-') {
			if ($project == 'wikipedia') {
				$project = 'wiki';
			} elseif ($project == 'wikimedia') {
				$project = 'wiki';
			} elseif($project == 'wikidata') {
				$project = 'wiki';
				$lang = 'wikidata';
			}
			return $lang . $project . $separator . 'p';
		}
		
		/**
		 * liest alle Ergebnisse einer Datenbankabfrage aus und gibt sie als Array zurück.
		 *
		 * @parameter
		 * - query : Query-Objekt
		 *
		 * @rückgabe
		 * - Ergebnisarray 
		 */
		public static function fetchResult($query) {   
		    $array = [];
   
			if ($query instanceof mysqli_stmt) {
				$query->store_result();
			   
				$variables = [];
				$data = [];
				$meta = $query->result_metadata();
			   
				while ($field = $meta->fetch_field()) {
					$variables[] = &$data[$field->name];
				}
			   
				call_user_func_array([$query, 'bind_result'], $variables);
			   
				$i = 0;
				while ($query->fetch()) {
					$array[$i] = [];
					foreach ($data as $k => $v)
						$array[$i][$k] = $v;
					$i++;
				}
			} elseif ($query instanceof mysqli_result) {
				while($row = $query->fetch_assoc()) {
					$array[] = $row;
				}
			}
			return $array;
		}
		
		/**
		 * Bezeichnung des Namensraums nach dessen Nummer ermitteln
		 *
		 * @parameter
		 * - nr         : Namensraumnummer
		 * - urlencoded : URL-kodierte Namen zurückgeben
		 *
		 */		
		public static function getNsNameFromNr($nr, $urlencoded = true) {
			$ns = 	[	0 => '',
						1 => 'Talk:',
						2 => 'User:',
						3 => 'User_talk:',
						4 => 'Project:',
						5 => 'Project_talk:',
						6 => 'File:',
						7 => 'File_talk:',
						8 => 'MediaWiki:',
						9 => 'MediaWiki_talk:',
						10 => 'Template:',
						11 => 'Template_talk:',
						12 => 'Help:',
						13 => 'Help_talk:',
						14 => 'Category:',
						15 => 'Category_talk:'
					];
			
			$name = $ns[$nr];
			
			if ($urlencoded == true) {
				return $name;
			} else {
				return str_replace('_', ' ', $name);
			}
		}
		
	}
	
	/**
	 * ##### CLASS RequestValidator CLASS #####
	 * Klasse für Anfragenvalidierung
	 *
	 */
	class RequestValidator {
		
		/**
		 * [array]
		 * allowed params
		 */
		private $allowed = [];
		
		/**
		 * validates one single parameter
		 *
		 */
		private function validateSingleParam($name, $value) {
			$ret = '';
			
			if (!isset($this->allowed[$name])) {
				return false;
			}
			
			if ($value == '' && isset($this->allowed[$name]['default']) && $this->allowed[$name]['default'] != '') {
				$ret = $this->allowed[$name]['default'];
			} else {
				$ret = $value;
			}
			
			if ($this->allowed[$name]['lcase'] == true) {
				$ret = strtolower($ret);
			}
			
			$ret = htmlspecialchars($ret);
			
			if (isset($this->allowed[$name]['pattern']) && $this->allowed[$name]['pattern'] != '' && $ret != '') {
				if (!preg_match($this->allowed[$name]['pattern'], $ret)) {
					return false;
				}
			}
			
			return $ret;
		}
		
		/**
		 * adds a new allowed parameter
		 *
		 */
		public function addAllowed($type, $name, $default = '', $pattern = '', $required = false, $lcase = true) {
			if ($type == 'GET' || $type == 'POST' ) {
				$this->allowed[$name]['type']     = $type;
				$this->allowed[$name]['default']  = $default;
				$this->allowed[$name]['pattern']  = $pattern;
				$this->allowed[$name]['required'] = $required;
				$this->allowed[$name]['lcase']    = $lcase;
				$this->allowed[$name]['touched']  = false;
				return true;
			} else {
				return false;
			}
		}

		/**
		 * validates request parameters and returns the valid ones
		 *
		 */		
		public function getParams() {
			$ret = [];
			$val = '';
			
			foreach ($this->allowed as $k1 => &$v1) {
				foreach ($_GET as $k2 => $v2) {
					if ($k1 == $k2 && $v1['type'] == 'GET') {
						$val = $this->validateSingleParam($k1, $v2);
						$v1['touched'] = true;
					}
				}
				
				foreach ($_POST as $k2 => $v2) {
					if ($k1 == $k2 && $v1['type'] == 'POST') {
						$val = $this->validateSingleParam($k1, $v2);
						$v1['touched'] = true;
					}
				}
				
				if ($v1['touched'] == false) {
					$val = $this->validateSingleParam($k1, '');
					$v1['touched'] = true;
				}
				
				$ret[$k1] = $val;
				$v1['value'] = $val;
			}
			unset($v1);
			
			return $ret;
		}
		
		/**
		 * checks if all required params are defined
		 *
		 */		

		public function allRequiredDefined() {
			$all = true;
			
			foreach ($this->allowed as $k1 => $v1) {
				if ($v1['required'] == true && $v1['value'] == '') {
					$all = false;
				}
			}
			
			return $all;
		}
		
	}
	
	/**
	 * ##### CLASS Hgz CLASS #####
	 * Klasse für Hilfsfunktionen
	 *
	 */
	class Hgz {
		
		protected $page;
		
		protected $db;
		
		protected $rq;
		
		/**
		 * creates a link to a wiki page
		 *
		 */
		public static function buildWikilink($lang, $project, $page, $title = '', $urlquery = '') {
			$ret  = '<a href="https://' . $lang . '.' . $project . '.org/wiki/' . $page;
			if ($urlquery != '') {
				$ret .= '?' . $urlquery;
			}
			$ret .= '" title="';
			if ($title != '') { $ret .= $title; } else { $ret .= $page; }
			$ret .= '">';
			if ($title != '') { $ret .= $title; } else { $ret .= $page; }
			$ret .= '</a>';
			
			return $ret;
		}

		/**
		 * returns the shortcut for wmf projects
		 *
		 */		
		public static function getProjectShortcut($project, $lang) {
			
			switch (strtolower($project)) {
				case 'wikipedia'   : $project = 'w'; break;
				case 'wikidata'    : $project = 'd'; break;
				case 'wikisource'  : $project = 's'; break;
				case 'wikivoyage'  : $project = 'voy'; break;
				case 'wikiversity' : $project = 'v'; break;
				case 'wikibooks'   : $project = 'b'; break;
				case 'wiktionary'  : $project = 'wikt'; break;
				case 'wikiquote'   : $project = 'q'; break;
				case 'wikinews'    : $project = 'n'; break;
			}
			
			switch (strtolower($lang)) {
				case 'commons' : 
					$project = 'c';
					$lang = '';
					break;
				case 'meta' : 
					$project = 'meta';
					$lang = '';
					break;
			}
			
			$ret = $project . ':';
			if ($lang) {
				$ret .= $lang . ':';
			}
			
			return $ret;
			
		}
		
	}
	
?>
