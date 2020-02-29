<?php
/*
  $Id: wishlist.php, 2.3.4 revision 3  2014/11/21 Dennis Blake
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Released under the GNU General Public License
*/
// This version, removes the product when adding to a cart now

  require('includes/application_top.php');
  require('includes/languages/' . $language . '/wishlist.php');

	$pg = (isset($_GET['page']) ? 'page=' . $_GET['page'] : '');

  $products = $wishList->get_products();
  for ($i=0, $n=sizeof($products); $i<$n; $i++) {
// Push all attributes information in an array
    if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
      while (list($option, $value) = each($products[$i]['attributes'])) {
        $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix
                                    from products_options popt, products_options_values poval, products_attributes pa
                                    where pa.products_id = '" . (int)$products[$i]['id'] . "'
                                    and pa.options_id = '" . (int)$option . "'
                                    and pa.options_id = popt.products_options_id
                                    and pa.options_values_id = '" . (int)$value . "'
                                    and pa.options_values_id = poval.products_options_values_id
                                    and popt.language_id = '" . (int)$languages_id . "'
                                    and poval.language_id = '" . (int)$languages_id . "'");
        $attributes_values = tep_db_fetch_array($attributes);
        $products[$i][$option]['products_options_name'] = $attributes_values['products_options_name'];
        $products[$i][$option]['options_values_id'] = $value;
        $products[$i][$option]['products_options_values_name'] = $attributes_values['products_options_values_name'];
        $products[$i][$option]['options_values_price'] = $attributes_values['options_values_price'];
        $products[$i][$option]['price_prefix'] = $attributes_values['price_prefix'];
      }
    }
  }

/****************** ADD PRODUCT TO SHOPPING CART ********************/

  if (isset($_POST['add_wishprod'])) {
  	if (isset($_POST['wlaction']) && $_POST['wlaction'] == 'cart') {
	  	for ($i=0, $n=sizeof($products); $i<$n; $i++) {
		  	if (isset($_POST['add_wishprod'][$products[$i]['id']]) && ($_POST['add_wishprod'][$products[$i]['id']] == $products[$i]['id'])) {
			    $cart->add_cart($products[$i]['id'], $cart->get_quantity($products[$i]['id'])+(int)$_POST['quantity'][$products[$i]['id']], $products[$i]['attributes']);
			    $wishList->remove($products[$i]['id']);
				}
			}
			if (DISPLAY_CART == 'true') tep_redirect(tep_href_link('shopping_cart.php'));
			tep_redirect(tep_href_link('wishlist.php')); // reload updated wishlist if not redirecting to cart
		}
	}

/***************** UPDATE WISH LIST QUANTITY ***********************/

  if (isset($_POST['wlaction']) && $_POST['wlaction'] == 'update_qty') {
	  for ($i=0, $n=sizeof($products); $i<$n; $i++) {
		 	if (isset($_POST['quantity'][$products[$i]['id']]) && is_numeric($_POST['quantity'][$products[$i]['id']])) {
				$qty = (int)$_POST['quantity'][$products[$i]['id']];
				if ($qty < 1) { // remove if quantity is 0 or less
		      $wishList->remove($products[$i]['id']);
				} elseif ($qty != $products[$i]['quantity']) { // update if quantity has changed
				  $wishList->update_quantity($products[$i]['id'], $qty, $products[$i]['attributes']);
				}
			}
		} //print_r($_POST); echo '<br><br><br><br>'; print_r($products);
		tep_redirect(tep_href_link('wishlist.php', $pg)); // reload updated wishlist
	}

/*******************************************************************
****************** DELETE PRODUCT FROM WISHLIST ********************
*******************************************************************/

  if (isset($_POST['add_wishprod'])) {
  	if (isset($_POST['wlaction']) && $_POST['wlaction'] == 'delete') {
	  	foreach ($_POST['add_wishprod'] as $key => $value) {
		  	if ($key == $value) $wishList->remove($value);
		  }
			tep_redirect(tep_href_link('wishlist.php')); // reload updated wishlist
	  }
  }

/*******************************************************************
************* EMAIL THE WISHLIST TO MULTIPLE FRIENDS ***************
*******************************************************************/

	$wishlist_not_empty = ($wishList->count_contents() > 0);
	$error = false;
	$guest_errors = "";
	$email_errors = "";
	$message_error = "";
	$from_name = $from_email = '';
	for ($x = 0; $x < 1; $x++) $friend[$x] = $email[$x] = '';
  if (isset($_POST['wlaction']) && ($_POST['wlaction'] == 'email') && isset($_POST['formid']) && ($_POST['formid'] == $sessiontoken) && $wishlist_not_empty) {

  	$message = tep_db_prepare_input($_POST['message']);
		if(strlen($message) < 1) {
			$error = true;
			$message_error .= "<div class=\"alert alert-danger\">" . ERROR_MESSAGE . "</div>";
		}			
    // check for links to other web sites, a sign that a spammer is trying to use this site to send spam
    $protocols = array('http://', 'https://', 'file://', 'ftp://', 'news://', 'mailto:', 'telnet://', 'ssh:');
    $check = strtolower($message);
    $thisdomain = HTTP_SERVER;
    $thisdomain = strtolower(substr($thisdomain, strpos($thisdomain, '://') + 3));
    foreach ($protocols as $p ) {
      $x = 0;
      while (strpos($check, $p, $x) !== false) {
        $x = strpos($check, $p, $x) + strlen($p);
        if ((substr($check, $x, strlen($thisdomain)) != $thisdomain) || !preg_match('/\/|\s/', substr($check, $x + strlen($thisdomain), 1))) {
          $error = true;
          $message_error .= "<div class=\"alert alert-danger\">" . ERROR_INVALID_LINK . "</div>";
        }
      }
    }

 		if(tep_session_is_registered('customer_id')) { // logged in
			$customer_query = tep_db_query("select customers_firstname, customers_lastname, customers_email_address from customers where customers_id = '" . (int)$customer_id . "'");
	  	if (tep_db_num_rows($customer_query) != 1 ) tep_redirect(tep_href_link('logoff.php', '', 'SSL')); // invalid customer id
	  	$customer = tep_db_fetch_array($customer_query);
	
			$from_name = $customer['customers_firstname'] . ' ' . $customer['customers_lastname'];
			$from_email = $customer['customers_email_address'];
			$subject = $customer['customers_firstname'] . ' ' . WISHLIST_EMAIL_SUBJECT;
			$link = tep_href_link('wishlist_public.php', "public_id=" . $customer_id);
	
			$body = $message . sprintf(WISHLIST_EMAIL_LINK, $from_name, $link, $link);
		} else { // guest
			$from_name = tep_db_prepare_input($_POST['your_name']);
			$from_email = tep_db_prepare_input($_POST['your_email']);
			if(strlen($from_name) < 1) {
				$error = true;
				$guest_errors .= "<div class=\"alert alert-danger\">" . ERROR_YOUR_NAME . "</div>";
			}
			if(strlen($from_email) < 1) {
				$error = true;
				$guest_errors .= "<div class=\"alert alert-danger\">" .ERROR_YOUR_EMAIL . "</div>";
			} elseif(!tep_validate_email($from_email)) {
				$error = true;
				$guest_errors .= "<div class=\"alert alert-danger\">" . ERROR_VALID_EMAIL . "</div>";
			}

			$subject = $from_name . ' ' . WISHLIST_EMAIL_SUBJECT;

			$prods = "";
			for ($i=0, $n=sizeof($products); $i<$n; $i++) {
				$prods .= '<a href="' . tep_href_link('product_info.php', 'products_id=' . $products[$i]['id']) .'">' . $products[$i]['name'] . "\n";
        if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes']))
          foreach($products[$i]['attributes'] as $option => $value) {
						$prods .= "  " . $products[$i][$option]['products_options_name'] . ": " . $products[$i][$option]['products_options_values_name'] . "\n";
					}
				$prods .= "  " . tep_href_link('product_info.php', 'products_id=' . $products[$i]['id']) . "</a>\n\n";
			}
			$body = $message . "\n\n" . $prods . "\n\n" . $from_name . WISHLIST_EMAIL_GUEST;
	  }

		//Check each posted name => email for errors.
    $email = tep_db_prepare_input($_POST['email']);
    $friend = tep_db_prepare_input($_POST['friend']);
		for ($j=0; $j < sizeof($friend); $j++) {
		  $friend[$j] = $friend[$j];
			if($j == 0) {
				if($friend[0] == '' && $email[0] == '') {
					$error = true;
					$email_errors .= "<div class=\"alert alert-danger\">" . ERROR_ONE_EMAIL . "</div>";
				}
			}

			if(isset($friend[$j]) && $friend[$j] != '') {
				if(strlen($email[$j]) < '1') {
					$error = true;
					$email_errors .= "<div class=\"alert alert-danger\">" . ERROR_ENTER_EMAIL . "</div>";
				} elseif(!tep_validate_email($email[$j])) {
					$error = true;
					$email_errors .= "<div class=\"alert alert-danger\">" . ERROR_VALID_EMAIL . "</div>";
				}
			}

			if(isset($email[$j]) && $email[$j] != '') {
				if(strlen($friend[$j]) < '1') {
					$error = true;
					$email_errors .= "<div class=\"alert alert-danger\">" . ERROR_ENTER_NAME . "</div>";
				}
			}
		}

    // check for attempt to send email from another page besides this sites Wishlist script
    if (substr($_SERVER['HTTP_REFERER'], 0, strpos($_SERVER['HTTP_REFERER'], '.php') + 4) != tep_href_link('wishlist.php')) {
      if (tep_session_is_registered('customer_id')) {
        $cid = $customer_id;
      } else {
        $cid = TEXT_SPAM_NO_ID;
      }
      $spammsg = sprintf(TEXT_SPAM_MESSAGE, date('l F j, Y  H:i:s'), $cid, $from_name, $from_email, $_SERVER['HTTP_REFERER'], tep_get_ip_address(), $_SERVER['REMOTE_PORT'], $_SERVER['HTTP_USER_AGENT']) . $message;
      tep_mail(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, TEXT_SPAM_SUBJECT, $spammsg, $from_name, $from_email);
      foreach ($_SESSION as $key => $value) unset($_SESSION[$key]);
      echo ERROR_SPAM_BLOCKED;
      tep_exit();
    }

    $actionRecorder = new actionRecorder('ar_wish_list', (tep_session_is_registered('customer_id') ? $customer_id : null), $from_name);
    if (!$actionRecorder->canPerform()) {
      $error = true;

      $actionRecorder->record(false);

      $messageStack->add('wishlist', sprintf(ERROR_ACTION_RECORDER, (defined('MODULE_ACTION_RECORDER_WISH_LIST_EMAIL_MINUTES') ? (int)MODULE_ACTION_RECORDER_WISH_LIST_EMAIL_MINUTES : 15)));
    }

		if($error == false) {
			for ($j=0; $j < sizeof($friend); $j++) {
				if($friend[$j] != '') {
					tep_mail($friend[$j], $email[$j], $subject, $friend[$j] . ",\n\n" . $body, $from_name, $from_email);
				}
			//Clear Values
				$friend[$j] = "";
				$email[$j] = "";
			}
			$message = "";
     	$actionRecorder->record();
     	$messageStack->add('wishlist', WISHLIST_SENT, 'success');
		}
  }

 $breadcrumb->add(NAVBAR_TITLE_WISHLIST, tep_href_link('wishlist.php', '', 'SSL'));
 
 require('includes/template_top.php');
 if ($messageStack->size('wishlist') > 0) {
    echo $messageStack->output('wishlist');
  }
?>

<?php echo $guest_errors; ?>
<?php echo $email_errors; ?>
<?php echo $message_error; ?>

<div class="page-header">
<h1><?php echo HEADING_TITLE; ?></h1>
</div>

<?php
  if ($messageStack->size('product_action') > 0) {
    echo $messageStack->output('product_action');
  }
?>

<div class="contentContainer">
<?php echo tep_draw_form('wishlist_form', tep_href_link('wishlist.php', $pg), 'post', 'class="form-horizontal"', true);

// display split-page-number-links
    function wlist_display_links($max_page_links, $parameters = '') {
      global $request_type, $pages, $page;

      $display_links_string = '';
      if (tep_not_null($parameters) && (substr($parameters, -1) != '&')) $parameters .= '&';

// previous button - not displayed on first page
      if ($page > 1) {
		$display_links_string .= '<li><a href="' . tep_href_link('wishlist.php', $parameters . 'page=' . ($page - 1), $request_type) . '" class="pageResults" title=" ' . PREVNEXT_TITLE_PREVIOUS_PAGE . ' ">&laquo;</a></li>';
      } else {
        $display_links_string .= '<li class="disabled"><span>&laquo;</span></li>';
      }
// check if number_of_pages > $max_page_links
      $cur_window_num = intval($page / $max_page_links);
      if ($page % $max_page_links) $cur_window_num++;

      $max_window_num = intval($pages / $max_page_links);
      if ($pages % $max_page_links) $max_window_num++;

// previous window of pages
      if ($cur_window_num > 1) $display_links_string .= '<li><a href="' . tep_href_link('wishlist.php', $parameters . 'page=' . (($cur_window_num - 1) * $max_page_links), $request_type) . '" class="pageResults" title=" ' . sprintf(PREVNEXT_TITLE_PREV_SET_OF_NO_PAGE, $max_page_links) . ' ">...</a></li>';

// page nn button
      for ($jump_to_page = 1 + (($cur_window_num - 1) * $max_page_links); ($jump_to_page <= ($cur_window_num * $max_page_links)) && ($jump_to_page <= $pages); $jump_to_page++) {
        if ($jump_to_page == $page) {
          //$display_links_string .= '<li class="active">' . $jump_to_page . '<span class="sr-only">(current)</span></li>';
		  $display_links_string .= '<li class="active"><a href="' . tep_href_link('wishlist.php', $parameters . 'page=' . $jump_to_page, $request_type) . '" title=" ' . sprintf(PREVNEXT_TITLE_PAGE_NO, $jump_to_page) . ' ">' . $jump_to_page . '<span class="sr-only">(current)</span></a></li>';
        } else {
          $display_links_string .= '<li><a href="' . tep_href_link('wishlist.php', $parameters . 'page=' . $jump_to_page, $request_type) . '" class="pageResults" title=" ' . sprintf(PREVNEXT_TITLE_PAGE_NO, $jump_to_page) . ' ">' . $jump_to_page . '</a></li>';
        }
      }

// next window of pages
      if ($cur_window_num < $max_window_num) $display_links_string .= '<li><a href="' . tep_href_link('wishlist.php', $parameters . 'page=' . (($cur_window_num) * $max_page_links + 1), $request_type) . '" class="pageResults" title=" ' . sprintf(PREVNEXT_TITLE_NEXT_SET_OF_NO_PAGE, $max_page_links) . ' ">...</a></li>';

// next button
      if (($page < $pages) && ($pages != 1)) {
	    $display_links_string .= '<li><a href="' . tep_href_link('wishlist.php', $parameters . 'page=' . ($page + 1), $request_type) . '" class="pageResults" title=" ' . PREVNEXT_TITLE_NEXT_PAGE . ' ">&raquo;</a></li>';
	  } else {
		$display_links_string .= '<li class="disabled"><span>&raquo;</span></li>';
	  }
      return $display_links_string;
      }

if ($wishList->count_contents() > 0) {
  $n=sizeof($products);
	$pages = ceil($n / MAX_DISPLAY_WISHLIST_PRODUCTS);
	$page = (isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1);
	if ($page > $pages) $page = $pages;
	$offset = (MAX_DISPLAY_WISHLIST_PRODUCTS * ($page - 1));
	$limit = $page * MAX_DISPLAY_WISHLIST_PRODUCTS;
	if ($limit > $n) $limit = $n;

	if ($n > 0 && (PREV_NEXT_BAR_LOCATION == '1' || PREV_NEXT_BAR_LOCATION == '3')) {
?>

<div class="row">
  <div class="col-sm-6 pagenumber hidden-xs">
	<?php echo sprintf(TEXT_DISPLAY_NUMBER_OF_WISHLIST, $offset+1, $limit, $n); ?>
  </div>
  <div class="col-sm-6">
    <div class="pull-right pagenav"><ul class="pagination"><?php echo wlist_display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></ul></div>
 <span class="pull-right"><?php echo TEXT_RESULT_PAGE; ?></span>
  </div>
</div>	

<?php
  }
?>
  <table class="table table-striped table-hover table-responsive">
  <thead>
    <tr>
		<th class="text-center hidden-xs"><?php echo BOX_TEXT_IMAGE; ?></th>
		<th><?php echo BOX_TEXT_PRODUCT; ?></th>
		<th><?php echo BOX_TEXT_QTY; ?></th>
		<th class="text-center"><?php echo BOX_TEXT_PRICE; ?></th>
		<th class="text-center"><?php echo BOX_TEXT_SELECT; ?></th>
    </tr>
	</thead>
	<tbody>
<?php for ($i=$offset; $i<$limit; $i++) { ?>
    <tr>
      <td class="hidden-xs">
	        <?php
			   $image = NULL;
		  	   $image = tep_image('images/' . $products[$i]['image'], $products[$i]['name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT);	 
			    if ($products[$i]['products_status'] == 0) {
				    echo $image; 
			    } else {
					echo '<a href="' . tep_href_link('product_info.php', 'products_id=' . $products[$i]['id'], 'NONSSL') . '">' . $image . '</a>';
				}
			?>
	   </td>
        <td class="text-left"><strong><?php
		    if($products[$i]['products_status'] == 0) {
		      echo $products[$i]['products_name']; 
			} else {
			  echo '<a href="' . tep_href_link('product_info.php', 'products_id=' . $products[$i]['id'], 'NONSSL') .'">' . $products[$i]['name'] . '</a>';
			}
			  echo '</strong>';
			$att_name = "";
          if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
						$att_name = "<small><em>";
            foreach($products[$i]['attributes'] as $option => $value) {
						  $att_name .= "<br />" . $products[$i][$option]['products_options_name'] . ": " . $products[$i][$option]['products_options_values_name'];
					  }
						$att_name .= '</em></small>';
          }
					echo $att_name;
/*******************************************************************
******* CHECK TO SEE IF PRODUCT HAS BEEN ADDED TO THEIR CART *******
*******************************************************************/

			if($cart->in_cart($products[$i]['id'])) {
				echo '<br /><strong style="color: red">' . TEXT_ITEM_IN_CART . '</strong>';
			}

/*******************************************************************
********** CHECK TO SEE IF PRODUCT IS NO LONGER AVAILABLE **********
*******************************************************************/

   			if($products[$i]['products_status'] == 0) {
   				echo '<br /><strong style="color: red">' . TEXT_ITEM_NOT_AVAILABLE . '</strong>';
  			}
?></td>
<td><?php echo tep_draw_input_field('quantity[' . $products[$i]['id'] . ']', $products[$i]['quantity'], 'style="width:60px; text-align:center;"') ?></td>
      <td class="text-center"><?php
			  $attributes_addon_price = $wishList->attributes_price($products[$i]['id']);
			  if (isset($products[$i]['sale_price'])) {
          $products_price = '<del>' . $currencies->display_price($products[$i]['price']+$attributes_addon_price, tep_get_tax_rate($products[$i]['tax_class_id']), $products[$i]['quantity']) . '</del> <span class="productSpecialPrice">' . $currencies->display_price($products[$i]['sale_price']+$attributes_addon_price, tep_get_tax_rate($products[$i]['tax_class_id']), $products[$i]['quantity']);
				  if (tep_not_null($products[$i]['sale_expires'])) $products_price .= '<br /><small>' . BOX_TEXT_SALE_EXPIRES .  $products[$i]['sale_expires'] . '</small>';
				  $products_price .= '</span>';
        } else {
          $products_price = $currencies->display_price($products[$i]['price']+$attributes_addon_price, tep_get_tax_rate($products[$i]['tax_class_id']), $products[$i]['quantity']);
				}
        echo $products_price; ?></td>
		 <td class="text-center"><?php

/*******************************************************************
* PREVENT THE ITEM FROM BEING ADDED TO CART IF NO LONGER AVAILABLE *
*******************************************************************/

			if($products[$i]['products_status'] != 0) {
				echo tep_draw_checkbox_field('add_wishprod[' . $products[$i]['id'] . ']', $products[$i]['id']);
			}
?></td>
    </tr>
<?php } ?>
    </tbody>
  </table>
  <hr />
  <p class="text-right"><strong><?php echo TEXT_WISHLIST_TOTAL . $currencies->format($wishList->show_total()); ?></strong></p>
  <br />
  <br />
<?php
  if ($n > 0 && (PREV_NEXT_BAR_LOCATION == '2' || PREV_NEXT_BAR_LOCATION == '3')) {
?>
<div class="row">
  <div class="col-sm-6 pagenumber hidden-xs">
	<?php echo sprintf(TEXT_DISPLAY_NUMBER_OF_WISHLIST, $offset+1, $limit, $n); ?>
  </div>
  <div class="col-sm-6">
    <div class="pull-right pagenav"><ul class="pagination"><?php echo wlist_display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></ul></div>
 <span class="pull-right"><?php echo TEXT_RESULT_PAGE; ?></span>
  </div>
</div>	
<?php
	}
?>
  
<div class="row">
<div class="col-sm-4" style="margin-top:10px;">
<?php echo tep_draw_button(BUTTON_TEXT_DELETE, 'glyphicon glyphicon-trash', null, 'primary', array('params' => 'name="wlaction" value="delete"'), 'btn btn-danger'); ?>
</div>   
<div class="col-sm-4 text-center" style="margin-top:10px;">
<?php echo tep_draw_button(BUTTON_UPDATE_QTY, 'glyphicon glyphicon-refresh', null, 'primary', array('params' => 'name="wlaction" value="update_qty"'), 'btn btn-primary'); ?>
</div>  
<div class="col-sm-4 text-right" style="margin-top:10px;">
<?php echo tep_draw_button(BUTTON_TEXT_ADD_CART, 'glyphicon glyphicon-shopping-cart', null, 'primary', array('params' => 'name="wlaction" value="cart"'), 'btn btn-success'); ?>
</div> 
</div>  
<br />
<!--
/*******************************************************************
*********** CODE TO SPECIFY HOW MANY EMAILS TO DISPLAY *************
*******************************************************************/
//-->
<?php if(!tep_session_is_registered('customer_id')) { ?>

   <div class="alert alert-info">
		<?php 
 		echo WISHLIST_EMAIL_TEXT_GUEST; 
		?>
	</div>
	<span class="inputRequirement pull-right text-right"><?php echo FORM_REQUIRED_INFORMATION; ?></span>
	<div class="form-group has-feedback">
      <label for="wishName" class="control-label col-sm-3"><?php echo TEXT_YOUR_NAME . ':'; ?></label>
      <div class="col-sm-6">
		<?php 
			echo tep_draw_input_field('your_name', NULL, 'id="wishName" placeholder="' . TEXT_YOUR_NAME . '"', $from_name); 
			echo FORM_REQUIRED_INPUT;
		?>
	   </div>
    </div>	
	<div class="form-group has-feedback">
      <label for="wishEmail" class="control-label col-sm-3"><?php echo TEXT_YOUR_EMAIL . ':'; ?></label>
      <div class="col-sm-6">
		<?php 
			echo tep_draw_input_field('your_email', NULL, 'id="wishEmail" placeholder="' . TEXT_YOUR_EMAIL . '"', $from_email);	
			echo FORM_REQUIRED_INPUT;
		?>
      </div>
    </div>
 <hr />	
  <?php
	} else {
?>
	   <div class="alert alert-info">
		<?php 
			echo WISHLIST_EMAIL_TEXT; 
		?>
	</div>
  <?php
	}
?>
               <div id="EmailList">


		<div class="form-group has-feedback">
		  <label for="recvName" class="control-label col-sm-3"><?php echo TEXT_NAME . ':'; ?></label>
		  <div class="col-sm-6">
			<?php 
				echo tep_draw_input_field('friend[]', NULL, 'id="recvName" placeholder="' . TEXT_NAME . '"'); 
				echo FORM_REQUIRED_INPUT;
			?>
		   </div>
		</div>	
		<div class="form-group has-feedback">
		  <label for="recvEmail" class="control-label col-sm-3"><?php echo TEXT_EMAIL . ':'; ?></label>
		  <div class="col-sm-6">
			<?php 
				echo tep_draw_input_field('email[]', NULL, 'id="recvEmail" placeholder="' . TEXT_EMAIL . '"');
				echo FORM_REQUIRED_INPUT;
			?>
		   </div>
		</div>
<hr />

 </div>

<div class="button_more text-right">
  <a class="btn btn-default" role="button" href="#" onclick="addNewEmailField();return false;"><i class="glyphicon glyphicon-plus"></i><?php echo TEXT_WISHLIST_ADD_EMAIL; ?></a>
</div>

<hr />
 
  
  
<?php echo tep_draw_separator('pixel_trans.gif', '20', '20'); ?>
  
  <div class="form-group has-feedback">
	<label for="wishMessage" class="control-label col-sm-3"><?php echo TEXT_MESSAGE . ':'; ?></label>
   <div class="col-sm-7">
	<?php 
		echo tep_draw_textarea_field('message', 'soft', 45, 5, NULL, 'id="wishMessage" placeholder="' . TEXT_MESSAGE . '"'); 
		echo FORM_REQUIRED_INPUT;
	?>
   </div>
  </div>
  <p class="text-right"><?php echo tep_draw_button(WISHLIST_EMAIL_BUTTON, 'glyphicon glyphicon-envelope', null, 'primary', array('params' => 'name="wlaction" value="email"')); ?></p>

<?php
} else { // Nothing in the customers wishlist
?>
  <div class="alert alert-danger"><?php echo BOX_TEXT_NO_ITEMS; ?></div>
  <div class="text-right"><?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'glyphicon glyphicon-chevron-right', tep_href_link('index.php')); ?></div>
<?php } ?>
  </form>
</div> <!-- .contentContainer //-->
<?php 
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>

<script>
function addNewEmailField() {
  $('#EmailList').append('<div id="added"><div class="form-group has-feedback"><label for="recvName" class="control-label col-sm-3"><?php echo TEXT_NAME . ':'; ?></label><div class="col-sm-6"><?php echo tep_draw_input_field('friend[]', NULL, 'id="recvName" placeholder="' . TEXT_NAME . '"'); echo FORM_REQUIRED_INPUT;?></div></div><div class="form-group has-feedback"> <label for="recvEmail" class="control-label col-sm-3"><?php echo TEXT_EMAIL . ':'; ?></label> <div class="col-sm-6"><?php echo tep_draw_input_field('email[]', NULL, 'id="recvEmail" placeholder="' . TEXT_EMAIL . '"'); echo FORM_REQUIRED_INPUT;?></div></div><hr /></div></div>');
$('.button_more').replaceWith('<div class="row button_added"><div class="col-sm-6"><a class="btn btn-default btn-block" role="button" href="#" onclick="addNewEmailField();return false;"><i class="glyphicon glyphicon-plus"></i><?php echo TEXT_WISHLIST_ADD_EMAIL; ?></a></div><div class="col-sm-6"><a class="btn btn-default btn-block" role="button" href="#" onclick="removeEmailField();return false;"><i class="glyphicon glyphicon-minus"></i><?php echo TEXT_WISHLIST_REMOVE_EMAIL; ?></a></div></div>');
}
function removeEmailField() {
  $('#added').remove();
}
</script>
