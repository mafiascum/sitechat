<?php

namespace mafiascum\sitechat\controller;

class toggleSiteChat
{
    /* @var \phpbb\request\request */
    protected $request;

    /* @var \phpbb\user */
    protected $user;

    /* @var \phpbb\db\driver\driver */
    protected $db;
    
    public function __construct(\phpbb\request\request $request, \phpbb\user $user, \phpbb\db\driver\driver_interface $db)
    {
        $this->request = $request;
        $this->user = $user;
        $this->db = $db;
    }

    public function handle()
    {
        $this->user->data['chat_enabled'] = !$this->user->data['chat_enabled'];

        $sql_ary = array(
            'chat_enabled' => $this->user->data['chat_enabled']
        );

        $sql = 'UPDATE ' . USERS_TABLE . '
                SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
                WHERE user_id = ' . $this->user->data['user_id'];
        
        $this->db->sql_query($sql);
        return new \Symfony\Component\HttpFoundation\JsonResponse(array());
    }
}