<?php
/*
 * $File: contest_view_result.php
 * $Date: Thu Oct 28 10:59:43 2010 +0800
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

require_once $includes_path . 'contest/ctal.php';
require_once $includes_path . 'problem.php';

/*
 * page argument: <contest id:int>[|<page number:int>])
 *
 * POST:
 *		[page_num]: int, page number (starting at 1)
 *
 * if page number < 0, download the rank list
 */

if (is_null($page_arg))
	die('which contest do you like?');

define('PAGE_SIZE', 50);

$tmp = explode('|', $page_arg);
$cid = intval($tmp[0]);
if (count($tmp) >= 2)
	$pg_num = intval($tmp[1]);
else
	$pg_num = 0;

if (isset($_POST['page_num']))
	$pg_num = intval($_POST['page_num']) - 1;

$html_id_page = _tf_get_random_id();

echo "<div id='$html_id_page'>";

echo '<div style="clear: both; float: left;"><a class="contest-button-a" href="';
t_get_link('contest', $cid);
echo '" onclick="back_to_contest(); return false;">' .
	__('Back to contest') . '</a>
	<a class="contest-button-a" href="';
t_get_link('ajax-contest-view-result', "$cid|-1");
echo '">' . __('Download as a single XHTML page') . '</a>
	</div>';

?>

<script type="text/javascript">
$(".contest-button-a").button();

function back_to_contest()
{
	$.ajax({
		"type": "post",
		"cache": false,
		"url": "<?php t_get_link('ajax-contest-view');?>",
		"data": ({"id": <?php echo $cid;
			if (isset($_POST['back_to_list']))
				echo ', "back_to_list": "' . $_POST['back_to_list'] . '"';
		?>}),
		"success": function (data) {
			$("#<?php echo $html_id_page;?>").parent().html(data);
		}
	});
}
</script>

<?php
try
{
	$ct = ctal_get_class_by_cid($cid);
	if (!$ct->result_is_ready())
		throw new Exc_runtime(__('contest result is not available'));

	if ($pg_num < 0)
		$list = $ct->get_rank_list();
	else
		$list = $ct->get_rank_list(NULL, $pg_num * PAGE_SIZE, PAGE_SIZE);
}
catch (Exc_orzoj $e)
{
	echo '<div style="clear: both; float: left;">';
	echo __('Failed to get contest information: %s', htmlencode($e->msg()));
	echo '</div></div>';
	return;
}

if ($pg_num < 0)
{
	ob_clean();
	header('Content-type: application/orzoj-contest-result');
	header(sprintf('Content-Disposition: attachment; filename="result-%d.xhtml"',
		$cid));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php t_get_html_head(); ?>
	<title><?php echo __('Contest result of %d', $cid);?></title>
	<style type="text/css">
		table
		{
			border-collapse: collapse;
			margin: auto;
		}
		tr, th, td
		{
			border: 1px solid #C3C3C3;
			padding: 3px;
			text-align: center;
		}
		th { background-color: #E5EECC; }
		tr:hover { background-color: #E5EEFF; }
		body { font-size: 16px; color: black; text-align: center; }
		h1 { text-align: center; font-size: 24px; font-weight: bold; }
		h2 { text-align: center; font-size: 20px; color: #1DBDB4; }
		div.sign {clear: both; float: right; font-size: 12px; font-style: italic; }
	</style>
</head>
<body>
	<h1><?php echo $ct->data['name'];?></h1>
	<h2><?php echo __('CONTEST RESULT');?></h2>
	<table>
<?php
echo '<tr>';
foreach ($list[0] as $col)
	echo '<th>' . $col . '</th>';
echo '</tr>';

for ($i = 1; $i < count($list); $i ++)
{
	$row = &$list[$i];
	echo '<tr>';
	foreach ($row as $col)
	{
		echo '<td>';
		if (is_array($col))
			$col = $col[0];
		echo $col;
		echo '</td>';
	}
	echo '</tr>';
	unset($row);
}

?>
	</table>
	<div class="sign">
<?php
echo __('Generated by %s',
	'<a href="' . ORZOJ_OFFICIAL_WEBSITE . '">Orz Online Judge ' . ORZOJ_VERSION . '</a>') . '<br />';
echo time2str(time());
?>
	</div>
</body>
</html>
<?php
	die;
}

echo '<div style="clear: both;"><table class="page-table">';
echo '<tr>';
foreach ($list[0] as $col)
	echo '<th>' . $col . '</th>';
echo '</tr>';

for ($i = 1; $i < count($list); $i ++)
{
	$row = &$list[$i];
	echo '<tr>';
	foreach ($row as $col)
	{
		echo '<td>';
		if (is_array($col))
		{
			$href = NULL;
			if ($col[1] == 'uid')
				$href = t_get_link('ajax-user-info', $col[2], TRUE, TRUE);
			else if ($col[1] == 'rid')
				$href = t_get_link('ajax-record-detail', $col[2], TRUE, TRUE);
			if ($href)
				echo "<a href='$href' class='contest-result-a-colorbox'>";
			echo $col[0];
			if ($href)
				echo '</a>';
		}
		else
			echo $col;
		echo '</td>';
	}
	echo '</tr>';
	unset($row);
}

echo '</table></div>';

echo '<div class="contest-nav">';
function _make_link($prompt, $pg)
{
	global $cid;
	printf('<a href="%s" onclick="contest_result_nav(%d); return false;">%s</a>',
		t_get_link('show-ajax-contest-view-result', "$cid|$pg", TRUE, TRUE),
		$pg + 1, $prompt);
}

if ($pg_num)
{
	echo '<div style="float: left;">&lt;';
	_make_link(__('Prev'), $pg_num - 1);
	echo ' | </div>';
}

$html_id_nav_form = _tf_get_random_id();
$html_id_nav_input = _tf_get_random_id();

echo '<form id="' . $html_id_nav_form . '" method="post" action="';
t_get_link('show-ajax-contest-view-result', $cid);
echo '">
	<input type="text" id="'. $html_id_nav_input .'" name="page_num" value="' . ($pg_num + 1) .'" />';
$tot_page = ceil($ct->get_user_amount() / PAGE_SIZE);
echo "/$tot_page";
echo '</form>';

if ($pg_num + 1 < $tot_page)
{
	echo '<div style="float: left;"> | ';
	_make_link(__('Next'), $pg_num + 1);
	echo '&gt;</div>';
}

echo '</div> <!-- class: contest-nav -->';

?>

<script type="text/javascript">
$(".contest-result-a-colorbox").colorbox();
$("#<?php echo $html_id_nav_form;?>").bind("submit", function(){
	contest_result_nav($("#<?php echo $html_id_nav_input;?>").val());
	return false;
})
table_set_double_bgcolor();

function contest_result_nav(pg)
{
	$.ajax({
		"type": "post",
		"cache": false,
		"url": "<?php t_get_link('ajax-contest-view-result', $cid, FALSE);?>",
		"data": ({"page_num": pg <?php
			if (isset($_POST['back_to_list']))
				echo ', "back_to_list": "' . $_POST['back_to_list'] . '"';
		?>}),
		"success": function (data) {
			$("#<?php echo $html_id_page;?>").parent().html(data);
		}
	});
}

</script>

</div>

