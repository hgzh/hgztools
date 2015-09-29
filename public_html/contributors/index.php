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
	
	// create new page object
	$page = new HtmlPage('Contributors');

	// create new database object
	$db = new Database();
	
	// get parameters from url
	$par_lang    = $page->getParam('lang',    '',           '/^[a-z]{1,7}$/');
	$par_project = $page->getParam('project', '',           '/^[a-z]{1,15}$/');
	$par_page    = $page->getParam('page',    '',           '', false);
	$par_since   = $page->getParam('since',   '0000-00-00', '/^\d{4}-\d{2}-\d{2}$/', false);
	$par_until   = $page->getParam('until',   '0000-00-00', '/^\d{4}-\d{2}-\d{2}$/', false);
	
	$page->openBlock('div', 'iw-content');
	$page->addInline('p', 'This tool creates a list of contributors to a given article on a given project in wikitext.');
	$page->addInline('h2', 'Options');
	
	$optionForm = new HtmlForm('index.php', 'GET');
	$optionForm->addHTML('<table class="iw-nostyle">');

	$optionForm->addHTML('<tr><td>');
	$optionForm->addLabel('lang', 'Language');
	$optionForm->addHTML('</td><td>');
	$optionForm->addInput('lang', $par_lang, 'Language code of the project, e.g. de', 7, true);
	$optionForm->addHTML('</td></tr>');
	
	$optionForm->addHTML('<tr><td>');
	$optionForm->addLabel('project', 'Project');
	$optionForm->addHTML('</td><td>');
	$optionForm->addInput('project', $par_project, 'Project code, e.g wikipedia', 20, true);
	$optionForm->addHTML('</td></tr>');
	
	$optionForm->addHTML('<tr><td>');
	$optionForm->addLabel('page', 'Page title');
	$optionForm->addHTML('</td><td>');
	$optionForm->addInput('page', $par_page, 'A page title in the main namespace (0)', 0, true);
	$optionForm->addHTML('</td></tr>');
	
	$optionForm->addHTML('<tr><td>');
	$optionForm->addLabel('since', 'Revisions since');
	$optionForm->addHTML('</td><td>');
	$optionForm->addInput('since', $par_since, '(Format: YYYY-MM-DD)');
	$optionForm->addHTML('</td></tr>');

	$optionForm->addHTML('<tr><td>');
	$optionForm->addLabel('until', 'Revisions until');
	$optionForm->addHTML('</td><td>');
	$optionForm->addInput('until', $par_until, '(Format: YYYY-MM-DD)');
	$optionForm->addHTML('</td></tr>');
	
	$optionForm->addHTML('<tr><td colspan="2">');
	$optionForm->addButton('submit', 'Get revisions');
	$optionForm->addHTML('</td></tr>');
	
	$optionForm->addHTML('</table>');
	$optionForm->output();
	
	$page->closeBlock();
	
	if (isset($par_lang) && $par_lang != '' && isset($par_project) && $par_project != '' && isset($par_page) && $par_page != '') {
		$page->openBlock('div', 'iw-content');
		$page->addInline('h2', 'Results');
		
		$db->replicaConnect(Database::getName($par_lang, $par_project));
		$par_page = str_replace(' ', '_', $par_page);
		$par_page = $db->real_escape_string($par_page);
		$t1  = 'SELECT revision_userindex.rev_timestamp, revision_userindex.rev_user_text, revision_userindex.rev_comment, revision_userindex.rev_id FROM revision_userindex, page WHERE page.page_title = \'' . $par_page . '\' ';
		$t1 .= 'AND page.page_namespace = 0 AND revision_userindex.rev_page = page.page_id ';
		$t1 .= 'ORDER BY revision_userindex.rev_timestamp DESC;';
		
		$q1 = $db->query($t1);
		
		if( $q1->num_rows === 0 ) {
			$page->addInline('p', 'there were no results for this query', 'iw-info');
		} else {
			$page->addInline('p', 'found ' . $q1->num_rows . ' revisions for article ' . 
				Hgz::buildWikilink($par_lang, $par_project, $par_page, str_replace('_', ' ', $par_page)) . '(<a href="https://' . $par_lang . '.' . $par_project . '.org/w/index.php?title=' . $par_page . '&action=history">History</a>).');
			$page->openBlock('div', 'iw-code');
			while ($l1 = $q1->fetch_assoc()) {
				$datetime = DateTime::createFromFormat('YmdHis', $l1['rev_timestamp']);
				$dateform = $datetime->format('Y-m-d H:i');
				$dateraw  = $datetime->format('Ymd');
				if (isset($par_since) && $par_since != '0000-00-00') {
					$timestamp = str_replace('-', '', $par_since);
					if ($dateraw < $timestamp) { 
						continue; 
					}
				}
				if (isset($par_until) && $par_until != '0000-00-00')  {
					$timestamp = str_replace('-', '', $par_until);
					if ($dateraw > $timestamp) {
						continue;
					}
				}
				$page->addHTML('* [[Special:PermaLink/' . $l1['rev_id'] . '|' . $dateform . ']]');
				$page->addHTML(' . . [[User:' . $l1['rev_user_text'] . '|' . $l1['rev_user_text'] . ']]');
				if (isset($l1['rev_comment']) && $l1['rev_comment'] != '') {
					$comment = $l1['rev_comment'];
					$comment = str_replace('[[', '[[:', $comment);
					$comment = preg_replace('/\{\{(.+)\}\}/', '&lt;nowiki&gt;{{$1}}}&lt;/nowiki&gt;', $comment);
					$comment = preg_replace('/\/\*\s(.+)\s\*\//', '[[Special:PermaLink/' . $l1['rev_id'] . '#$1|→‎]]$1:', $comment);
					$page->addHTML(' \'\'('. $comment . ')\'\' ');
				}
				$page->addHTML('<br />');
			}
			$page->closeBlock();
		}
		
		$q1->close();
		$page->closeBlock();
	}
	
	$db->close();
	$page->output();
	
?>