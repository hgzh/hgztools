<?php

	error_reporting(E_ALL);
		
	/**
	 * CLASS HtmlPage
	 * creating a single web page
	 */
	class HtmlPage {
		
		/**
		 * page content
		 */
		protected static $content = '';
		
		/**
		 * head contents
		 */
		private $head = '';
		
		/**
		 * navigation bar
		 */
		private $navigation = '';
		
		/**
		 * user notification messages
		 */
		private $message = '';
		
		/**
		 * footer contents
		 */
		private $foot = '';

		/**
		 * stack for easy container handling
		 */		
		private $stack;

		/**
		 * number of items in $stack
		 */		
		private $stackNo;
		
        /** __construct()
         * standard initializations
		 * - $title : document title
		 */
		public function __construct($title = '') {			
			// define HTML head content and begin HTML body
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
			
			// set footer and end HTML document
			$this->foot .= '</div><div class="iw-footer">';
			$this->foot .= '<a href="https://tools.wmflabs.org/hgztools">hgztools</a> powered by <a href="https://wikitech.wikimedia.org/wiki/Portal:Toolforge">Toolforge</a>.';
			$this->foot .= '</div></div>';
			$this->foot .= '</body>';
			$this->foot .= '</html>';
			
			// initialize stack
			$this->stack = new \SplStack;
		}
		
		/** setNavigation()
		 * create the navigation bar
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
	 * CLASS Database
	 * database helper functions extending mysqli functionality
	 */
	class Database extends mysqli {
		
		/** replicaConnect()
		 * initialize connection with wmcs database replicas using user credentials
		 * - $database : database name
		 */
		public function replicaConnect($database) {
			// get credentials
			$mycnf = parse_ini_file('/data/project/hgztools/replica.my.cnf');
			
			// get database cluster
			$cluster = ( preg_match( '/[-_]p$/', $database ) ) ? substr( $database, 0, -2 ) : $database;
			
			// connect to database
			parent::connect($cluster . '.analytics.db.svc.eqiad.wmflabs', $mycnf['user'], $mycnf['password']);
			
			// destroy credential information
			unset($mycnf);
			
			// fetch connecting errors
			if ($this->connect_error) {
				die( '<p><strong>Database server login failed.</strong> '
				. ' This is probably a temporary problem with the server and will be fixed soon. '
				. ' The server returned error code ' . $this->connect_errno . '.</p>' );
			}
			
			// select database
			$res = $this->select_db(str_replace('-', '_', $database));
			
			// fetch selection errors
			if ($res === false) {
				die( '<p><strong>Database selection failed.</strong> '
				. ' This is probably a temporary problem with the server and will be fixed soon.</p>' );
			}
		}

		/** refValues()
		 * commit raw data values as reference
		 * - $arr : array with values
		 */
		private function refValues($arr){
			if (strnatcmp(phpversion(), '5.3') >= 0) {
				$refs = [];
				foreach($arr as $k1 => $v1)
					$refs[$k1] = &$arr[$k1];
				return $refs;
			}
			
			// return
			return $arr;
		}
		
		/** executePreparedQuery()
		 * creates a prepared mysqli statement and executes it directly
		 * - (1)    : query string
		 * - (2)    : reference types (first parameter to mysqli::bind_param())
		 * - (3...) : references (following parameters to mysqli::bind_param())
		 */
		public function executePreparedQuery() {
			// at least 1 parameter is needed
			$numParam = func_num_args();
			if ($numParam < 1) {
				return false;
			}
			
			// get all parameters
			$parList = func_get_args();
			
			// prepare statement with text from first parameter
			$query = $this->prepare($parList[0]);
			if ($query === false) {
				return false;
			}
			
			// remove first parameter, supply the rest to mysqli::bind_param()
			unset($parList[0]);
			
			// if parameters left, supply them to mysqli::bind_param()
			if (count($parList) != 0) {
				call_user_func_array([$query, 'bind_param'], $this->refValues($parList));
			}
			
			// execute query and store result
			$query->execute();
			$query->store_result();
			
			// return mysqli_stmt object
			return $query;
		}

		/** checkSqlQueryObject()
		 * checks if the given query/result object is valid, for
		 * catching sql errors
		 * - $query : result object
		 */
		public static function checkSqlQueryObject($query) {
			// mysqli_stmt and mysqli_result are valid
			if (($query instanceof mysqli_stmt) || ($query instanceof mysqli_result)) {
				return true;
			} else {
				return false;
			}
		}

		/** getName()
		 * get wiki database name
		 * $lang      : project language
		 * $project   : project name
		 * $separator : additional separator
		 */		
		public static function getName($lang, $project, $separator = '-') {
			// get suffix
			if ($project == 'wikipedia') {
				$project = 'wiki';
			} elseif ($project == 'wikimedia') {
				$project = 'wiki';
			} elseif($project == 'wikidata') {
				$project = 'wiki';
				$lang = 'wikidata';
			}
			
			// add prefix with separator and return
			return $lang . $project . $separator . 'p';
		}
		
		/** fetchResult()
		 * fetching both mysqli_stmt and mysqli_result query results in the same way
		 * - $query : mysqli result/statement object
		 */
		public static function fetchResult($query) {   
		    $array = [];
			
			if ($query instanceof mysqli_stmt) {
				// mysqli_stmt
				
				// get statement metadata
				$query->store_result();
				$variables = [];
				$data = [];
				$meta = $query->result_metadata();
				
				// get sql result field names
				while ($field = $meta->fetch_field()) {
					$variables[] = &$data[$field->name];
				}
				
				// bind results to corresponding field names
				call_user_func_array([$query, 'bind_result'], $variables);
				
				// fetch data
				$i = 0;
				while ($query->fetch()) {
					$array[$i] = [];
					foreach ($data as $k => $v)
						$array[$i][$k] = $v;
					$i++;
				}
			} elseif ($query instanceof mysqli_result) {
				// mysqli_result
				
				// fetch all rows
				while ($row = $query->fetch_assoc()) {
					$array[] = $row;
				}
			}
			
			// return
			return $array;
		}
		
		/** getNsNameFromNr()
		 * returns namespace number from canonic name
		 * - $nr         : namespace nr
		 * - $urlencoded : replace spaces by underscores (true/false)
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
			
			// match nr with name
			$name = $ns[$nr];
			
			// apply urlencoded form
			if ($urlencoded == true) {
				return $name;
			} else {
				return str_replace('_', ' ', $name);
			}
		}
		
	}
	
	/** CLASS RequestValidator
	 * check request parameters for matching format and other specifications, apply
	 * changes to them if necessary
	 */
	class RequestValidator {
		
		/**
		 * allowed params
		 */
		private $allowed = [];
		
		/** validateSingleParam()
		 * validates one single parameter based on its attributes defined in addAllowed()
		 * - $name  : parameter name
		 * - $value : parameter value
		 */
		private function validateSingleParam($name, $value) {
			$ret = '';
			
			// is allowed parameter?
			if (!isset($this->allowed[$name])) {
				return false;
			}
			
			// if empty, apply default value
			if ($value == '' && isset($this->allowed[$name]['default']) && $this->allowed[$name]['default'] != '') {
				$ret = $this->allowed[$name]['default'];
			} else {
				$ret = $value;
			}
			
			// apply lcase transformation
			if ($this->allowed[$name]['lcase'] == true) {
				$ret = strtolower($ret);
			}
			
			// remove html special chars
			$ret = htmlspecialchars($ret);
			
			// check if parameter value matches given regexp pattern
			if (isset($this->allowed[$name]['pattern']) && $this->allowed[$name]['pattern'] != '' && $ret != '') {
				if (!preg_match($this->allowed[$name]['pattern'], $ret)) {
					return false;
				}
			}
			
			// return validated value
			return $ret;
		}
		
		/** addAllowed()
		 * adds a new allowed request parameter
		 * - $type     : http request type (GET/POST)
		 * - $name     : parameter name
		 * - $default  : default value
		 * - $pattern  : validation pattern
		 * - $required : true/false
		 * - $lcase    : always transform to lcase (true/false)
		 */
		public function addAllowed($type, $name, $default = '', $pattern = '', $required = false, $lcase = true) {
			if ($type == 'GET' || $type == 'POST' ) {
				// set attributes for allowed parameters
				$this->allowed[$name]['type']     = $type;
				$this->allowed[$name]['default']  = $default;
				$this->allowed[$name]['pattern']  = $pattern;
				$this->allowed[$name]['required'] = $required;
				$this->allowed[$name]['lcase']    = $lcase;
				$this->allowed[$name]['touched']  = false;
				return true;
			} else {
				// only GET and POST allowed
				return false;
			}
		}

		/** getParams()
		 * validates request parameters and returns the valid ones
		 */		
		public function getParams() {
			$ret = [];
			$val = '';
			
			// loop through parameters defined as allowed
			foreach ($this->allowed as $k1 => &$v1) {
				
				// HTTP GET parameters
				foreach ($_GET as $k2 => $v2) {
					if ($k1 == $k2 && $v1['type'] == 'GET') {
						// validate match
						$val = $this->validateSingleParam($k1, $v2);
						$v1['touched'] = true;
					}
				}
				
				// HTTP POST parameters
				foreach ($_POST as $k2 => $v2) {
					if ($k1 == $k2 && $v1['type'] == 'POST') {
						// validate match
						$val = $this->validateSingleParam($k1, $v2);
						$v1['touched'] = true;
					}
				}
				
				// not found in GET or POST
				if ($v1['touched'] == false) {
					$val = $this->validateSingleParam($k1, '');
					$v1['touched'] = true;
				}
				
				// set validated data
				$ret[$k1] = $val;
				$v1['value'] = $val;
			}
			unset($v1);
			
			// return
			return $ret;
		}
		
		/** allRequiredDefined()
		 * checks if all required parameters are defined in the current query
		 */		
		public function allRequiredDefined() {
			// standard
			$all = true;
			
			foreach ($this->allowed as $k1 => $v1) {
				if ($v1['required'] == true && $v1['value'] == '') {
					// found required parameter with no value
					$all = false;
				}
			}
			
			// return
			return $all;
		}
		
	}
	
	/** CLASS Hgz
	 * standard tool class
	 */
	class Hgz {
		
		/**
		 * standard page object
		 */
		protected $page;

		/**
		 * standard database object
		 */		
		protected $db;

		/**
		 * standard RequestValidator object
		 */		
		protected $rq;
		
		/** buildWikilink()
		 * creates a link to a wiki page
		 * - $lang     : project language
		 * - $project  : project name
		 * - $page     : wiki page
		 * - $title    : link title
		 * - $urlquery : additional query
		 */
		public static function buildWikilink($lang, $project, $page, $title = '', $urlquery = '') {
			// begin link
			$ret  = '<a href="https://' . $lang . '.' . $project . '.org/wiki/' . $page;
			
			// additional query
			if ($urlquery != '') {
				$ret .= '?' . $urlquery;
			}
			
			// title
			$ret .= '" title="';
			if ($title != '') { $ret .= $title; } else { $ret .= $page; }
			$ret .= '">';
			
			// text
			if ($title != '') { $ret .= $title; } else { $ret .= $page; }
			
			// finish link
			$ret .= '</a>';
			
			// return
			return $ret;
		}

		/** getProjectShortcut()
		 * returns the shortcut for wmf projects
		 * - $lang     : project language
		 * - $project  : project name
		 */		
		public static function getProjectShortcut($project, $lang) {
			
			// get standard project abbreviations
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
			
			// special hosted wikis (.wikimedia.org)
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
			
			// build return value "project:lang:"
			$ret = $project . ':';
			if ($lang) {
				$ret .= $lang . ':';
			}
			
			// return
			return $ret;
		}
		
	}
	
?>
