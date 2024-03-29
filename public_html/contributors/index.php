<?php
	/**
	 *
	 * CONTRIBUTORS TOOL
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
	
	class hgzContributors extends Hgz {
		
		protected $par = [];
		
		public function __construct() {
			// create new page object
			$this->page = new HtmlPage('Contributors');

			// create new database object
			$this->db = new Database();

			// create new request validator
			$this->rq = new RequestValidator();
			
			$this->run();
			$this->finish();
		}
		
		private function mustSkip($pDate) {
			if (isset($this->par['since']) && $this->par['since'] != '0000-00-00') {
				$timestamp = str_replace('-', '', $this->par['since']);
				if ($pDate < $timestamp) { 
					return true; 
				}
			}
			if (isset($this->par['until']) && $this->par['until'] != '0000-00-00')  {
				$timestamp = str_replace('-', '', $this->par['until']);
				if ($pDate > $timestamp) {
					return true;
				}
			}
			
			return false;
		}
		
		private function formatterWikiIntern($pData) {
			$sc = ':' . parent::getProjectShortcut($this->par['project'], $this->par['lang']);
			
			$html = '';
			foreach ($pData as $row) {
				$datetime = DateTime::createFromFormat('YmdHis', $row['rev_timestamp']);
				$dateraw  = $datetime->format('Ymd');
				$dateform = $datetime->format('Y-m-d H:i');
				if ($this->mustSkip($dateraw) === true) {
					continue;
				}
				
				$html .= '* [[' . $sc . 'Special:PermaLink/';
				$html .= $row['rev_id'] . '|' . $dateform . ' (UTC)]]';
				$html .= ' . . ';
				$html .= '[[' . $sc . 'User:';
				$html .= $row['actor_name'] . '|' . $row['actor_name'] . ']]';

				if (isset($row['comment_text']) && $row['comment_text'] != '') {
					$cmt = $row['comment_text'];
					$cmt = str_replace('[[', '[[' . $sc, $cmt);
					$cmt = preg_replace('/\{\{(.+)\}\}/', '&lt;nowiki&gt;{{$1}}}&lt;/nowiki&gt;', $cmt);
					$cmt = preg_replace('/\/\*\s(.+)\s\*\//', '[[' . $sc . 'Special:PermaLink/' . $row['rev_id'] . '#$1|→‎]]$1:', $cmt);
					$html .= ' \'\'('. $cmt . ')\'\' ';
				}
				$html .= '<br />';			
			}
			$this->page->addHTML($html);
			
		}
		
		private function formatterWikiExtern($pData) {
			$html = '';
			foreach ($pData as $row) {
				$datetime = DateTime::createFromFormat('YmdHis', $row['rev_timestamp']);
				$dateraw  = $datetime->format('Ymd');
				$dateform = $datetime->format('Y-m-d H:i');
				if ($this->mustSkip($dateraw) === true) {
					continue;
				}
				
				$html .= '* [https://' . $this->par['lang'] . '.' . $this->par['project'] . '.org/wiki/Special:PermaLink/';
				$html .= $row['rev_id'] . ' ' . $dateform . ' (UTC)]';
				$html .= ' . . ';
				$html .= '[https://' . $this->par['lang'] . '.' . $this->par['project'] . '.org/wiki/User:';
				$html .= $row['actor_name'] . ' ' . $row['actor_name'] . ']';

				if (isset($row['comment_text']) && $row['comment_text'] != '') {
					$cmt = $row['comment_text'];
					$cmt = preg_replace('/\[\[(.+)\]\]/', '&lt;nowiki&gt;[[$1]]]&lt;/nowiki&gt;', $cmt);
					$cmt = preg_replace('/\{\{(.+)\}\}/', '&lt;nowiki&gt;{{$1}}}&lt;/nowiki&gt;', $cmt);
					$cmt = preg_replace('/\/\*\s(.+)\s\*\//', '[https://' . $this->par['lang'] . '.' . $this->par['project'] . '.org/wiki/Special:PermaLink/' . $row['rev_id'] . '#$1 →‎]$1:', $cmt);
					$html .= ' \'\'('. $cmt . ')\'\' ';
				}
				$html .= '<br />';
			}
			$this->page->addHTML($html);

		}
		
		private function formatterHtml($pData) {
			$html = '&lt;ul>';
			foreach ($pData as $row) {
				$datetime = DateTime::createFromFormat('YmdHis', $row['rev_timestamp']);
				$dateraw  = $datetime->format('Ymd');
				$dateform = $datetime->format('Y-m-d H:i');
				if ($this->mustSkip($dateraw) === true) {
					continue;
				}
				
				$html .= '&lt;li>';
				$html .= '&lt;a href="https://' . $this->par['lang'] . '.' . $this->par['project'] . '.org/wiki/Special:PermaLink/';
				$html .= $row['rev_id'] . '">' . $dateform . ' (UTC)&lt;/a>';
				$html .= ' . . ';
				$html .= '&lt;a href="https://' . $this->par['lang'] . '.' . $this->par['project'] . '.org/wiki/User:';
				$html .= $row['actor_name'] . '">' . $row['actor_name'] . '&lt;/a>';

				if (isset($row['comment_text']) && $row['comment_text'] != '') {
					$cmt = $row['comment_text'];
					$cmt = preg_replace('/\/\*\s(.+)\s\*\//', '&lt;a href="https://' . $this->par['lang'] . '.' . $this->par['project'] . '.org/wiki/Special:PermaLink/' . $row['rev_id'] . '#$1">→‎&lt;/a>$1:', $cmt);
					$html .= ' &lt;i>('. $cmt . ')&lt;/i> ';
				}
				$html .= "&lt;/li>\r\n";
			}
			$html .= '&lt;/ul>';
			$this->page->addHTML($html);
		}
		
		private function initRequestValidator() {
			// get parameters
			$this->rq->addAllowed('GET', 'lang',    '',           '/^[a-z]{1,7}$/', true);
			$this->rq->addAllowed('GET', 'project', '',           '/^[a-z]{1,15}$/', true);
			$this->rq->addAllowed('GET', 'page',    '',           '', true, false);
			$this->rq->addAllowed('GET', 'since',   '0000-00-00', '/^\d{4}-\d{2}-\d{2}$/', false, false);
			$this->rq->addAllowed('GET', 'until',   '0000-00-00', '/^\d{4}-\d{2}-\d{2}$/', false, false);
			$this->rq->addAllowed('GET', 'format',  'wiki_int',   '/^(wiki_int|wiki_ext|html)$/', false, true);
			$this->par = $this->rq->getParams();
		}
		
		private function printToolHead() {
			$this->page->openBlock('div', 'iw-content');
			$this->page->addInline('p', 'This tool creates a list of contributors to a given article on a given project in wikitext.');
			$this->page->addInline('h2', 'Options');
			
			$optionForm = new HtmlForm('index.php', 'GET');
			$optionForm->addHTML('<table class="iw-nostyle">');
			
			$optionForm->addHTML('<tr><td>');
			$optionForm->addLabel('lang', 'Project');
			$optionForm->addHTML('</td><td>');
			$optionForm->addInput('lang', $this->par['lang'], '', 7, true);
			$optionForm->addHTML('&nbsp;.&nbsp;');
			$optionForm->addInput('project', $this->par['project'], '', 20, true);
			$optionForm->addHTML('&nbsp;.org</td></tr>');
			
			$optionForm->addHTML('<tr><td>');
			$optionForm->addLabel('page', 'Page title');
			$optionForm->addHTML('</td><td>');
			$optionForm->addInput('page', $this->par['page'], 'A page title in the main namespace (0)', 0, true);
			$optionForm->addHTML('</td></tr>');
			
			$optionForm->addHTML('<tr><td>');
			$optionForm->addLabel('since', 'Revisions since');
			$optionForm->addHTML('</td><td>');
			$optionForm->addInput('since', $this->par['since'], '(Format: YYYY-MM-DD)');
			$optionForm->addHTML('</td></tr>');

			$optionForm->addHTML('<tr><td>');
			$optionForm->addLabel('until', 'Revisions until');
			$optionForm->addHTML('</td><td>');
			$optionForm->addInput('until', $this->par['until'], '(Format: YYYY-MM-DD)');
			$optionForm->addHTML('</td></tr>');
			
			$optionForm->addHTML('<tr><td>');
			$optionForm->addLabel('format', 'Format');
			$optionForm->addHTML('</td><td>');
			$optionForm->addHTML('<select id="format" name="format">');
			$optionForm->addHTML('<option value="wiki_int">wikitext (internal links)</option>');
			$optionForm->addHTML('<option value="wiki_ext">wikitext (external links)</option>');
			$optionForm->addHTML('<option value="html">HTML</option>');
			$optionForm->addHTML('</select>');
			$optionForm->addHTML('</td></tr>');
			
			$optionForm->addHTML('<tr><td colspan="2">');
			$optionForm->addButton('submit', 'Get revisions');
			$optionForm->addHTML('</td></tr>');
			
			$optionForm->addHTML('</table>');
			$optionForm->output();
			
			$this->page->closeBlock();
		}
		
		private function formSubmitted() {
			$this->page->openBlock('div', 'iw-content');
			$this->page->addInline('h2', 'Results');
						
			$this->db->replicaConnect(Database::getName($this->par['lang'], $this->par['project']));
			$this->par['page'] = urldecode($this->par['page']);
			$this->par['page'] = html_entity_decode($this->par['page']);
			$this->par['page'] = str_replace(' ', '_', $this->par['page']);			
			$t1  = 'SELECT rv.rev_timestamp, ac.actor_name, cm.comment_text, rv.rev_id';
			$t1 .= ' FROM revision_userindex rv';
			$t1 .= ' INNER JOIN page p ON rv.rev_page = p.page_id';
			$t1 .= ' INNER JOIN comment cm ON rv.rev_comment_id = cm.comment_id';
			$t1 .= ' INNER JOIN actor ac ON rv.rev_actor = ac.actor_id';
			$t1 .= ' WHERE p.page_title = ?';
			$t1 .= ' AND p.page_namespace = 0';
			$t1 .= ' ORDER BY rv.rev_timestamp DESC';
			
			$q1 = $this->db->executePreparedQuery($t1, 's', $this->par['page']);
						
			if (Database::checkSqlQueryObject($q1) === false) {
				$this->page->addInline('p', 'SQL Error: ' . $this->db->error, 'iw-error');
				$this->page->closeBlock();
				return;
			}
			
			if ($q1->num_rows === 0) {
				$this->page->addInline('p', 'there were no results for this query.', 'iw-info');
			} else {
				$this->page->addInline('p', 'found ' . $q1->num_rows . ' revisions for article ' . 
					parent::buildWikilink($this->par['lang'], $this->par['project'], $this->par['page'], str_replace('_', ' ', $this->par['page'])) . ' (<a href="https://' . $this->par['lang'] . '.' . $this->par['project'] . '.org/w/index.php?title=' . $this->par['page'] . '&action=history">History</a>).');

				$this->page->openBlock('div', 'iw-code');
					
				$result = Database::fetchResult($q1);
				if ($this->par['format'] == 'wiki_int') {
					$this->formatterWikiIntern($result);
				} elseif ($this->par['format'] == 'wiki_ext') {
					$this->formatterWikiExtern($result);
				} elseif ($this->par['format'] == 'html') {
					$this->formatterHtml($result);
				}
				
				$this->page->closeBlock();
			}
			
			$q1->close();
			$this->page->closeBlock();
		}
		
		private function finish() {
			$this->db->close();
			$this->page->output();
		}
		
		private function run() {
			$this->initRequestValidator();
			$this->printToolHead();
			
			if ($this->rq->allRequiredDefined() == true) {
				$this->formSubmitted();
			}
		}
	}
	
	$instance = new HgzContributors();
?>