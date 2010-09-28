<?php
/* 
 * $File: user.php
 * $Date: Tue Sep 28 10:55:56 2010 +0800
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

/**
 * @ignore
 */
class _User
{
	var $id, $name, $realname,
		$avatar, // avatar file name
		$email, $self_desc, $tid,
		$view_gid, // array of gid who can view the user's source
		$reg_time, $reg_ip, $plang, $wlang,
		$groups; // array of id of groups that the user blong to
}

$user = NULL;
/**
 * check user login and initialize $user structure
 * @global $user
 * @return bool whether login successfully
 */
function check_login()
{
}

