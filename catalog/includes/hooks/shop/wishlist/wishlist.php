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

class hook_shop_wishlist_wishlist {

  function listen_WishListMod() {
    global $oscTemplate, $languages_id, $currencies, $wishList;

      	if (!tep_session_is_registered('wishList') || !is_object($wishList)) {
		      tep_session_register('wishList');
		      $wishList = new wishlist;
	      }
	
  if (isset($_POST['wishlist'])) {
	  if (isset($_POST['products_id']) && is_numeric($_POST['products_id'])) {
      $attributes = isset($_POST['id']) ? $_POST['id'] : '';
        // php 7
         $qty = (int)($_POST['qty'] ?? 1);
      $wishList->add_wishlist($_POST['products_id'], $wishList->get_quantity(tep_get_uprid($_POST['products_id'], $attributes))+$qty, $attributes);
	  }
		if (WISHLIST_REDIRECT ==  'No') tep_redirect(tep_href_link('product_info.php', 'products_id=' . $_POST['products_id']));
	  tep_redirect(tep_href_link('wishlist.php'));
  }
  }

  function listen_WishListModRestore() {
     $wishList->restore_wishlist();
   }
   function listen_WishListModReset() {
     $wishList->reset();
   }
   function listen_WishListModClear() {
     $wishList->clear();
   }
}

