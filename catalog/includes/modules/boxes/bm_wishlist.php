<?php
/*
  $Id: Wish List Box revision 3
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Released under the GNU General Public License
*/


class bm_wishlist {
    var $code = 'bm_wishlist';
    var $group = 'boxes';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->title = MODULE_BOXES_WISHLIST_TITLE;
      $this->description = MODULE_BOXES_WISHLIST_DESCRIPTION;

      if ( defined('MODULE_BOXES_WISHLIST_STATUS') ) {
        $this->sort_order = MODULE_BOXES_WISHLIST_SORT_ORDER;
        $this->enabled = (MODULE_BOXES_WISHLIST_STATUS == 'True');

        $this->group = ((MODULE_BOXES_WISHLIST_CONTENT_PLACEMENT == 'Left Column') ? 'boxes_column_left' : 'boxes_column_right');
      }
    }

    function execute() {
      global $wishList, $currencies, $languages_id, $oscTemplate;

      $counter = $wishList->count_contents();
      if ($counter > 0) {
                    $wishlist_box = '';
        $products = $wishList->get_products();
                                $n=sizeof($products);
                    if ($n <= MAX_DISPLAY_WISHLIST_BOX) {
          for ($i=0; $i<$n; $i++) {
            $wishlist_box .= '<a class="list-group-item list-group-item-action';
            $wishlist_box .= '" href="' . tep_href_link('product_info.php', 'products_id=' . $products[$i]['id']) . '">';
            $wishlist_box .= $products[$i]['quantity'] . ' x ' . $products[$i]['name'];
            $wishlist_box .= '</a>';
          }

          $wishlist_totalized .= sprintf(MODULE_BOXES_WISHLIST_BOX_CART_TOTAL, $currencies->format($wishList->show_total()));
                    }
                $wishlist_box .= '<a href="' . tep_href_link('wishlist.php') . '">' . sprintf(TEXT_WISHLIST_COUNT, $counter) . '</a>';
            } else {
                    $wishlist_box = '<p>' . MODULE_BOXES_WISHLIST_BOX_CART_EMPTY . '</p>';
            }

      $tpl_data = ['group' => $this->group, 'file' => __FILE__];
      include 'includes/modules/block_template.php';
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_BOXES_WISHLIST_STATUS');
    }

    function install() {
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Wish List Box Module', 'MODULE_BOXES_WISHLIST_STATUS', 'True', 'Do you want to add the module to your shop?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Content Placement', 'MODULE_BOXES_WISHLIST_CONTENT_PLACEMENT', 'Right Column', 'Should the module be loaded in the left or right column?', '6', '1', 'tep_cfg_select_option(array(\'Left Column\', \'Right Column\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_BOXES_WISHLIST_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from configuration where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_BOXES_WISHLIST_STATUS', 'MODULE_BOXES_WISHLIST_CONTENT_PLACEMENT', 'MODULE_BOXES_WISHLIST_SORT_ORDER');
    }
}
?>

