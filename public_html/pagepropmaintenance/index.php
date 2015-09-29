<?php
	/**
	 *
	 * PAGEPROP MAINTENANCE
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
	$page = new HtmlPage('Page prop maintenance');

	// create new database object
	$db = new Database();
	
	// get parameters from url
	$par_lang    = $page->getParam('lang',    '', '/^[a-z]{1,7}$/');
	$par_project = $page->getParam('project', '', '/^[a-z]{1,15}$/');
	$par_mode    = $page->getParam('mode',    '', '/^(ns0\-noindex|ns0\-index|ns0\-noeditsection|ns0\-newsectionlink|staticredirect)$/');
	
	$page->openBlock('div', 'iw-content');
	$page->addInline('p', 'This tool allows to get information about some probably misused magic words in a specific project.');
	
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
	$optionForm->addLabel('mode', 'Mode');
	$optionForm->addHTML('</td><td>');
	$optionForm->addHTML('<select id="mode" name="mode">');
	$optionForm->addHTML('<option value="ns0-noindex">NOINDEX in main namespace</option>');
	$optionForm->addHTML('<option value="ns0-index">INDEX in main namespace</option>');
	$optionForm->addHTML('<option value="ns0-noeditsection">NOEDITSECTION in main namespace</option>');
	$optionForm->addHTML('<option value="ns0-newsectionlink">NEWSECTIONLINK in main namespace</option>');
	$optionForm->addHTML('<option value="staticredirect">STATICREDIRECT on non-redirect pages</option>');
	$optionForm->addHTML('</select>');
	$optionForm->addHTML('</td></tr>');
	
	$optionForm->addHTML('<tr><td colspan="2">');
	$optionForm->addButton('submit', 'View list');
	$optionForm->addHTML('</td></tr>');
	
	$optionForm->addHTML('</table>');
	$optionForm->output();
	
	$page->closeBlock();
	
	if (isset($par_lang) && $par_lang != '' && isset($par_project) && $par_project != '' && isset($par_mode) && $par_project != '') {
		$page->openBlock('div', 'iw-content');
		$page->addInline('h2', 'Results');
		
		$db->replicaConnect(Database::getName($par_lang, $par_project));
		$t1  = 'SELECT page_title, page_namespace, pp_value FROM page, page_props';
		switch ($par_mode) {
			case 'ns0-noindex'        : $t1 .= ' WHERE pp_propname = \'noindex\' AND pp_page = page_id AND page_namespace = 0'; break;
			case 'ns0-index'          : $t1 .= ' WHERE pp_propname = \'index\' AND pp_page = page_id AND page_namespace = 0'; break;
			case 'ns0-noeditsection'  : $t1 .= ' WHERE pp_propname = \'noeditsection\' AND pp_page = page_id AND page_namespace = 0'; break;
			case 'ns0-newsectionlink' : $t1 .= ' WHERE pp_propname = \'newsectionlink\' AND pp_page = page_id AND page_namespace = 0'; break;
			case 'staticredirect'     : $t1 .= ' WHERE pp_propname = \'staticredirect\' AND pp_page = page_id AND page_is_redirect = 0'; break;
		}
		$t1 .= ' ORDER BY page_title;';
		
		$q1 = $db->query($t1);
		if ($q1->num_rows === 0) {
			$page->addInline('p', 'there were no results for this query', 'iw-info');
		} else {
			$page->openBlock('table', 'iw-table');
			$page->addInline('tr', '<th>Page</th><th>Page prop value</th>');
			while ($l1 = $q1->fetch_assoc()) {
				$page->openBlock('tr');
				$page->addInline('td', Hgz::buildWikilink($par_lang, $par_project, Database::getNsNameFromNr($l1['page_namespace']) . $l1['page_title'], 
				                    Database::getNsNameFromNr($l1['page_namespace'], false) . str_replace('_', ' ', $l1['page_title'])));
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