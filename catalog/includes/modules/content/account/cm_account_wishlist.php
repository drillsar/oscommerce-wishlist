<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2018 osCommerce

  Released under the GNU General Public License
*/

  class cm_account_wishlist {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = MODULE_CONTENT_ACCOUNT_WISHLIST_TITLE;
      $this->description = MODULE_CONTENT_ACCOUNT_WISHLIST_DESCRIPTION;

      if ( defined('MODULE_CONTENT_ACCOUNT_WISHLIST_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_ACCOUNT_WISHLIST_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_ACCOUNT_WISHLIST_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate, $language;
       	
        $oscTemplate->_data[$this->group] += array('wishlist' => array('title' => MODULE_CONTENT_ACCOUNT_WISHLIST_PUBLIC_TITLE,
                                                                       'sort_order' => MODULE_CONTENT_ACCOUNT_WISHLIST_SORT_ORDER,
                                                                       'links' => array('list' => array('title' => MY_WISHLIST_VIEW,
																					    'link' => tep_href_link('wishlist.php', '', 'SSL'),
																					    'icon' => 'fas fa-heart'))));															   
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CONTENT_ACCOUNT_WISHLIST_STATUS');
    }

    function install() {
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Wishlist Module', 'MODULE_CONTENT_ACCOUNT_WISHLIST_STATUS', 'True', 'Show wishlist link inside the account page?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_CONTENT_ACCOUNT_WISHLIST_SORT_ORDER', '50', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from configuration where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_CONTENT_ACCOUNT_WISHLIST_STATUS', 'MODULE_CONTENT_ACCOUNT_WISHLIST_SORT_ORDER');
    }
  }

