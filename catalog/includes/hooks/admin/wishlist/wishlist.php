<?php
/*
  $Id: wishlist_hooks.php
  $Loc: catalog/includes/hooks/shop/wishlist/

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Wishlist v3 for Phoenix 1.0.5.0
   by @tmccaff 

  Copyright (c) 2018 Thomas McCaffery

  Released under the GNU General Public License
*/

class hook_admin_wishlist_wishlist {

  function listen_WishListMod() {

    tep_db_query("delete from customers_wishlist where products_id = '" . (int)$product_id . "' or products_id like '" . (int)$product_id . "{%'");
    tep_db_query("delete from customers_wishlist_attributes where products_id = '" . (int)$product_id . "' or products_id like '" . (int)$product_id . "{%'");
   }
   function listen_WishListModCustomer() {
     tep_db_query("delete from customers_wishlist where customers_id = " . (int)$customers_id);
     tep_db_query("delete from customers_wishlist_attributes where customers_id = " . (int)$customers_id);
   }
}
