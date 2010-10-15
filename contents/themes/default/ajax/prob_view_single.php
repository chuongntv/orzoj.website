<?php
/*
 * $File: prob_view_single.php
 * $Date: Fri Oct 15 10:12:25 2010 +0800
 */
/**
 * @package orzoj-website
 * @license http://gnu.org/licenses GNU GPLv3
 */
/*
	This file is part of orzoj

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
if (!defined('IN_ORZOJ'))
	exit;

require_once $includes_path . 'problem.php';
require_once $theme_path . 'prob_func.php';

prob_view_single_parse_arg();

try
{
	/* navigator button-set version */
	// previous page
	$content = '';
	if ($start_page != -1)
	{
		$content = '<div id="prob-view-single-navigator-top">';
		$content .= '<a href="' . prob_view_by_group_get_a_href($gid, $start_page) 
			. '" id="prob-view-single-submit"'
			. ' onclick="' . prob_view_by_group_get_a_onclick($gid, $start_page) . '"><botton type="button">';
		$content .= __('previous page');
		$content .= '</button></a>';
		$content .= '</div>'; 
	}

	// problem descriptiong
	$content .= prob_view($pid);

	// submit
	$content .= '<a id="prob-submit-link" href="' . t_get_link('ajax-prob-submit', "$pid", TRUE, TRUE) . '">'
		. __('submit') . '</a>';

	// javascript
	$content .= '<script type="text/javascript">$("button").button();';
	$content .= '$("#prob-submit-link").colorbox({"escKey" : false});';
	$content .= '</script>';

	echo $content;
}
catch (Exc_runtime $e)
{
	die(__('Hello buddy: %s', $e->msg()));
}
?>

