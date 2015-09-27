<?php
	/**
	 *
	 * HGZTOOLS MAIN PAGE
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
	$page = new HtmlPage('hgztools');
	
	$page->addInline('h2', 'Tools for all Wikimedia projects');
	$page->openBlock('ul');
	$page->addInline('li', '<a href="botstatistics/">Bot statistics</a>');
	$page->addInline('li', '<a href="categoryredirects/">Category redirects</a>');
	$page->addInline('li', '<a href="contributors/">Contributors tool</a>');
	$page->addInline('li', '<a href="uselessprotections/">Useless protections</a>');
	$page->closeBlock();
	
	$page->addInline('p', 'Contact: <a href="https://de.wikipedia.org/wiki/User_talk:Hgzh">hgzh @ de.wikipedia</a>, Source: <a href="https://github.com/hgzh">hgzh @ github.com</a>');
	
	$page->addInline('h2', 'Change log');
	$page->addInline('h3', '2015-09-27');
	$page->openBlock('ul');
	$page->addInline('li', 'Category redirects: added sorting');
	$page->addInline('li', 'migrated all tools to new framework');
	$page->closeBlock();
		
	$page->output();
	
?>