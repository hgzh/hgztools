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
			$this->page->addInline('li', 'Backlinks from other articles which have no links from given article');
			$this->page->addInline('li', 'Links from given article with backlinks from other articles');
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
			$optionForm->addButton('submit', 'View page conjunction');
			$optionForm->addHTML('</td></tr>');
			
			$optionForm->addHTML('</table>');
			$optionForm->output();
			
			$this->page->closeBlock();
		}
	
		/* formSubmitted()
			tool action after option form has been submitted
		*/
		private function formSubmitted() {
			
			// connect db
			$this->db->replicaConnect(Database::getName($this->par['lang'], $this->par['project']));
			
			// build query for outgoing links
			$this->par['page'] = str_replace(' ', '_', $this->par['page']);
			$t1  = 'SELECT s.pl_title';
			$t1 .= ' FROM pagelinks s';
			$t1 .= ' INNER JOIN page tp ON (s.pl_title = tp.page_title AND s.pl_namespace = tp.page_namespace)';
			$t1 .= ' INNER JOIN page sp ON (s.pl_from = sp.page_id AND s.pl_namespace = sp.page_namespace AND sp.page_title = ?)';
			$t1 .= ' WHERE s.pl_namespace = 0';
			$t1 .= ' AND s.pl_from_namespace = 0';
			
			// execute query and get results
			$q1 = $this->db->prepare($t1);
			$q1->bind_param('s', $this->par['page']);
			$q1->execute();
			$r1 = Database::fetchResult($q1);
			
			// build query for incoming links
			$t2  = 'SELECT tp.page_title';
			$t2 .= ' FROM page tp';
			$t2 .= ' INNER JOIN pagelinks t ON (tp.page_id = t.pl_from AND t.pl_title = ? AND t.pl_namespace = 0 AND t.pl_from_namespace = 0)';
			$t2 .= ' WHERE tp.page_is_redirect = 0';
			
			// execute query and get results
			$q2 = $this->db->prepare($t2);
			$q2->bind_param('s', $this->par['page']);
			$q2->execute();
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
			
			// get arrays
			$common = array_intersect($out, $inc);
			$noback = array_diff($out, $inc);
			$nolink = array_diff($inc, $out);
			
			// sort arrays
			sort($common);
			sort($noback);
			sort($nolink);
			
			// close queries
			$q1->close();
			$q2->close();
			
			// open statistics area
			$this->page->openBlock('div', 'iw-content');
			$this->page->addInline('h2', 'Statistics');
			
			$this->page->addInline('p', 'Page conjunction for ' . Hgz::buildWikilink($this->par['lang'], $this->par['project'], $this->par['page'], str_replace('_', ' ', $this->par['page']))
																. ' (' . Hgz::buildWikilink($this->par['lang'], $this->par['project'], 'Special:Whatlinkshere/' . $this->par['page'], 'What links here') . ')');
			
			// statistics
			$this->page->openBlock('p');
			$this->page->openBlock('ul');
			$this->page->addInline('li', count($out) . ' links on this page');
			$this->page->addInline('li', count($inc) . ' links to this page');
			$this->page->addInline('li', '<a href="#noback">' . count($noback) . ' links on this page without backlinks</a>');
			$this->page->addInline('li', '<a href="#nolink">' . count($nolink) . ' incoming links without corresponding outgoing links</a>');
			$this->page->addInline('li', '<a href="#mutual">' . count($common) . ' mutual links</a>');
			$this->page->closeBlock(2);
			
			// open result area
			$this->page->closeBlock();
			$this->page->openBlock('div', 'iw-content');
			$this->page->addInline('h2', 'Results');
			
			// no backlinks
			if (count($noback) != 0) {
				$this->page->addHTML('<span id="noback"></span>');
				$this->page->addInline('h3', 'Articles linked from ' . str_replace('_', ' ', $this->par['page']) . ' with no backlinks:');
				$this->page->openBlock('ul');
				foreach ($noback as $v1) {
					$this->page->addInline('li', Hgz::buildWikilink($this->par['lang'], $this->par['project'], $v1, str_replace('_', ' ', $v1)));
				}
				$this->page->closeBlock();
			}

			// no wikilinks
			if (count($nolink) != 0) {
				$this->page->addHTML('<span id="nolink"></span>');
				$this->page->addInline('h3', 'Articles with links to ' . str_replace('_', ' ', $this->par['page']) . ' but no links from here:');
				$this->page->openBlock('ul');
				foreach ($nolink as $v2) {
					$this->page->addInline('li', Hgz::buildWikilink($this->par['lang'], $this->par['project'], $v2, str_replace('_', ' ', $v2)));
				}
				$this->page->closeBlock();
			}

			// common links
			if (count($common) != 0) {
				$this->page->addHTML('<span id="mutual"></span>');
				$this->page->addInline('h3', 'Articles linked from ' . str_replace('_', ' ', $this->par['page']) . ' with backlinks (mutual links):');
				$this->page->openBlock('ul');
				foreach ($common as $v3) {
					$this->page->addInline('li', Hgz::buildWikilink($this->par['lang'], $this->par['project'], $v3, str_replace('_', ' ', $v3)));
				}
				$this->page->closeBlock();
			}
			
			// close result area
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