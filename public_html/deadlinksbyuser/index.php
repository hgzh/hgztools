<?php
	/**
	 *
	 * DEAD LINKS BY USER
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
	$page = new HtmlPage('Tote Links nach Benutzer');

	// create new database object
	$db = new Database();
	
	// create new request validator
	$rq = new RequestValidator();
	
	// get parameters
	$rq->addAllowed('GET', 'user', '', '',  true, false);
	$par = $rq->getParams();
	
	$page->openBlock('div', 'iw-content');
	$page->addInline('p', 'Dieses Tool zeigt alle vom angegebenen Benutzer erstellten Artikel an, bei denen auf der Diskussionsseite ein Hinweis auf einen defekten Weblink hinterlassen wurde.');
	
	$page->addInline('h2', 'Optionen');
	
	$optionForm = new HtmlForm('index.php', 'GET');
	$optionForm->addHTML('<table class="iw-nostyle">');
	
	$optionForm->addHTML('<tr><td>');
	$optionForm->addLabel('user', 'Benutzername');
	$optionForm->addHTML('</td><td>');
	$optionForm->addInput('user', $par['user'], 'Name des zu analysierenden Benutzers', 0, true);
	$optionForm->addHTML('</td></tr>');

	$optionForm->addHTML('<tr><td colspan="2">');
	$optionForm->addButton('submit', 'Artikel anzeigen');
	$optionForm->addHTML('</td></tr>');
	
	$optionForm->addHTML('</table>');
	$optionForm->output();
	
	$page->closeBlock();
	
	if ($rq->allRequiredDefined() == true) {
		$page->openBlock('div', 'iw-content');
		$page->addInline('h2', 'Results');
		
		$par['user'] = str_replace(' ', '_', $par['user']);
		
		$db->replicaConnect(Database::getName('de', 'wikipedia'));
		$t1  = 'SELECT p.page_title';
		$t1 .= ' FROM page p';
		$t1 .= ' INNER JOIN revision_userindex rv ON rv.rev_page = p.page_id';
		$t1 .= ' INNER JOIN page pd ON p.page_title = pd.page_title';
		$t1 .= ' INNER JOIN categorylinks cl ON cl.cl_from = pd.page_id';
		$t1 .= ' WHERE rv.rev_parent_id = 0';
		$t1 .= ' AND p.page_namespace = 0';
		$t1 .= ' AND pd.page_namespace = 1';
		$t1 .= ' AND p.page_is_redirect = 0';
		$t1 .= ' AND rv.rev_user_text = ?';
		$t1 .= ' AND cl.cl_to = "Wikipedia:Defekte_Weblinks/Bot"';
		$q1 = $db->prepare($t1);
		$q1->bind_param('s', $par['user']);
		$q1->execute();
				
		$q1->store_result();
		$q1->bind_result($l1['page_title']);
		
		if ($q1->num_rows === 0) {
			$page->addInline('p', 'Es wurden keine Ergebnisse gefunden.', 'iw-info');
		} else {
			$page->openBlock('ul');
			while ($q1->fetch()) {
				$page->addInline('li', Hgz::buildWikilink('de', 'wikipedia', $l1['page_title'], str_replace('_', ' ', $l1['page_title'])) . ' (' . Hgz::buildWikilink('de', 'wikipedia', 'Diskussion:' . $l1['page_title'], 'Diskussion') . ')');
			}
			$page->closeBlock();
		}
		
		$q1->close();
		$page->closeBlock();
	}
	
	$db->close();
	$page->output();
	
?>