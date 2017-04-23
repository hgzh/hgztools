<?php
	/**
	 *
	 * LIVE BOT STATISTICS
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

	class HgzBotStatistics extends Hgz {
		
		// request parameters
		protected $par = [];
		
		/* __construct()
			create objects
		*/
		public function __construct() {
			// create new page object
			$this->page = new HtmlPage('Bot statistics');

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
			// language
			$this->rq->addAllowed('GET', 'lang',    '',     '/^[a-z]{1,7}$/',  true);
			
			// project
			$this->rq->addAllowed('GET', 'project', '',     '/^[a-z]{1,15}$/', true);
			
			// sort by
			$this->rq->addAllowed('GET', 'sort',    'ec',   '/^(name|reg|ec)$/');
			
			// sort direction
			$this->rq->addAllowed('GET', 'dir',     'desc', '/^(asc|desc)$/');
			
			$this->par = $this->rq->getParams();
		}

		/* printToolHead()
			output header and tool option area
		*/
		private function printToolHead() {
			$this->page->openBlock('div', 'iw-content');
			$this->page->addInline('p', 'This tool generates a list of bots in a given project with their total editcount and registration date.');
			$this->page->addInline('h2', 'Options');
			
			// options
			$optionForm = new HtmlForm('index.php', 'GET');
			$optionForm->addHTML('<table class="iw-nostyle">');
			
			// lang/project
			$optionForm->addHTML('<tr><td>');
			$optionForm->addLabel('lang', 'Project');
			$optionForm->addHTML('</td><td>');
			$optionForm->addInput('lang', $par['lang'], '', 7, true);
			$optionForm->addHTML('&nbsp;.&nbsp;');
			$optionForm->addInput('project', $par['project'], '', 20, true);
			$optionForm->addHTML('&nbsp;.org</td></tr>');
			
			// submit button
			$optionForm->addHTML('<tr><td colspan="2">');
			$optionForm->addButton('submit', 'View statistics');
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
			$this->page->addInline('h2', 'Results');
			
			// connect db
			$this->db->replicaConnect(Database::getName($this->par['lang'], $this->par['project']));
			
			// build query
			$t1  = 'SELECT DISTINCT user_id, user_name, user_registration, user_editcount FROM user, user_groups';
			$t1 .= ' WHERE ug_group = \'bot\' AND ug_user = user_id';
			$t1 .= ' ORDER BY ';
			switch ($this->par['sort']) {
				case 'name': $t1 .= 'user_name '; break;
				case 'reg':  $t1 .= 'user_registration '; break;
				case 'ec':   $t1 .= 'user_editcount '; break;
			}
			$t1 .= strtoupper($par['dir']) . ';';
			
			// save current sort direction
			if ($this->par['dir'] == 'asc') {
				$sortNow = 'desc';
			} else {
				$sortNow = 'asc';
			}
			
			// execute query and get results
			$q1 = $this->db->query($t1);
			$r1 = Database::fetchResult($q1);
			
			// result table header
			$this->page->openBlock('table', 'iw-table iw-full');
			$this->page->openBlock('tr');
			$this->page->addInline('th', '#');
			$this->page->addInline('th', '<a href="index.php?lang=' . $par['lang'] . '&project=' . $par['project'] . '&sort=name&dir=' . $sortNow . '">Name</a>');
			$this->page->addInline('th', '<a href="index.php?lang=' . $par['lang'] . '&project=' . $par['project'] . '&sort=reg&dir=' . $sortNow . '">Registration</a>');
			$this->page->addInline('th', '<a href="index.php?lang=' . $par['lang'] . '&project=' . $par['project'] . '&sort=ec&dir=' . $sortNow . '">Editcount</a>');
			$this->page->addInline('th', 'Edits/day');
			$this->page->closeBlock();
			
			// init edits/day
			$datenow = new DateTime('now');
			$counter = 1;
			
			// loop through results
			foreach ($r1 as $l1) {
				
				// not every account with registration date in database
				if (isset($l1['user_registration']) && $l1['user_registration'] != '') {
					// format registration date and edits/day
					$datereg = DateTime::createFromFormat('YmdHis', $l1['user_registration']);
					$dateitv = $datereg->diff($datenow);
					$dateday = $dateitv->format('%a');
					$epd     = ($l1['user_editcount'] / $dateday);
					
					$datefound = true;
				} else {
					$datefound = false;
				}
				
				// open table row
				$this->page->openBlock('tr');
				
				// #nr
				$this->page->addInline('td', $counter++);
				
				// name and details
				$this->page->addInline('td', parent::buildWikilink($this->par['lang'], $this->par['project'], 'User:' . $l1['user_name'], str_replace('_', ' ', $l1['user_name'])) .
										' ( ' . parent::buildWikilink($this->par['lang'], $this->par['project'], 'Special:Contributions/' . $l1['user_name'], 'c') . ' | ' .
												parent::buildWikilink($this->par['lang'], $this->par['project'], 'Special:Log/' . $l1['user_name'], 'l') . ' )');
				
				// registration date
				if ($datefound == true) { 
					$this->page->addInline('td', $datereg->format('j M Y'));
				} else { 
					$this->page->addInline('td', '');
				}
				
				// editcount
				$this->page->addInline('td', number_format($l1['user_editcount'], 0, '', ' '));
				
				// edits/day
				if ($datefound == true) { 
					$this->page->addInline('td', number_format($epd, 2, ',', ' ' ));
				} else {
					$this->page->addInline('td', '');
				}
				
				// close table row
				$this->page->closeBlock();
			}
			
			//close result table
			$this->page->closeBlock();
			
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
	
	$instance = new HgzBotStatistics();
?>