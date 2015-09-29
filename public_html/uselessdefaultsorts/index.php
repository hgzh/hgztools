<?php
	/**
	 *
	 * USELESS DEFAULTSORT TOOL
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
	$page = new HtmlPage('Useless defaultsorts');

	// create new database object
	$db = new Database();
	
	// get parameters from url
	$par_lang    = (isset($_GET['lang']   ) && $_GET['lang']    != '') ? strtolower($_GET['lang']   ) : '';
	$par_project = (isset($_GET['project']) && $_GET['project'] != '') ? strtolower($_GET['project']) : '';
	
	$page->openBlock('div', 'iw-content');
	$page->addInline('p', 'This tool generates a list of DEFAULTSORT keys that match the page title exactly.');
	
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
	
	$optionForm->addHTML('<tr><td colspan="2">');
	$optionForm->addButton('submit', 'View useless defaultsorts');
	$optionForm->addHTML('</td></tr>');
	
	$optionForm->addHTML('</table>');
	$optionForm->output();
	
	$page->closeBlock();
	
	if (isset($par_lang) && $par_lang != '' & isset($par_project) && $par_project != '') {
		
		if (!preg_match('/^[a-z]{1,7}$/', $par_lang) || !preg_match('/^[a-z]{1,15}$/', $par_project)) {
			$page->setMessage('Please enter valid language and project codes.', true);
		}
		
		$page->openBlock('div', 'iw-content');
		$page->addInline('h2', 'Results');
		
		$db->replicaConnect(Database::getName($par_lang, $par_project));
		$t1  = 'SELECT page_title, page_namespace, pp_value FROM page, page_props';
		$t1 .= ' WHERE pp_propname = \'defaultsort\' AND pp_page = page_id AND page_title = pp_value';
		$t1 .= ' ORDER BY pp_sortkey;';
		
		$q1 = $db->query($t1);
		if ($q1->num_rows === 0) {
			$page->addInline('p', 'there were no results for this query', 'iw-info');
		} else {
			$page->openBlock('table', 'iw-table');
			$page->addInline('tr', '<th>Page</th><th>Defaultsort</th>');
			while ($l1 = $q1->fetch_assoc()) {
				$page->openBlock('tr');
				$page->addInline('td', '<a href="https://' . $par_lang . '.' . $par_project . '.org/wiki/' . Database::getNsNameFromNr($l1['page_namespace']) . $l1['page_title'] . '">' 
									 . Database::getNsNameFromNr($l1['page_namespace'], false) . str_replace('_', ' ', $l1['page_title']) . '</a>');
				$page->addInline('td', $l1['pp_value']);
				$page->closeBlock();
			}
			$page->closeBlock();
		}
		
		$q1->close();
		$page->closeBlock();

	}
	
	$db->close();
	$page->output();
	
?>