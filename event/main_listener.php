<?php
/**
 *
 * @package phpBB Extension - Mafiascum SiteChat
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace mafiascum\sitechat\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
/**
 * Event listener
 */
class main_listener implements EventSubscriberInterface
{
    
    /* @var \phpbb\controller\helper */
    protected $helper;

    /* @var \phpbb\template\template */
    protected $template;

    /* @var \phpbb\request\request */
    protected $request;

    /* @var \phpbb\user */
    protected $user;

    static public function getSubscribedEvents()
    {
        return array(
            'core.page_footer_after'          => 'inject_sitechat_template_vars',
            'core.ucp_prefs_view_data'        => 'deserialize_sitechat_ucp_options',
            'core.ucp_prefs_view_update_data' => 'save_sitechat_ucp_options',
            'core.ucp_prefs_view_after'       => 'serialize_sitechat_ucp_options',
            'core.user_setup'                 => 'load_language_on_setup',

        );
    }

    public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\request\request $request, \phpbb\user $user)
    {
        $this->helper = $helper;
        $this->template = $template;
        $this->request = $request;
        $this->user = $user;
    }

    public function load_language_on_setup($event)
    {
        $lang_set_ext = $event['lang_set_ext'];
        $lang_set_ext[] = array(
            'ext_name' => 'mafiascum/sitechat',
            'lang_set' => 'common',
        );
        $event['lang_set_ext'] = $lang_set_ext;
    }

    public function deserialize_sitechat_ucp_options($event) {
        $submit = $event['submit'];
        $data = $event['data'];
        $data['chat_enabled'] = $this->request->variable('chat_enabled', (!empty($this->user->data['chat_enabled']) ? ($this->user->data['chat_enabled'] ? 1 : 0) : 0));
        $data['enterlobby'] = $this->request->variable('enterlobby', (!empty($this->user->data['enterlobby']) ? ($this->user->data['enterlobby'] ? 1 : 0) : 1));
        
        $event['data'] = $data;
    }

    public function serialize_sitechat_ucp_options($event) {
        $data = $event['data'];
        $this->template->assign_vars(array(
            'S_CHAT'  => $data['chat_enabled'] == 1,
            'S_LOBBY' => $data['enterlobby'] == 1,
        ));
    }

    public function save_sitechat_ucp_options($event) {
        $data = $event['data'];
        $sql_ary = $event['sql_ary'];

        $sql_ary['chat_enabled'] = $data['chat_enabled'];
        $sql_ary['enterlobby'] = $data['enterlobby'];
        $event['sql_ary'] = $sql_ary;
    }

    public function inject_sitechat_template_vars($event) {
        global $siteChatUrl, $siteChatProtocol;

        $this->template->assign_vars(array(
            'SITE_CHAT_URL'      => $siteChatUrl,
            'SITE_CHAT_PROTOCOL' => $siteChatProtocol,
            'S_CHAT'             => $this->user->data['chat_enabled'] == 1 && $this->user->data['user_type'] != USER_INACTIVE,
            'S_LOBBY'            => $this->user->data['enterlobby'] == 1,
            'S_USER_ID'          => $this->user->data['user_id'],
        ));
    }

  
}