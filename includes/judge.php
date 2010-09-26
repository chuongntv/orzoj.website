<?php
/* 
 * $File: judge.php
 * $Date: Sun Sep 26 22:29:32 2010 +0800
 */
/**
 * @package orzoj-website
 * @license http://gnu.org/licenses GNU GPLv3
 */
/*
	This file is part of orzoj.

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

define('JUDGE_STATUS_ONLINE',1);
define('JUDGE_STATUS_OFFLINE',0);
define('JUDGE_STATUS_RUNNING',2);


function judge_search_by_name($name)
{
	global $db,$tablepre;
	$condition = array('param1' => 'name','op1' => 'text_eq','param2' => $name);
	$rt = $db->select_from($tablepre.'judges',NULL,$condition);
	if (is_array($rt) && count($rt) > 0)
		return $rt;
	else
		return false;
}

function judge_add($name,$language_supported,$query_ans)
{
	global $db,$tablepre;
	$content = array(
		'name' => $name,
		'language_supported' => serialize($language_supported),
		'variables' => serialize($query_ans)
	);
	$db->transaction_begin();
	$insert_id = $db->insert_into($tablepre.'judges',$content);
	if ($insert_id !== FALSE)
	{
		if ($insert_id == 0)
		{
			error_set_message(sprintf(__('Can\'t fetch the ID of new judge.')));
			$db->transaction_rollback();
			return FALSE;
		}
		else
		{
			$success = apply_filters('after_add_judge',true,$insert_id);
			if ($success)
			{
				$db->transaction_commit();
				return $insert_id;
			}
			else
			{
				$db->transaction_rollback();
				return FALSE;
			}
		}
	}
	else
	{
		error_set_message(sprintf(__('SQL Error : %s'),$db->error()));
		$db->transaction_rollback();
	}
}


function judge_update($id,$name,$language_supported,$query_ans)
{
	global $db,$tablepre;
	$condition = array('param1' => 'id','op1' => 'int_eq','param2' => $id);
	$content = array(
		'name' => $name,
		'language_supported' => serialize($language_supported)
		'variables' => serialize($query_ans)
	);
	$db->transaction_begin();
	$succ = $db->update_data($tablepre.'judges',$content,$condition);
	if ($succ !== FALSE)
	{
		$success = apply_filters('after_add_judge',true,$id);
		if ($success)
		{
			$db->transaction_commit();
			return $id;
		}
		else
		{
			$db->transaction_rollback();
			return FALSE;
		}
	}
	else
	{
		error_set_message(sprintf(__('SQL Error : %s'),$db->error()));
		$db->transaction_rollback();
	}

}


function judge_set_status($id,$status,$success_filter)
{	
	global $db,$tablepre;
	$condition = array('param1' => 'id','op1' => 'int_eq','param2' => $id);
	$content = array(
		'status' => $status);
	if ($db->update_data($tablepre.'judges',$content,$condition) !== FALSE)
	{
		$success = apply_filters($success_filter,true,$id);
		return true;
	}
	else
	{
		error_set_message(sprintf(__('SQL Error : %s'),$db->error()));
		return false;
	}

}

function judge_online($id)
{
	return judge_set_status($id,JUDGE_STATUS_ONLINE,'after_judge_online');
}


function judge_offline($id)
{
	return judge_set_status($id,JUDGE_STATUS_OFFLINE,'after_judge_offline');
}

function judge_running($id)
{
	return judge_set_status($id,JUDGE_STATUS_RUNNING,'after_judge_running');
}

