<?php
	/**
	 *
	 * CATEGORY REDIRECTS
	 * Find category pages that redirect to another one.
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

	class HgzCategoryRedirects extends Hgz {
		
		// request parameters
		protected $par = [];
		
		/* __construct()
			create objects
		*/
		public function __construct() {
			// create new page object
			$this->page = new HtmlPage('Category redirects');

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
			$this->rq->addAllowed('GET', 'sort',    'name', '/^(name|entries|length)$/');
			
			$this->par = $this->rq->getParams();
		}
	
		/* printToolHead()
			output header and tool option area
		*/
		private function printToolHead() {
			$this->page->openBlock('div', 'iw-content');
			$this->page->addInline('p', 'This tool shows a list of categories that redirect to another category in the given project and the number of entries in it.');
			$this->page->addInline('h2', 'Options');
			
			// options
			$optionForm = new HtmlForm('index.php', 'GET');
			$optionForm->addHTML('<table class="iw-nostyle">');
			
			// lang/project
			$optionForm->addHTML('<tr><td>');
			$optionForm->addLabel('lang', 'Project');
			$optionForm->addHTML('</td><td>');
			$optionForm->addInput('lang', $this->par['lang'], '', 7, true);
			$optionForm->addHTML('&nbsp;.&nbsp;');
			$optionForm->addInput('project', $this->par['project'], '', 20, true);
			$optionForm->addHTML('&nbsp;.org</td></tr>');
			
			// submit button
			$optionForm->addHTML('<tr><td colspan="2">');
			$optionForm->addButton('submit', 'View categories');
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
			$t1  = 'SELECT p.page_title, p.page_len, COUNT(cl.cl_from) AS anz';
			$t1 .= ' FROM page p';
			$t1 .= ' LEFT JOIN categorylinks cl ON p.page_title = cl.cl_to';
			$t1 .= ' WHERE p.page_namespace = 14';
			$t1 .= ' AND p.page_is_redirect = 1';
			$t1 .= ' GROUP BY p.page_title';
			if ($this->par['sort'] == 'name') {
				$t1 .= ' ORDER BY p.page_title;';
			} elseif ($this->par['sort'] == 'length') {
				$t1 .= ' ORDER BY p.page_len DESC;';
			} elseif ($this->par['sort'] == 'entries') {
				$t1 .= ' ORDER BY anz DESC;';
			}
			
			// execute query and get results
			$q1 = $this->db->query($t1);
			$r1 = Database::fetchResult($q1);
			
			if ($q1->num_rows === 0) {
				// no results
				$this->page->addInline('p', 'there were no results for this query', 'iw-info');
			} else {
				// results found, results table header
				$this->page->openBlock('table', 'iw-table');
				$this->page->openBlock('tr');
				$this->page->addInline('th', '<a href="index.php?lang=' . $this->par['lang'] . '&project=' . $this->par['project'] . '&sort=name">Name</a>');
				$this->page->addInline('th', '<a href="index.php?lang=' . $this->par['lang'] . '&project=' . $this->par['project'] . '&sort=entries">Entries</a>');
				$this->page->addInline('th', '<a href="index.php?lang=' . $this->par['lang'] . '&project=' . $this->par['project'] . '&sort=length">Bytes</a>');
				$this->page->closeBlock();
				
				// loop through results
				foreach ($r1 as $l1) {
					$this->page->openBlock('tr');
					$this->page->addInline('td', parent::buildWikilink($this->par['lang'], $this->par['project'], 'Category:' . $l1['page_title'], str_replace('_', ' ', $l1['page_title']), 'redirect=no'));
					$this->page->addInline('td', $l1['anz']);
					$this->page->addInline('td', $l1['page_len']);
					$this->page->closeBlock();
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
	
	$instance = new HgzCategoryRedirects();
?>