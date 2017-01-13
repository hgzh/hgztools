<?php
	/**
	 *
	 * LIVE BOT STATISTICS
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
	$page = new HtmlPage('Bot statistics');

	// create new database object
	$db = new Database();
	
	// create new request validator
	$rq = new RequestValidator();
	
	// get parameters
	$rq->addAllowed('GET', 'lang',    '',     '/^[a-z]{1,7}$/',  true);
	$rq->addAllowed('GET', 'project', '',     '/^[a-z]{1,15}$/', true);
	$rq->addAllowed('GET', 'sort',    'ec',   '/^(name|reg|ec)$/');
	$rq->addAllowed('GET', 'dir',     'desc', '/^(asc|desc)$/');
	$par = $rq->getParams();
	
	$page->openBlock('div', 'iw-content');
	$page->addInline('p', 'This tool generates a list of bots in a given project with their total editcount and registration date.');
	$page->addInline('h2', 'Options');
	
	$optionForm = new HtmlForm('index.php', 'GET');
	$optionForm->addHTML('<table class="iw-nostyle">');
	
	$optionForm->addHTML('<tr><td>');
	$optionForm->addLabel('lang', 'Project');
	$optionForm->addHTML('</td><td>');
	$optionForm->addInput('lang', $par['lang'], '', 7, true);
	$optionForm->addHTML('&nbsp;.&nbsp;');
	$optionForm->addInput('project', $par['project'], '', 20, true);
	$optionForm->addHTML('&nbsp;.org</td></tr>');
	
	$optionForm->addHTML('<tr><td colspan="2">');
	$optionForm->addButton('submit', 'View statistics');
	$optionForm->addHTML('</td></tr>');
	
	$optionForm->addHTML('</table>');
	$optionForm->output();
	
	$page->closeBlock();
	
	if ($rq->allRequiredDefined() == true) {
		$page->openBlock('div', 'iw-content');		
		$page->addInline('h2', 'Results');
		
		$db->replicaConnect(Database::getName($par['lang'], $par['project']));
		$t1  = 'SELECT DISTINCT user_id, user_name, user_registration, user_editcount FROM user, user_groups';
		$t1 .= ' WHERE ug_group = \'bot\' AND ug_user = user_id';
		$t1 .= ' ORDER BY ';
		switch ($par['sort']) {
			case 'name': $t1 .= 'user_name '; break;
			case 'reg':  $t1 .= 'user_registration '; break;
			case 'ec':   $t1 .= 'user_editcount '; break;
		}
		$t1 .= strtoupper($par['dir']) . ';';
		$q1 = $db->query($t1);
		
		if ($par['dir'] == 'asc') {
			$sortNow = 'desc';
		} else {
			$sortNow = 'asc';
		}
		
		$page->openBlock('table', 'iw-table iw-full');
		$page->openBlock('tr');
		$page->addInline('th', '#');
		$page->addInline('th', '<a href="index.php?lang=' . $par['lang'] . '&project=' . $par['project'] . '&sort=name&dir=' . $sortNow . '">Name</a>');
		$page->addInline('th', '<a href="index.php?lang=' . $par['lang'] . '&project=' . $par['project'] . '&sort=reg&dir=' . $sortNow . '">Registration</a>');
		$page->addInline('th', '<a href="index.php?lang=' . $par['lang'] . '&project=' . $par['project'] . '&sort=ec&dir=' . $sortNow . '">Editcount</a>');
		$page->addInline('th', 'Edits/day');
		$page->closeBlock();
		$datenow = new DateTime('now');
		$counter = 1;
		while ($l1 = $q1->fetch_assoc()) {
			if (isset( $l1['user_registration'] ) && $l1['user_registration'] != '') {
				$datereg = DateTime::createFromFormat('YmdHis', $l1['user_registration']);
				$dateitv = $datereg->diff($datenow);
				$dateday = $dateitv->format('%a');
				$epd     = ($l1['user_editcount'] / $dateday);
				
				$datefound = true;
			} else {
				$datefound = false;
			}
			
			$page->openBlock('tr');
			$page->addInline('td', $counter++);
			$page->addInline('td', Hgz::buildWikilink($par['lang'], $par['project'], 'User:' . $l1['user_name'], str_replace('_', ' ', $l1['user_name'])) .
				           ' ( ' . Hgz::buildWikilink($par['lang'], $par['project'], 'Special:Contributions/' . $l1['user_name'], 'c') . ' | ' .
				                   Hgz::buildWikilink($par['lang'], $par['project'], 'Special:Log/' . $l1['user_name'], 'l') . ' )');
			if ($datefound == true) { 
				$page->addInline('td', $datereg->format('j M Y'));
			} else { 
				$page->addInline('td', '');
			}
			$page->addInline('td', number_format($l1['user_editcount'], 0, '', ' '));
			if ($datefound == true) { 
				$page->addInline('td', number_format($epd, 2, ',', ' ' ));
			} else {
				$page->addInline('td', '');
			}
			$page->closeBlock();
		}
		$page->closeBlock();
		
		$q1->close();
		$page->closeBlock();
	}
	
	$db->close();
	$page->output();
	
?>