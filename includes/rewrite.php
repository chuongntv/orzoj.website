<?php
/* 
 * $File: rewrite.php
 * $Date: Tue Jun 15 23:54:35 2010 +0800
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
	Copyright (C) <2010>  (Fan Qijiang) <fqj1994@gmail.com>

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

require $root_path.'includes/common.php';


/**
 * @decrepated
 */
function rewrite_generate_url($type,$info)
{
	$url = get_option('weburl');
	extract($info,EXTR_PREFIX_ALL,'info');
	switch ($type)
	{
	case 'problemview':
		$rt = $url.'index.php?action=problemview&';
		if ($info_method = 'id')
			$rt .= 'method=id&id='.$info_id;
		else if ($info_method = 'slug')
			$rt .= 'method=slug&slug='.urlencode($info_slug);
		break;
	case 'problemlist':
		$rt = $url.'index.php?action=problemlist&page='.$info_page;
		break;
	case 'page':
		$rt = $url.'?action=page&method=';
		if ($info_method = 'slug')
			$rt .= 'slug&slug='.urlencode($info_slug);
		else if ($info_method = 'id')
			$rt .= 'id&id='.$info_id;
		break;
	}
}


