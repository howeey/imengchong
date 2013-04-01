<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of test_model
 *
 * @author Administrator
 */
class test_model extends MC_Model {
    function  __construct() {
        parent::__construct();
    }
    function test(){

		/*
		CREATE TABLE IF NOT EXISTS `test` (
		  `ID` int(20) NOT NULL auto_increment,
		  `key` varchar(255) NOT NULL,
		  PRIMARY KEY  (`ID`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;
		
		*/
      //echo 'hello kitty';
      //$this->load->database();
      //$this->db->cache_on();
      //$this->db->cache_delete_all();
      //$ret = $query->result_array();
      $this->load->library('MC_Orm');
      $ret = $this->mc_orm->mc_query(array('type'=>'update', 'sql'=>'insert into mc_tag_meng_link values('.rand().','.rand().')'));
      var_dump($ret);
      echo '<br>';

      $input = array (
        'table_name' => 'mc_tag_meng_link',
        'field' => array('reffer_id'),
      );
      $ret = $this->mc_orm->mc_get($input);
      var_dump($ret);
      echo '<br>';

      $input = array (
        'table_name' => 'mc_tag_meng_link',
        'field_value' => array (
            'reffer_id' => rand(),
            'tag_id' => rand(),
        ),
      );
      $ret = $this->mc_orm->mc_add($input);
      var_dump($ret);
      echo '<br>';
//    foreach ($query->result_array() as $row)
//		{
//			   echo $row->ID;
//			   echo $row->key;
//		}
    }

 
}
?>
