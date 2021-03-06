<?php
	/**
	 *
	 * DEAD LINKS BY USER
	 * 
	 *
	 * 
	 * This program is free software; you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published by
	 * the Free Software Foundation; either version 2 of the License, or
	 * (at your option) any later version.
	 *
	 * This program is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with this program; if not, write to the Free Software
	 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
	 */
	
	require_once('/data/project/hgztools/public_html/general.php');
	
	class HgzDeadlinksByUser extends Hgz {
		
		// request parameters
		protected $par = [];
		
		/* __construct()
			create objects
		*/
		public function __construct() {
			// create new page object
			$this->page = new HtmlPage('Tote Links nach Benutzer');

			// create new database object
			$this->db = new Database();
			
			// create new request validator
			$this->rq = new RequestValidator();
			
			// execute tool
			$this->run();
			$this->finish();
		}
		
		/* initRequestValidator()
			define accepted url parameters
		*/
		private function initRequestValidator() {
			// user to get articles for
			$this->rq->addAllowed('GET', 'user', '', '',  true, false);
			
			$this->par = $this->rq->getParams();
		}
		
		/* printToolHead()
			output header and tool option area
		*/
		private function printToolHead() {
			$this->page->openBlock('div', 'iw-content');
			$this->page->addInline('p', 'Dieses Tool zeigt vom angegebenen Benutzer erstellte Artikel an, bei denen defekte Weblinks gefunden und gekennzeichnet worden sind.'
									. ' Dies kann eine Defekte-Weblink-Meldung auf der Diskussionsseite oder die Einordnung des Artikels in eine Defekte-Weblink-Kategorie sein.');
			
			$this->page->addInline('h2', 'Optionen');
			
			// options
			$optionForm = new HtmlForm('index.php', 'GET');
			$optionForm->addHTML('<table class="iw-nostyle">');
			
			// user name
			$optionForm->addHTML('<tr><td>');
			$optionForm->addLabel('user', 'Benutzername');
			$optionForm->addHTML('</td><td>');
			$optionForm->addInput('user', $this->par['user'], 'Name des zu analysierenden Benutzers', 0, true);
			$optionForm->addHTML('</td></tr>');
			
			// submit button
			$optionForm->addHTML('<tr><td colspan="2">');
			$optionForm->addButton('submit', 'Artikel anzeigen');
			$optionForm->addHTML('</td></tr>');
			
			$optionForm->addHTML('</table>');
			$optionForm->output();
			
			$this->page->closeBlock();
		}
		
		/* formSubmitted()
			tool action after option form has been submitted
		*/
		private function formSubmitted() {
			// open result area
			$this->page->openBlock('div', 'iw-content');
			$this->page->addInline('h2', 'Ergebnisse');
			
			// connect db
			$this->db->replicaConnect(Database::getName('de', 'wikipedia'));
			
			// build and execute query
			$t1  = 'SELECT DISTINCT p.page_title';
			$t1 .= ' FROM page p';
			$t1 .= ' INNER JOIN revision_userindex rv ON rv.rev_page = p.page_id';
			$t1 .= ' LEFT JOIN (page pd, categorylinks clpd) ON (p.page_title = pd.page_title AND clpd.cl_from = pd.page_id AND pd.page_namespace = 1)';
			$t1 .= ' LEFT JOIN categorylinks clp ON clp.cl_from = p.page_id';
			$t1 .= ' LEFT JOIN actor ac ON ac.actor_id = rv.rev_actor';
			$t1 .= ' WHERE ac.actor_name = ?';
			$t1 .= ' AND rv.rev_parent_id = 0';
			$t1 .= ' AND p.page_namespace = 0';
			$t1 .= ' AND p.page_is_redirect = 0';
			$t1 .= ' AND (clp.cl_to REGEXP "Wikipedia\:(Defekte\_Weblinks\/Ungeprüfte\_Botmarkierungen.*|Weblink\_offline)" OR clpd.cl_to = "Wikipedia:Defekte_Weblinks/Bot")';
			$t1 .= ' ORDER BY p.page_title';
			$q1 = $this->db->executePreparedQuery($t1, 's', $this->par['user']);
			
			// check for sql errors
			if (Database::checkSqlQueryObject($q1) === false) {
				$this->page->addInline('p', 'SQL Error: ' . $this->db->error, 'iw-error');
				$this->page->closeBlock();
				return;
			}
			
			$r1 = Database::fetchResult($q1);
			
			if ($q1->num_rows === 0) {
				// no results
				$this->page->addInline('p', 'Es wurden keine Ergebnisse gefunden.', 'iw-info');
			} else {
				// results found, open unordered list
				$this->page->openBlock('ul');
				
				// loop through results
				foreach ($r1 as $l1) {
					$this->page->addInline('li', parent::buildWikilink('de', 'wikipedia', $l1['page_title'], str_replace('_', ' ', $l1['page_title']))
										. ' (' . parent::buildWikilink('de', 'wikipedia', 'Diskussion:' . $l1['page_title'], 'Diskussion') . ')');
				}
				$this->page->closeBlock();
			}
			
			// close query and close result area
			$q1->close();
			$this->page->closeBlock();
		}
		
		/* finish()
			finish tool, close database connection, output page content
		*/
		private function finish() {
			$this->db->close();
			$this->page->output();
		}
		
		/* run()
			run tool
		*/
		private function run() {
			$this->initRequestValidator();
			$this->printToolHead();
			
			if ($this->rq->allRequiredDefined() == true) {
				$this->formSubmitted();
			}
		}
	}
	
	$instance = new HgzDeadlinksByUser();
	
?>