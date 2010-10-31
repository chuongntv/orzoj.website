<?php
/*
 * $File: post_list.php
 * $Date: Sun Oct 31 14:27:05 2010 +0800
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

/*
 * page argument: <start_page=int>|<type=string>|<uid=int>|<author=string>|<subject=string>
 *		type string see includes/post.php : $POST_TYPE_SET
 *		type option can appear more than once
 *		uid: user id
 *		subject: string
 *		author: string, both username and nickname are supported
 */
require_once $includes_path . 'post.php';
require_once $theme_path . 'post_func.php';

$POST_TOPIC_PER_PAGE = 20;

$start_page = 1;
$type = NULL;
$uid = NULL;
$subject = NULL;
$author = NULL;

if (isset($page_arg))
{
	$options = explode('|', $page_arg);
	foreach ($options as $option)
	{
		$expr = explode('=', $option);
		if (count($expr) != 2)
			die(__('Unknown page argument.'));
		switch ($expr[0])
		{
		case 'start_page':
			$start_page = intval($expr[1]);
			break;
		case 'type':
			if (array_search($expr[1], $POST_TYPE_SET) === FALSE)
				die(__('Unknown page argument.'));
			$type = $expr[1];
			break;
		case 'uid':
			$uid = intval($expr[1]);
			break;
		case 'subject':
			$subject = $expr[1];
			break;
		case 'author':
			$author = $expr[1];
			break;
		}
	}
}

foreach (array('start_page', 'uid', 'subject', 'author', 'type') as $item)
	if (isset($_POST[$item]))
	{
		$$item = $_POST[$item];
		if (empty($$item))
			$$item = NULL;
	}
if (is_string($start_page))
	$start_page = intval($start_page);
if (is_string($uid))
	$uid = intval($uid);

if (array_search($type, $POST_TYPE_SET) === FALSE)
	$type = NULL;
if ($type == 'all')
	$type = NULL;

if (is_string($subject))
	$subject_pattern = transform_pattern($subject);
else $subject_pattern = NULL;


?>

<div id="post-filter">

<div class="post-filter" style="margin-right: 10px; float: left;">
<?php echo __('Filter:'); ?>
</div>

<form action="<?php t_get_link('ajax-post-list'); ?>" method="post" id="post-filter-form">

<?php
/**
 * @ignore
 */
function _make_input($prompt, $post_name)
{
	global $$post_name;
	if (isset($$post_name))
		$default = $$post_name;
	else $default = '';
	$id = get_random_id();
	echo <<<EOF
<div class="post-filter"><label for="$id">$prompt</label></div>
<div class="post-filter"><input type="text" name="$post_name" id="$id" value="$default" /></div>
EOF;
}
/**
 * @ignore
 */
function _make_select($prompt, $post_name, $options)
{
	global $type;
	if (is_string($type))
		$default = $type;
	else $default = '';
	$id = get_random_id();
	echo <<<EOF
<div class="post-filter"><label for="$id">$prompt</label></div>
<div class="post-filter"><select id="$id" name="$post_name">
EOF;
	asort($options);

	foreach ($options as $disp => $val)
	{
		if ((string)$val == $default) 
			$selected = 'selected="selected"';
		else $selected = '';
		echo <<<EOF
<option value="$val" $selected>$disp</option>
EOF;
	}
	echo '</select></div>';
}
_make_input(__('Subject'), 'subject');
_make_input(__('Author'), 'author');
$types = array();
foreach ($POST_TYPE_SET as $ty)
	$types[$POST_TYPE_DISP[$ty]] = $ty;
_make_select(__('Type'), 'type', $types);
$Apply = __('Apply');
echo <<<EOF
<div class="post-filter"><input type="submit" id="filter-apply-button" value="$Apply" /></div>
EOF;
?>
</form></div><!-- id: post-filter -->

<?php
// cv : column value

/**
 * @ignore
 */
function _cv_type()
{
	global $post, $theme_path, $POST_TYPE_SET;
	echo '<img src="' . $theme_path . 'images/post-type-' .$post['type'] . '.gif' . '" alt="' . $POST_TYPE_SET[$post['type']] . '"/>';
}

/**
 * @ignore
 */
function _cv_subject()
{
	global $post, $start_page, $type, $uid, $subject, $author;
	echo '<a class="post-subject" href="' 
		. post_view_single_get_a_href($post['id'], 1, $start_page, $type, $uid, $subject, $author) . '"'
		. 'onclick="' . post_view_single_get_a_onclick($post['id'], 1, $start_page, $type, $uid, $subject, $author) . '"'
		. '>' . $post['subject'] . '</a>';
}

/**
 * @ignore
 */
function _cv_author()
{
	global $post;
	echo '<a class="post-author" href="' . t_get_link('ajax-user-info', $post['uid'], TRUE, TRUE) . '">' . $post['nickname_uid'] . '</a>';
}

/**
 * @ignore
 */
function _cv_rep_viewed()
{
	global $post;
	echo '<span class="post-reply-amount">' . $post['reply_amount']. '</span>';
	echo '/';
	echo '<span class="post-viewed-amount">' . $post['viewed_amount'] . '</span>';
}

/**
 * @ignore
 */
function _cv_last_replay()
{
	global $post;
	echo '<div class="post-last-reply">';
	echo '<div class="post-last-reply-user"><a href="' . t_get_link('ajax-user-info', $post['last_reply_user'], TRUE, TRUE) . '">' . $post['nickname_last_reply_user'] . '</a></div>';
	echo '<div class="post-last-reply-time">' . time2str($post['last_reply_time']) . '</div>';
	echo '</div>';
}

$cols = array(
	// array(<display name>, <display function>)
	array('', '_cv_type'),
	array(__('Subject'), '_cv_subject'),
	array(__('Author'), '_cv_author'),
	array(__('Rep./Viewed'), '_cv_rep_viewed'),
	array(__('Last reply'), '_cv_last_replay')
);

$error = false;
try
{
	$author_type = array('nickname', 'username');
	$total_page = ceil(post_get_topic_amount($type, $uid, $subject_pattern, $author, $author_type) / $POST_TOPIC_PER_PAGE);
	if ($start_page < 1) $start_page = 1;
	if ($start_page > $total_page) $start_page = $total_page;

	$posts = post_get_topic_list(
		array('id', 'uid', 'prob_id', 'score', 'type', 'last_reply_time', 'last_reply_user', 'subject', 'nickname_last_reply_user', 'nickname_uid', 'reply_amount', 'viewed_amount'), 
		$type,
		($start_page - 1) * $POST_TOPIC_PER_PAGE, 
		$POST_TOPIC_PER_PAGE,
		$uid,
		$subject_pattern,
		$author,
		$author_type
	);
} catch (Exc_runtime $e)
	{
		echo '<div style="clear: both;">' . $e->msg() . '</div>';
		$error = true;
	}

if (!$error)
{
	echo '<table class="page-table">';

	echo '<tr>';
	foreach ($cols as $val)
		echo "<th>$val[0]</th>";
	echo '</tr>';


	foreach ($posts as $post)
	{
		echo '<tr>';
		foreach ($cols as $col)
		{
			echo '<td>';
			$func = $col[1];
			$func();
			echo '</td>';
		}
		echo '</tr>';
	}
?>
</table>
<div id="post-list-navigator-bottom">
<?
	/**
	 * @ignore
	 */
	function _make_page_link($text, $page)
	{
		global $type, $uid, $subject, $author;
		return sprintf('<a href="%s" onclick="%s">%s</a>',
			post_list_get_a_href($page, $type, $uid, $subject, $author),
			post_list_get_a_onclick($page, $type, $uid, $subject, $author),
			$text
		);
	}

	/**
	 * @ignore
	 */
	function _make_page_nav()
	{
		global $start_page, $total_page;
		$ret = '';

		if ($start_page > 1)
			$ret .= '&lt;' . _make_page_link(__('Prev'), $start_page - 1);

		if ($start_page < $total_page)
			$ret .= ($start_page > 1 ? ' | ' : '') . _make_page_link(__('Next'), $start_page + 1) . '&gt;';
		return $ret;
	}
	echo _make_page_nav();
	$id = get_random_id();
	$GoToPage = __('Go to page');
	echo <<<EOF
<form action="#" id="post-list-goto-form" method="post" onsubmit="post_list_goto(); return false;">
<label for="$id" style="float: left">$GoToPage</label>
<input value="$start_page" name="goto_page" id="$id" style="float: left; width: 30px" type="text" />
/$total_page
</form>
EOF;
?>
</div><!-- id: post-list-navigator-bottom -->
<?php
	echo $db->get_query_amount() . ' database queries. ' . count($posts) . ' posts.';
}

?>

<script type="text/javascript">
table_set_double_bgcolor();
$(".post-last-reply-user a").colorbox();
$("a.post-author").colorbox();
function post_list_goto()
{
	var t = $("#posts-view");
	t.animate({"opacity" : 0.5}, 1);
	page = $("#post-list-goto-form input").val();
	$.ajax({
		"url" : "<?php t_get_link('ajax-post-list', NULL, FALSE, FALSE); ?>",
			"type" : "post",
			"cache" : false,
			"data" : ({
				"start_page" : page
<?php
foreach (array('uid', 'subject', 'author', 'type') as $item)
	if (is_int($$item) || (is_string($$item) && strlen($$item)))
		echo ', "' . $item . '" : "' . $$item . '"';
?>
			}),
		"success" : function(data) {
			t.animate({"opacity" : 1}, 1);
			t.html(data);
		}
	});
	return false;
}
$("#post-filter-form").bind("submit", function(){
	var t = $("#posts-view");
	t.animate({"opacity" : 0.5}, 1);
	$.ajax({
		"type" : "post",
		"cache" : false,
		"url" : "<?php t_get_link('ajax-post-list', NULL, FALSE); ?>",
		"data" : $("#post-filter-form").serializeArray(),
		"success" : function(data) {
			t.animate({"opacity": 1}, 1);
			t.html(data);
		}
	});
	return false;
});
$("#filter-apply-button").button();
</script>
