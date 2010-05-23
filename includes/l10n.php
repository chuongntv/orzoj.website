<?php
/* 
 * $File: l10n.php
 * $Date: Sun Apr 18 05:15:20 2010 -0400
 * $Author: Fan Qijiang <fqj1994@gmail.com>
 */
/**
 * @package orzoj-phpwebsite
 * @license http://gnu.org/licenses GNU GPLv3
 * @version phpweb-1.0.0alpha1
 * @copyright (C) Fan Qijiang
 * @author Fan Qijiang <fqj1994@gmail.com>
 */
/*
	Orz Online Judge is a cross-platform programming online judge.
	Copyright (C) <2009,2010>  (Fan Qijiang) <fqj1994@gmail.com>

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
if (!defined('IN_ORZOJ')) exit;

require_once $root_path."includes/pomo/mo.php";
require_once $root_path.'includes/pomo/po.php';

$translators = array();

/**
 * Get translation
 * @param string $text original text
 * @return string translation
 * @see __
 */
function _gettext($text)
{
	global $translators;
	foreach ($translators as $key => $translator)
	{
		$current = $translator['class']->translate($text); if ($current != $text) return $current;
   	}
	return $text;
}


/**
 * Get translation
 * @param string $text original text
 * @return string translation
 * @see _gettext
 */
function __($text)
{
	return _gettext($text);
}

/**
 * Add a new .mo file as a translation source.
 * @param string $filename path and name of .mo file
 * @param bool $use_cache whether use in memory cache or not
 * @see l10n_add_po_file
 */
function l10n_add_mo_file($filename,$use_cache = true)
{
	global $translators;
	$insert_id = count($translators);
	$newmo = new MOReader;
	$newmo->use_cache = $use_cache;
	$newmo->filename = $filename;
	$translators[$insert_id] = array(
		'type' => 'mo',
		'class' => $newmo
	);
}


/**
 * Add a new .po file as a translation source.
 * @param string $filename path and name of .po file
 * @param bool $use_cache whether use in memory or not
 * @see l10n_add_mo_file
 */
function l10n_add_po_file($filename,$use_cache)
{
	global $translators;
	$insert_id = count($translators);
	$newmo = new POReader;
	$newmo->use_cache = $use_cache;
	$newmo->filename = $filename;
	$translators[$insert_id] = array(
		'type' => 'po',
		'class' => $newmo
	);
}
