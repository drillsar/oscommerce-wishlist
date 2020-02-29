<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class cm_pi_wishlist {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));


      $this->title = MODULE_CONTENT_PRODUCT_INFO_WISHLIST_TITLE;
      $this->description = MODULE_CONTENT_PRODUCT_INFO_WISHLIST_DESCRIPTION;

      if ( defined('MODULE_CONTENT_PRODUCT_INFO_WISHLIST_STATUS') ) {

        $this->sort_order = MODULE_CONTENT_PRODUCT_INFO_WISHLIST_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_PRODUCT_INFO_WISHLIST_STATUS == 'True');
      }
    }

    function execute() {
      global $_GET, $oscTemplate;


          $data = '';

          $data .= '<div class="col-sm-12">' .
                   '  <div class="row alert alert-info">' .
                   '    <div class="col-sm-8 ">' . MODULE_CONTENT_PRODUCT_INFO_WISHLIST_TEXT_ENTRY  . '</div>' .
                   '    <div class="col-sm-4 text-right">' .  tep_draw_button(TEXT_ADD_WISHLIST, 'glyphicon glyphicon-heart', null, 'primary', array('params' => 'name="wishlist" value="wishlist"')) . '</div>' .
                   '   </div>' .
                   '</div>';


        ob_start();
        include('includes/modules/content/' . $this->group . '/templates/tpl_' . basename(__FILE__));
        $template = ob_get_clean();

        $oscTemplate->addContent($template, $this->group);

      }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CONTENT_PRODUCT_INFO_WISHLIST_STATUS');
    }

    function install() {
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display Module', 'MODULE_CONTENT_PRODUCT_INFO_WISHLIST_STATUS', 'True', 'Activate wishlist module?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Max Wishlist', 'MAX_DISPLAY_WISHLIST_PRODUCTS', '10', 'How many wishlist items to show per page on the main wishlist page', '6', '2', now(), now(), NULL, NULL)");    
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Display Wishlist After Adding Product', 'WISHLIST_REDIRECT', 'Yes', 'Display the Wishlist after adding a product (or stay on product_info.php page)', '6', '5', now(), now(), NULL, 'tep_cfg_select_option(array(\'Yes\', \'No\'),')");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_CONTENT_PRODUCT_INFO_WISHLIST_SORT_ORDER', '150', 'Sort order of display. Lowest is displayed first.', '6', '6', now())");
      tep_db_query("drop table if exists customers_wishlist");
      tep_db_query("create table customers_wishlist (customers_wishlist_id int unsigned NOT NULL auto_increment, customers_id int unsigned NOT NULL default '0', products_id tinytext NOT NULL, customers_wishlist_quantity int(2) NOT NULL, final_price decimal(15,4), customers_wishlist_date_added char(8), PRIMARY KEY  (customers_wishlist_id), KEY idx_wishlist_customers_id (customers_id)) CHARACTER SET utf8 COLLATE utf8_unicode_ci");
      tep_db_query("drop table if exists customers_wishlist_attributes");
      tep_db_query("create table customers_wishlist_attributes (customers_wishlist_attributes_id int unsigned NOT NULL auto_increment, customers_id int unsigned NOT NULL default '0', products_id tinytext NOT NULL, products_options_id int unsigned NOT NULL default '0', products_options_value_id int unsigned NOT NULL default '0', PRIMARY KEY  (customers_wishlist_attributes_id), KEY idx_wishlist_att_customers_id (customers_id)) CHARACTER SET utf8 COLLATE utf8_unicode_ci");
    }


    function remove() {
      tep_db_query("delete from configuration where configuration_key in ('" . implode("', '", $this->keys()) . "')");
      tep_db_query("drop table if exists customers_wishlist");
      tep_db_query("drop table if exists customers_wishlist_attributes;");
    }

    function keys() {
      return array('MODULE_CONTENT_PRODUCT_INFO_WISHLIST_STATUS', 'MODULE_CONTENT_PRODUCT_INFO_WISHLIST_SORT_ORDER', 'MAX_DISPLAY_WISHLIST_PRODUCTS', 'WISHLIST_REDIRECT');
    }
  }
