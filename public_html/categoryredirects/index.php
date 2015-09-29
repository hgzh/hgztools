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
	
	// get parameters from url
	$par_lang    = $page->getParam('lang',    '',     '/^[a-z]{1,7}$/');
	$par_project = $page->getParam('project', '',     '/^[a-z]{1,15}$/');
	$par_sort    = $page->getParam('sort',    'name', '/^(name|entries|length)$/');
	
	$page->openBlock('div', 'iw-content');
	$page->addInline('p', 'This tool shows a list of categories that redirect to another category in the given project and the number of entries in it.');
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
	$optionForm->addButton('submit', 'View categories');
	$optionForm->addHTML('</td></tr>');
	
	$optionForm->addHTML('</table>');
	$optionForm->output();
	
	$page->closeBlock();
	
	if (isset($par_lang) && $par_lang != '' && isset($par_project) && $par_project != '') {
		$page->openBlock('div', 'iw-content');		
		$page->addInline('h2', 'Results');
		
		$db->replicaConnect(Database::getName($par_lang, $par_project));
		$t1  = 'SELECT page.page_title, page.page_len, count(categorylinks.cl_from) AS cl ';
		$t1 .= 'FROM page LEFT JOIN categorylinks ON page.page_title = categorylinks.cl_to ';
		$t1 .= 'WHERE page.page_namespace = 14 AND page.page_is_redirect = 1 GROUP BY page.page_title ';
		if ($par_sort == 'name') {
			$t1 .= 'ORDER BY page.page_title;';
		} elseif ($par_sort == 'length') {
			$t1 .= 'ORDER BY page.page_len DESC;';
		} elseif ($par_sort == 'entries') {
			$t1 .= 'ORDER BY cl DESC;';
		}
		$q1 = $db->query($t1);
		
		if ($q1->num_rows === 0) {
			$page->addInline('p', 'there were no results for this query', 'iw-info');
		} else {
			$page->openBlock('table', 'iw-table');
			$page->openBlock('tr');
			$page->addInline('th', '<a href="index.php?lang=' . $par_lang . '&project=' . $par_project . '&sort=name">Name</a>');
			$page->addInline('th', '<a href="index.php?lang=' . $par_lang . '&project=' . $par_project . '&sort=entries">Entries</a>');
			$page->addInline('th', '<a href="index.php?lang=' . $par_lang . '&project=' . $par_project . '&sort=length">Bytes</a>');
			$page->closeBlock();
			while ($l1 = $q1->fetch_assoc()) {
				$page->openBlock('tr');
				$page->addInline('td', Hgz::buildWikilink($par_lang, $par_project, 'Category:' . $l1['page_title'], str_replace('_', ' ', $l1['page_title']), 'redirect=no'));
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