<?php
	/**
	 *
	 * WIKIDATA BADGES
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
	$page = new HtmlPage('Wikidata-Auszeichnungs-Abgleich');

	// create new database object
	$db = new Database();
	
	// get parameters from url
	$par_mode = $page->getParam('mode', '', '/^(lesenswert|exzellent|informativ|portal)$/', true);
	
	$page->openBlock('div', 'iw-content');
	$page->addInline('p', 'Mit diesem Werkzeug lassen sich Unterschiede zwischen den lokalen Auszeichnungsvorlagen und den Daten auf Wikidata feststellen.');
	
	$page->addInline('h2', 'Optionen');
	
	$optionForm = new HtmlForm('index.php', 'GET');
	$optionForm->addHTML('<table class="iw-nostyle">');
	
	$optionForm->addHTML('<tr><td>');
	$optionForm->addLabel('mode', 'Kategorie');
	$optionForm->addHTML('</td><td>');
	$optionForm->addHTML('<select id="mode" name="mode">');
	$optionForm->addHTML('<option value="lesenswert">lesenswerte Artikel</option>');
	$optionForm->addHTML('<option value="exzellent">exzellente Artikel</option>');
	$optionForm->addHTML('<option value="informativ">informative Listen</option>');
	$optionForm->addHTML('<option value="portal">informatives Portal</option>');
	$optionForm->addHTML('</select>');
	$optionForm->addHTML('</td></tr>');
	
	$optionForm->addHTML('<tr><td colspan="2">');
	$optionForm->addButton('submit', 'Abgleichen');
	$optionForm->addHTML('</td></tr>');
	
	$optionForm->addHTML('</table>');
	$optionForm->output();
	
	$page->closeBlock();
	
	if (isset($par_mode) && $par_mode != '') {
		$page->openBlock('div', 'iw-content');
		$page->addInline('h2', 'Ergebnisse');
		
		$db->replicaConnect(Database::getName('de', 'wikipedia'));
		$t1  = 'SELECT page_title FROM page, page_props';
		switch ($par_mode) {
			case 'lesenswert' : $t1 .= ' WHERE pp_propname = \'wikibase-badge-Q17437798\' AND pp_page = page_id AND page_namespace = 0'; break;
			case 'exzellent'  : $t1 .= ' WHERE pp_propname = \'wikibase-badge-Q17437796\' AND pp_page = page_id AND page_namespace = 0'; break;
			case 'informativ' : $t1 .= ' WHERE pp_propname = \'wikibase-badge-Q17506997\' AND pp_page = page_id AND page_namespace = 0'; break;
			case 'portal'     : $t1 .= ' WHERE pp_propname = \'wikibase-badge-Q17580674\' AND pp_page = page_id AND page_namespace = 100'; break;
		}
		$t1 .= ' ORDER BY page_title;';

		$t2  = 'SELECT page_title FROM page, categorylinks';
		switch ($par_mode) {
			case 'lesenswert' : $t2 .= ' WHERE cl_to = \'Wikipedia:Lesenswert\' AND cl_from = page_id AND page_namespace = 0'; break;
			case 'exzellent'  : $t2 .= ' WHERE cl_to = \'Wikipedia:Exzellent\' AND cl_from = page_id AND page_namespace = 0'; break;
			case 'informativ' : $t2 .= ' WHERE cl_to = \'Wikipedia:Informative_Liste\' AND cl_from = page_id AND page_namespace = 0'; break;
			case 'portal'     : $t2 .= ' WHERE cl_to = \'Wikipedia:Informatives_Portal\' AND cl_from = page_id AND page_namespace = 100'; break;
		}
		$t2 .= ' ORDER BY page_title;';
		
		$q1 = $db->query($t1);
		$q2 = $db->query($t2);
		
		$r1 = array();
		$r2 = array();
		$diff_nowd = array();
		$diff_nowp = array();
		
		while ($l1 = $q1->fetch_assoc()) {
			$r1[] = $l1['page_title'];
		}
		while ($l2 = $q2->fetch_assoc()) {
			$r2[] = $l2['page_title'];
		}
		
		$diff_nowd = array_diff($r2, $r1);
		$diff_nowp = array_diff($r1, $r2);
		
		if (count($diff_nowd) != 0) {
			$page->addInline('h3', 'Lokal als ' . $par_mode . ' ausgezeichnet, aber nicht auf Wikidata');
			$page->openBlock('ul');
			foreach ($diff_nowd as $v1) {
				if ($par_mode == 'portal') {
					$page->addInline('li', Hgz::buildWikilink('de', 'wikipedia', 'Portal:' . $v1, 'Portal:' . str_replace('_', ' ', $v1)));
				} else {
					$page->addInline('li', Hgz::buildWikilink('de', 'wikipedia', $v1, str_replace('_', ' ', $v1)));
				}
			}
			$page->closeBlock();
		}
		if (count($diff_nowp) != 0) {
			$page->addInline('h3', 'Auf Wikidata als ' . $par_mode . '  ausgezeichnet, aber nicht lokal');
			$page->openBlock('ul');
			foreach ($diff_nowp as $v1) {
				if ($par_mode == 'portal') {
					$page->addInline('li', Hgz::buildWikilink('de', 'wikipedia', 'Portal:' . $v1, 'Portal:' . str_replace('_', ' ', $v1)));
				} else {
					$page->addInline('li', Hgz::buildWikilink('de', 'wikipedia', $v1, str_replace('_', ' ', $v1)));
				}
			}
			$page->closeBlock();
		}
		
		$q1->close();
		$q2->close();
		$page->closeBlock();
	}
	
	$db->close();
	$page->output();
	
?>