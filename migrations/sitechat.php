<?php

namespace mafiascum\sitechat\migrations;

class sitechat extends \phpbb\db\migration\migration
{

    public function effectively_installed()
    {
        return $this->db_tools->sql_column_exists($this->table_prefix . 'users', 'enable_chat');
    }
    
    static public function depends_on()
    {
        return array('\phpbb\db\migration\data\v31x\v314');
    }
    
    public function update_schema()
    {
        return array(
            'add_columns' => array(
                 $this->table_prefix . 'users' => array(
                     'chat_enabled' => array('UINT:3', 0),
                     'enterlobby' => array('UINT:3', 1),
                 ),
            ),
        );
    }

    public function revert_schema()
    {
        return array(
            'drop_columns' => array(
                 $this->table_prefix . 'users' => array(
                     'chat_enabled',
                     'enterlobby',
                 ),
            ),
        );
    }
}
?>