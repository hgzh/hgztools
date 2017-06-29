<?php
	/**
	 *
	 * Page conjunction
	 * Checks outgoing and incoming links per article.
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

	class HgzPageConjunction extends Hgz {
		
		// request parameters
		protected $par = [];
		
		/* __construct()
			create objects
		*/
		public function __construct() {
			// create new page object
			$this->page = new HtmlPage('Page conjunction');

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
			
			// page
			$this->rq->addAllowed('GET', 'page',    '',           '', true, false);
			
			$this->par = $this->rq->getParams();
		}
	
		/* printToolHead()
			output header and tool option area
		*/
		private function printToolHead() {
			$this->page->openBlock('div', 'iw-content');
			$this->page->addInline('p', 'This tool generates reports about wikilinks in one article:');
			$this->page->openBlock('ul');
			$this->page->addInline('li', 'Links from given article which have no backlinks from target article');
			$this->page->addInline('li', 'Backlinks from foreign articles which have no links from given article');
			$this->page->addInline('li', 'Links from given article with backlinks from foreign articles');
			$this->page->closeBlock();
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
			
			// page
			$optionForm->addHTML('<tr><td>');
			$optionForm->addLabel('page', 'Page title');
			$optionForm->addHTML('</td><td>');
			$optionForm->addInput('page', $this->par['page'], 'A page title in the main namespace (0)', 0, true);
			$optionForm->addHTML('</td></tr>');
			
			// submit button
			$optionForm->addHTML('<tr><td colspan="2">');
			$optionForm->addButton('submit', 'View link report');
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
			
			// build query for outgoing links
			$this->par['page'] = str_replace(' ', '_', $this->par['page']);
			$t1  = 'SELECT s.pl_title';
			$t1 .= ' FROM pagelinks s, page sp';
			$t1 .= ' WHERE s.pl_from = sp.page_id';
			$t1 .= ' AND sp.page_title = ?';
			$t1 .= ' AND s.pl_namespace = 0';
			$t1 .= ' AND sp.pl_namespace = 0';
			$t1 .= ' AND s.pl_from_namespace = 0';
			
			// execute query and get results
			$q1 = $this->db->prepare($t1);
			$q1->bind_param('s', $this->par['page']);
			$r1 = Database::fetchResult($q1);
			
			// build query for incoming links
			$t2  = 'SELECT tp.page_title';
			$t2 .= ' FROM pagelinks t, page tp';
			$t2 .= ' WHERE t.pl_from = tp.page_id';
			$t2 .= ' AND t.pl_title = ?';
			$t2 .= ' AND t.pl_namespace = 0';
			$t2 .= ' AND t.pl_from_namespace = 0';
			
			// execute query and get results
			$q2 = $this->db->prepare($t2);
			$q2->bind_param('s', $this->par['page']);
			$r2 = Database::fetchResult($q2);
			
			$out = [];
			$inc = [];
			$common = [];
			$noback = [];
			$nolink = [];
			foreach ($r1 as $l1) {
				$out[] = $l1['pl_title'];
			}
			foreach ($r2 as $l2) {
				$inc[] = $l2['page_title'];
			}
			
			$common = array_intersect($out, $inc);
			$noback = array_diff($out, $inc);
			$nolink = array_diff($inc, $out);
			
			// no backlinks
			if (count($noback) != 0) {
				$page->addInline('h3', 'Articles linked from ' . str_replace('_', ' ', $this->par['page']) . ' with no backlinks:');
				$page->openBlock('ul');
				foreach ($noback as $v1) {
					$page->addInline('li', Hgz::buildWikilink($this->par['lang'], $this->par['project'], $v1, str_replace('_', ' ', $v1)));
				}
				$page->closeBlock();
			}

			// no wikilinks
			if (count($nolink) != 0) {
				$page->addInline('h3', 'Articles with links to ' . str_replace('_', ' ', $this->par['page']) . ' but no links from here:');
				$page->openBlock('ul');
				foreach ($nolink as $v2) {
					$page->addInline('li', Hgz::buildWikilink($this->par['lang'], $this->par['project'], $v2, str_replace('_', ' ', $v2)));
				}
				$page->closeBlock();
			}

			// common links
			if (count($common) != 0) {
				$page->addInline('h3', 'Articles linked from ' . str_replace('_', ' ', $this->par['page']) . ' with backlinks (common links):');
				$page->openBlock('ul');
				foreach ($common as $v3) {
					$page->addInline('li', Hgz::buildWikilink($this->par['lang'], $this->par['project'], $v3, str_replace('_', ' ', $v3)));
				}
				$page->closeBlock();
			}
			
			// close query and close result area
			$q1->close();
			$q2->close();
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
	
	$instance = new HgzPageConjunction();
?>