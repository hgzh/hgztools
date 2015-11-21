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
	
	// create new page object
	$page = new HtmlPage('Category redirects');

	// create new database object
	$db = new Database();
	
	// create new request validator
	$rq = new RequestValidator();
	
	// get parameters
	$rq->addAllowed('GET', 'lang',    '',     '/^[a-z]{1,7}$/',  true);
	$rq->addAllowed('GET', 'project', '',     '/^[a-z]{1,15}$/', true);
	$rq->addAllowed('GET', 'sort',    'name', '/^(name|entries|length)$/');
	$par = $rq->getParams();
	
	$page->openBlock('div', 'iw-content');
	$page->addInline('p', 'This tool shows a list of categories that redirect to another category in the given project and the number of entries in it.');
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
	$optionForm->addButton('submit', 'View categories');
	$optionForm->addHTML('</td></tr>');
	
	$optionForm->addHTML('</table>');
	$optionForm->output();
	
	$page->closeBlock();
	
	if ($rq->allRequiredDefined() == true) {
		$page->openBlock('div', 'iw-content');		
		$page->addInline('h2', 'Results');
		
		$db->replicaConnect(Database::getName($par['lang'], $par['project']));
		$t1  = 'SELECT page.page_title, page.page_len, count(categorylinks.cl_from) AS cl ';
		$t1 .= 'FROM page LEFT JOIN categorylinks ON page.page_title = categorylinks.cl_to ';
		$t1 .= 'WHERE page.page_namespace = 14 AND page.page_is_redirect = 1 GROUP BY page.page_title ';
		if ($par['sort'] == 'name') {
			$t1 .= 'ORDER BY page.page_title;';
		} elseif ($par['sort'] == 'length') {
			$t1 .= 'ORDER BY page.page_len DESC;';
		} elseif ($par['sort'] == 'entries') {
			$t1 .= 'ORDER BY cl DESC;';
		}
		$q1 = $db->query($t1);
		
		if ($q1->num_rows === 0) {
			$page->addInline('p', 'there were no results for this query', 'iw-info');
		} else {
			$page->openBlock('table', 'iw-table');
			$page->openBlock('tr');
			$page->addInline('th', '<a href="index.php?lang=' . $par['lang'] . '&project=' . $par['project'] . '&sort=name">Name</a>');
			$page->addInline('th', '<a href="index.php?lang=' . $par['lang'] . '&project=' . $par['project'] . '&sort=entries">Entries</a>');
			$page->addInline('th', '<a href="index.php?lang=' . $par['lang'] . '&project=' . $par['project'] . '&sort=length">Bytes</a>');
			$page->closeBlock();
			while ($l1 = $q1->fetch_assoc()) {
				$page->openBlock('tr');
				$page->addInline('td', Hgz::buildWikilink($par['lang'], $par['project'], 'Category:' . $l1['page_title'], str_replace('_', ' ', $l1['page_title']), 'redirect=no'));
				$page->addInline('td', $l1['cl']);
				$page->addInline('td', $l1['page_len']);
				$page->closeBlock();
			}
			$page->closeBlock();
		}
		
		$q1->close();
		$page->closeBlock();
	}
	
	$page->output();
	$db->close();
?>