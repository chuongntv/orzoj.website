<?php
/* 
 * $File: sched.php
 * $Date: Tue Sep 28 09:59:22 2010 +0800
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

require_once $includes_path . 'error.php';

/**
 * add a scheduled task
 * @param int $time task executing time (seconds since the Epoch)
 * @param string $file which file $func is (it's usually __FILE__) must be in orzoj-website direcotry
 * @param string $func the function to be called. It should not return NULL on success
 * @param array $args
 * @return int|bool id or FALSE if failed
 * @see instert_into
 */
function sched_add($time, $file, $func, $args)
{
	global $db, $root_path;
	$file = substr(realpath($file), strlen($root_path));
	$value_array = array(
		'time' => $time,
		'file' => $file,
		'func' => $func,
		'args' => serialize($args)
		);
	return $db->insert_into('jobs', $value_array);
}

/**
 * remove a scheduled task
 * @param int $id 
 * @return bool TRUE if succeed, otherwise FALSE
 */
function sched_remove($id)
{
	global $db, $DBOP;
	$where_clause = array(
		$DBOP['='], 'id', $id
		);
	return $db->delete_item('jobs', $where_clause);
}

/**
 * modify a scheduled task
 * @param int $id
 * @param int $time
 * @return bool TRUE if succeed, otherwise FALSE
 */
function sched_update($id, $time)
{
	global $db, $DBOP;
	$value = array(
		'time' => $time
	);
	$where_clause = array(
		$DBOP['='], 'id', $id
	);
	return $db->update_data('jobs', $value, $where_clause);
}

/**
 * 
 * find and execute jobs that should be executed now 
 * this function should be guaranteed to be executed frequently and regularly
 * @return int number of executed jobs, or -1 on error
 */
function sched_work()
{
	global $db, $DBOP, $root_path;
	$where_clause = array(
		$DBOP['<='], 'time', time()
		);
	$ret = $db->select_from('jobs', NULL, $where_clause);
	if ($ret === FALSE)
		error_set_message(__('%s: sched_work: failed to select from database: %s',
		__FILE__, $db->error()));
	$cnt = 0;
	foreach ($ret as $row)
	{
		require_once $root_path . $row['file'];
		$func = $row['func'];
		$args = unserialize($row['args']);
		if (call_user_func_array($func, $args) === NULL)
		{
			error_set_message(__('%s: failed to call user function', __FILE__));
			return -1;
		}
		sched_remove($row['id']);
		$cnt ++;
	}
	return $cnt;
}

