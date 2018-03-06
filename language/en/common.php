<?php
/**
*
* @package phpBB Extension - MafiaScum Site Chat
* @copyright (c) 2017 mafiascum.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
    'DISABLE_CHAT' => 'Disable chat',
    'ENABLE_CHAT'  => 'Enable chat',
    'ENTER_LOBBY'  => 'Enter chat lobby automatically',
));
