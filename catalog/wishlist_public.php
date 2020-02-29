<?php
/*
  $Id: wishlist_public.php, 2.3.4 revision 3  2014/11/21 Dennis Blake
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
  require('includes/languages/' . $language . '/wishlist.php');

  if (!isset($_GET['public_id'])) tep_redirect(tep_href_link('index.php'));

  $public_id = $_GET['public_id'];

/*******************************************************************
****************** QUERY CUSTOMER INFO FROM ID *********************
*******************************************************************/

 	$customer_query = tep_db_query("select customers_firstname, customers_lastname from customers where customers_id = '" . (int)$public_id . "'");
	if (tep_db_num_rows($customer_query) == 0) tep_redirect(tep_href_link('index.php'));
	$customer = tep_db_fetch_array($customer_query);

/*******************************************************************
****************** ADD PRODUCT TO SHOPPING CART ********************
*******************************************************************/

  if (isset($_POST['add_wishprod'])) {
		foreach ($_POST['add_wishprod'] as $key =>$value) {
			if ($key == $value) {
				$check_query = tep_db_query("select customers_wishlist_quantity from customers_wishlist where customers_id = '" . (int)$public_id . "' and products_id = '" . tep_db_input($value) . "'");
				if (tep_db_num_rows($check_query) == 1) { // make sure product is still in the wishlist
				  $check = tep_db_fetch_array($check_query);
				  $attributes = array();
			    $wishlist_products_attributes_query = tep_db_query("select products_options_id as po, products_options_value_id as pov from customers_wishlist_attributes where customers_id='" . (int)$public_id . "' and products_id = '" . tep_db_input($value) . "'");
          while ($wishlist_products_attributes = tep_db_fetch_array($wishlist_products_attributes_query)) {
					  $attributes[$wishlist_products_attributes['po']] = $wishlist_products_attributes['pov'];
				  }
			    $cart->add_cart($value, $cart->get_quantity($value)+$check['customers_wishlist_quantity'], $attributes);
				}
			}
		}
  	tep_redirect(tep_href_link('shopping_cart.php'));
  }


 $breadcrumb->add($customer['customers_firstname'] . ' ' . $customer['customers_lastname'] . NAVBAR_TITLE_PUBLIC_WISHLIST, tep_href_link('wishlist_public.php', 'public_id=' . $public_id, 'SSL'));
 
 require('includes/template_top.php');
 $page = '';
 if (isset($_GET['page'])) $page = '&page=' . $_GET['page'];
?>

<h1><?php echo $customer['customers_firstname'] . ' ' . $customer['customers_lastname'] .  HEADING_TITLE2; ?></h1>
<div class="contentContainer">
<?php echo tep_draw_form('wishlist_form', tep_href_link('wishlist_public.php', 'public_id=' . $public_id . $page, 'SSL'));

  if ($messageStack->size('wishlist') > 0) {
	echo $messageStack->output('wishlist'); 
  }

/*******************************************************************
****** QUERY THE DATABASE FOR THE CUSTOMERS WISHLIST PRODUCTS ******
*******************************************************************/

  $wishlist_query_raw = "select * from customers_wishlist where customers_id = '" . (int)$public_id . "'";
  $wishlist_split = new splitPageResults($wishlist_query_raw, MAX_DISPLAY_WISHLIST_PRODUCTS);
  $wishlist_query = tep_db_query($wishlist_split->sql_query);
  if (tep_db_num_rows($wishlist_query)) {

	if ($wishlist_split->number_of_rows > 0 && (PREV_NEXT_BAR_LOCATION == '1' || PREV_NEXT_BAR_LOCATION == '3')) {
?>
<div class="row">
  <div class="col-sm-6 pagenumber hidden-xs">
    <?php echo $wishlist_split->display_count(TEXT_DISPLAY_NUMBER_OF_WISHLIST); ?>
  </div>
  <div class="col-sm-6">
    <div class="pull-right pagenav"><ul class="pagination"><?php echo $wishlist_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></ul></div>
    <span class="pull-right"><?php echo TEXT_RESULT_PAGE; ?></span>
  </div>
</div>
<?php
  }
?>
  <table class="table table-striped table-hover table-responsive">
  <thead>
  <tr>
    <th class="text-center"><?php echo BOX_TEXT_IMAGE; ?></th>
    <th class="text-center"><?php echo BOX_TEXT_QTY; ?></th>
    <th><?php echo BOX_TEXT_PRODUCT; ?></th>
    <th><?php echo BOX_TEXT_PRICE; ?></th>
    <th class="text-center"><?php echo BOX_TEXT_SELECT; ?></th>
  </tr>
 </thead> 
 <tbody>
  <?php 
/*******************************************************************
***** LOOP THROUGH EACH PRODUCT ID TO DISPLAY IN THE WISHLIST ******
*******************************************************************/
	  $i = 0;
    while ($wishlist = tep_db_fetch_array($wishlist_query)) {
      $products_query = tep_db_query("select * from products p, products_description pd  where pd.products_id = '" . (int)$wishlist['products_id'] . "' and p.products_id = pd.products_id and pd.language_id = " . (int)$languages_id);
      $products = tep_db_fetch_array($products_query);
			$products['specials_new_products_price'] = $products['sale_expires'] = '';
      $specials_query = tep_db_query("select specials_new_products_price, expires_date from specials where products_id = '" . (int)$wishlist['products_id'] . "' and status = '1' order by specials_new_products_price, expires_date limit 1");
      if (tep_db_num_rows($specials_query)) {
        $specials = tep_db_fetch_array($specials_query);
        $products['specials_new_products_price'] = $specials['specials_new_products_price'];
			  $products['sale_expires'] = tep_date_short($specials['expires_date']);
      }
?>

  <tr>
    <td>
		<?php
			$image = '';
			$image = tep_image('images/' . $products['products_image'], $products['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT);
			if ($products['products_status'] == 0) {
			  echo $image; 
			} else {
			  echo '<a href="' . tep_href_link('product_info.php', 'products_id=' . $wishlist['products_id'], 'NONSSL') . '">' . $image . '</a>';
			} 
		?>
	</td>
    <td class="text-center"><?php echo $wishlist['customers_wishlist_quantity']; ?>&nbsp;x</td>
    <td><strong><?php if ($products['products_status'] != 0) echo '<a href="' . tep_href_link('product_info.php', 'products_id=' . $wishlist['products_id'], 'NONSSL') . '">';
     echo $products['products_name'];
     if ($products['products_status'] != 0) echo '</a>';
      ?></strong>
    <?php

/*******************************************************************
******** THIS IS THE WISHLIST CODE FOR PRODUCT ATTRIBUTES  *********
*******************************************************************/

      $attributes_addon_price = 0;
      // Now get and populate product attributes
      $wishlist_products_attributes_query = tep_db_query("select products_options_id as po, products_options_value_id as pov from customers_wishlist_attributes where customers_id='" . (int)$public_id . "' and products_id = '" . tep_db_input($wishlist['products_id']) . "'");
      while ($wishlist_products_attributes = tep_db_fetch_array($wishlist_products_attributes_query)) {
        // Output the appropriate attribute name
        $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix
                                    from products_options popt, products_options_values poval, products_attributes pa
                                    where pa.products_id = '" . (int)$wishlist['products_id'] . "'
                                    and pa.options_id = '" . (int)$wishlist_products_attributes['po'] . "'
                                    and pa.options_id = popt.products_options_id
                                    and pa.options_values_id = '" . (int)$wishlist_products_attributes['pov'] . "'
                                    and pa.options_values_id = poval.products_options_values_id
                                    and popt.language_id = '" . (int)$languages_id . "'
                                    and poval.language_id = '" . (int)$languages_id . "'");
        $attributes_values = tep_db_fetch_array($attributes);
        if ($attributes_values['price_prefix'] == '+') {
          $attributes_addon_price += $attributes_values['options_values_price'];
			  } else if ($attributes_values['price_prefix'] == '-') {
          $attributes_addon_price -= $attributes_values['options_values_price'];
			  }
        echo '<br /><small><em> ' . $attributes_values['products_options_name'] . ': ' . $attributes_values['products_options_values_name'] . '</em></small>';
      } // end while attributes for product

      if (tep_not_null($products['specials_new_products_price'])) {
        $products_price = '<del>' . $currencies->display_price($products['products_price']+$attributes_addon_price, tep_get_tax_rate($products['products_tax_class_id']), $wishlist['customers_wishlist_quantity']) . '</del> <span class="productSpecialPrice">' . $currencies->display_price($products['specials_new_products_price']+$attributes_addon_price, tep_get_tax_rate($products['products_tax_class_id']), $wishlist['customers_wishlist_quantity']);
				if (tep_not_null($products['sale_expires'])) $products_price .= '<br /><small>' . BOX_TEXT_SALE_EXPIRES .  $products['sale_expires'] . '</small>';
				$products_price .= '</span>';
      } else {
        $products_price = $currencies->display_price($products['products_price']+$attributes_addon_price, tep_get_tax_rate($products['products_tax_class_id']), $wishlist['customers_wishlist_quantity']);
      }

/*******************************************************************
******* CHECK TO SEE IF PRODUCT HAS BEEN ADDED TO THEIR CART *******
*******************************************************************/

		if($cart->in_cart($wishlist['products_id'])) {
			echo '<br /><strong style="color: red">' . TEXT_ITEM_IN_CART . '</strong>';
		}

/*******************************************************************
********** CHECK TO SEE IF PRODUCT IS NO LONGER AVAILABLE **********
*******************************************************************/

		if($products['products_status'] == 0) {
			echo '<br /><strong style="color: red">' . TEXT_ITEM_NOT_AVAILABLE . '</strong>';
		}

	  $i++;
?>
    </td>
    <td><?php echo $products_price; ?></td>
    <td class="text-center"><?php if ($products['products_status'] != 0) echo tep_draw_checkbox_field('add_wishprod[' . $wishlist['products_id'] . ']',$wishlist['products_id']); ?></td>
  </tr>
  <?php
    }
?>
<tbody>
</table>
<?php
  if ($wishlist_split->number_of_rows > 0 && (PREV_NEXT_BAR_LOCATION == '2' || PREV_NEXT_BAR_LOCATION == '3')) {
?>
<div class="row">
  <div class="col-sm-6 pagenumber hidden-xs">
    <?php echo $wishlist_split->display_count(TEXT_DISPLAY_NUMBER_OF_WISHLIST); ?>
  </div>
  <div class="col-sm-6">
    <div class="pull-right pagenav"><ul class="pagination"><?php echo $wishlist_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></ul></div>
    <span class="pull-right"><?php echo TEXT_RESULT_PAGE; ?></span>
  </div>
</div>
<?php
	}
?>
<p class="text-right"><?php echo tep_draw_button(BUTTON_TEXT_ADD_CART, 'glyphicon glyphicon-shopping-cart', null, 'primary', '', 'btn btn-success'); ?></p>
</form>

<?php
} else { // Nothing in the customers wishlist

?>
  <div class="alert alert-danger"><?php echo BOX_TEXT_NO_ITEMS; ?></div>
  <div class="text-right"><?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'glyphicon glyphicon-chevron-right', tep_href_link('index.php')); ?></div>
<?php
}
?>
</div> <!-- .contentContainer //-->
<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
