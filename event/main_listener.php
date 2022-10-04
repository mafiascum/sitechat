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
        $mobile = $this->is_mobile();

        $this->template->assign_vars(array(
            'SITE_CHAT_URL'      => $siteChatUrl,
            'SITE_CHAT_PROTOCOL' => $siteChatProtocol,
            'S_SHOW_CHAT_TOGGLE' => $this->user->data['user_id'] != ANONYMOUS,
            'S_CHAT'             => $this->user->data['chat_enabled'] == 1 && $this->user->data['user_type'] != USER_INACTIVE && !$mobile,
            'S_LOBBY'            => $this->user->data['enterlobby'] == 1,
            'S_USER_ID'          => $this->user->data['user_id'],
        ));
    }
    
    public function is_mobile() {
        $server_interface = \phpbb\request\request_interface::SERVER;
        $server_vars = $this->request->get_super_global();
        $user_agent = $this->request->server('HTTP_USER_AGENT');
        $http_accept = $this->request->server('HTTP_ACCEPT');

        $chrome_browser = 0;
        $mobile_browser = 0;
        $mobile = false;
        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|mobile)/i', strtolower($user_agent))) {
            $mobile_browser++;
        }
        if (preg_match('/(chrome)/i', strtolower($user_agent))) {
            $chrome_browser++;
        }

        if ((strpos(strtolower($http_accept),'application/vnd.wap.xhtml+xml') > 0) or (($this->request->is_set('HTTP_X_WAP_PROFILE', $server_interface) or $this->request->is_set('HTTP_PROFILE', $server_interface)))) {
            $mobile_browser++;
        }

        $mobile_ua = strtolower(substr($user_agent, 0, 4));
        $mobile_agents = array('w3c','acs-','alav','alca','amoi','audi','avan','benq','bird','blac','blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno','ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-','lge-','maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-','newt','noki','oper','palm','pana','pant','phil','play','port','prox','qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar','sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-','tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp', 'wapr','webc','winw','winw','xda ','xda-');

        if (in_array($mobile_ua,$mobile_agents)) {
            $mobile_browser++;
        }

        if (strpos(strtolower($user_agent),'windows') > 0) {
            $mobile_browser = 0;
        }

        if ($mobile_browser > 0) {
            $mobile = true;
        }
        else {
            $mobile= false;
        }

        return $mobile;
    }
}