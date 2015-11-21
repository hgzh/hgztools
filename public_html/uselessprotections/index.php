<?php
	/**
	 *
	 * USELESS PROTECTION TOOL
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
	
	require_once( '/data/project/hgztools/public_html/general.php' );
	
	// create new page object
	$page = new HtmlPage('Useless protections');

	// create new database object
	$db = new Database();
	
	// create new request validator
	$rq = new RequestValidator();
	
	// get parameters
	$rq->addAllowed('GET', 'lang',    '', '/^[a-z]{1,7}$/',  true);
	$rq->addAllowed('GET', 'project', '', '/^[a-z]{1,15}$/', true);
	$par = $rq->getParams();
	
	$page->openBlock('div', 'iw-content');
	$page->openBlock('p');
	$page->addHTML('This tool shows pages that meet the following criteria:');
	$page->openBlock('ol');
	$page->addInline('li', 'The page is protected from moves through non-autoconfirmed users');
	$page->addInline('li', 'The page is not protected from editing through non-autoconfirmed users or sysops');
	$page->closeBlock();
	$page->addHTML('In most wikis, the autoconfirmed right is required for moving pages, so a single move protection for non-autoconfirmed users will never be effective.');
	$page->closeBlock();
	
	$page->addInline('h2', 'Options');
	
	$optionForm = new HtmlForm('index.php', 'GET');
	$optionForm->addHTML('<table class="iw-nostyle">');
	
	$optionForm->addHTML('<tr><td>');
	$optionForm->addLabel('lang', 'Language');
	$optionForm->addHTML('</td><td>');
	$optionForm->addInput('lang', $par['lang'], 'Language code of the project, e.g. de', 7, true);
	$optionForm->addHTML('</td></tr>');
	
	$optionForm->addHTML('<tr><td>');
	$optionForm->addLabel('project', 'Project');
	$optionForm->addHTML('</td><td>');
	$optionForm->addInput('project', $par['project'], 'Project code, e.g wikipedia', 20, true);
	$optionForm->addHTML('</td></tr>');
	
	$optionForm->addHTML('<tr><td colspan="2">');
	$optionForm->addButton('submit', 'View useless protections');
	$optionForm->addHTML('</td></tr>');
	
	$optionForm->addHTML('</table>');
	$optionForm->output();
	
	$page->closeBlock();
	
	if ($rq->allRequiredDefined() == true) {
		$page->openBlock('div', 'iw-content');
		$page->addInline('h2', 'Results');
		
		$db->replicaConnect(Database::getName($par['lang'], $par['project']));
		$t1  = 'SELECT page_title, page_namespace FROM page, page_restrictions WHERE pr_page = page_id AND pr_type = \'move\' AND pr_level = \'autoconfirmed\'';
		$t1 .= ' AND page_id NOT IN (SELECT page_id FROM page, page_restrictions WHERE pr_page = page_id AND pr_type = \'edit\')';
		$t1 .= ' ORDER BY page_namespace, page_title;';
		
		$q1 = $db->query($t1);
		if ($q1->num_rows === 0) {
			$page->addInline('p', 'there were no results for this query', 'iw-info');
		} else {
			$page->openBlock('ul');
			while ($l1 = $q1->fetch_assoc()) {
				$page->addInline('li', Hgz::buildWikilink($par['lang'], $par['project'], Database::getNsNameFromNr($l1['page_namespace']) . $l1['page_title'], 
				                    Database::getNsNameFromNr($l1['page_namespace'], false) . str_replace('_', ' ', $l1['page_title'])));
			}
			$page->closeBlock();
		}
		
		$q1->close();
		$page->closeBlock();
	}
	
	$db->close();
	$page->output();
	
?>