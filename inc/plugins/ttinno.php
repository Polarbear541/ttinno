<?php
//TTINNO by Polarbear541
//Released under the LGPL Licence (http://www.gnu.org/licenses/lgpl.html)
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("showthread_start", "ttinno_showtitles");

function ttinno_info()
{
	return array(
		"name"			=> "TTINNO - Thread Titles (and static links) in Next Newest/Oldest",
		"description"	=> "A plugin to show thread titles and link statically to those threads in Next Newest/Next Oldest",
		"author"		=> "Polarbear541",
		"version"		=> "1.1",
		"compatibility" => "16*",
		"guid" 			=> "8e9703d129fbfed2cfb6233041a0d4f9"
	);
}

function ttinno_install()
{
	global $db;
	//Insert setting then rebuild	
	$setting_one = array(
		'name'			=> 'ttinno_static_onoff',
		'title'			=> 'TTINNO Static Links On/Off',
		'description'	=> 'Link statically to the next/previous thread rather than to the showthread.php function',
		'optionscode'	=> 'yesno',
		'value'			=> '1',
		'disporder'		=> 15,
		'gid'			=> 18,
	);
	$db->insert_query('settings', $setting_one);
	
	rebuild_settings();
}

function ttinno_uninstall()
{
	global $db;
	//Remove and rebuild settings
	$db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name IN ('ttinno_static_onoff')");
	rebuild_settings(); 
}

function ttinno_is_installed()
{
	global $mybb;
	if(isset($mybb->settings['ttinno_static_onoff']))
	{
		return true;
	}
	else
	{
		return false;
	}
}

function ttinno_activate() //When plugin installed
{
	require MYBB_ROOT.'/inc/adminfunctions_templates.php';
	find_replace_templatesets("showthread",'#'.preg_quote('&laquo; <a href="{$next_oldest_link}">{$lang->next_oldest}</a> | <a href="{$next_newest_link}">{$lang->next_newest}</a> &raquo;').'#','&laquo; <a href="{$ttinno_prevlink}">{$lang->next_oldest} {$ttinno_prev}</a><br /><a href="{$ttinno_nextlink}">{$lang->next_newest} {$ttinno_next}</a> &raquo;');
}

function ttinno_deactivate() //When plugin uninstalled
{
	require MYBB_ROOT.'/inc/adminfunctions_templates.php';
	find_replace_templatesets("showthread",'#'.preg_quote('&laquo; <a href="{$ttinno_prevlink}">{$lang->next_oldest} {$ttinno_prev}</a><br /><a href="{$ttinno_nextlink}">{$lang->next_newest} {$ttinno_next}</a> &raquo;').'#','&laquo; <a href="{$next_oldest_link}">{$lang->next_oldest}</a> | <a href="{$next_newest_link}">{$lang->next_newest}</a> &raquo;');
}

function ttinno_showtitles() //Show titles when thread is shown
{
	global $mybb, $db, $lang, $thread, $ttinno_next, $ttinno_prev, $ttinno_prevlink, $ttinno_nextlink;
	
	if(is_moderator($thread['fid']))
	{
		$visibleonly = " AND (visible='1' OR visible='0')";
	}
	
	$options = array(
		"limit" => 1,
		"limit_start" => 0,
		"order_by" => "lastpost",
		"order_dir" => "desc"
	);
	$query = $db->simple_select("threads", "*", "fid=".$thread['fid']." AND lastpost < ".$thread['lastpost']." {$visibleonly} AND closed NOT LIKE 'moved|%'", $options);
	$prevthread = $db->fetch_array($query);
	if($prevthread['tid'])
	{
		$ttinno_prev = "- " . $prevthread['subject'];
		if($mybb->settings['ttinno_static_onoff'] == 1)
		{
			$ttinno_prevlink = get_thread_link($prevthread['tid']);
		}
		else
		{
			$ttinno_prevlink = get_thread_link($thread['tid'],"","nextoldest");	
		}
	}
	else
	{
		$ttinno_prevlink = get_thread_link($thread['tid'],"","nextoldest");
	}
	
	$options = array(
		"limit_start" => 0,
		"limit" => 1,
		"order_by" => "lastpost"
	);
	$query = $db->simple_select('threads', '*', "fid=".$thread['fid']." AND lastpost > ".$thread['lastpost']." {$visibleonly} AND closed NOT LIKE 'moved|%'", $options);
	$nextthread = $db->fetch_array($query);
	if($nextthread['tid'])
	{
		$ttinno_next = "- " . $nextthread['subject'];
		if($mybb->settings['ttinno_static_onoff'] == 1)
		{
			$ttinno_nextlink = get_thread_link($nextthread['tid']);
		}
		else
		{
			$ttinno_nextlink = get_thread_link($thread['tid'],"","nextnewest");
		}
	}
	else
	{
		$ttinno_nextlink = get_thread_link($thread['tid'],"","nextnewest");
	}
}
?>