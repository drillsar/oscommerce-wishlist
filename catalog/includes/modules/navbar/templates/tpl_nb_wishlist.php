<li class="nav-item dropdown nb-wishlist">
  <a class="nav-link dropdown-toggle" href="#" id="navDropdownWishlist" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <?php echo sprintf(MODULE_NAVBAR_WISHLIST_CART_CONTENTS, $wishList->count_contents()); ?>
  </a>

  <div class="dropdown-menu<?php echo $menu_align; ?>" aria-labelledby="navDropdownWishlist">
    <?php
    echo '<a class="dropdown-item" href="' . tep_href_link('wishlist.php') . '">' . sprintf(MODULE_NAVBAR_WISHLIST_CART_HAS_CONTENTS, $wishList->count_contents(), $currencies->format($wishList->show_total())) . '</a>';
    if ($wishList->count_contents() > 0) {
      echo '<div class="dropdown-divider"></div>' . PHP_EOL;
      echo '<div class="dropdown-cart-list">';
        $products = $wishList->get_products();
        foreach ($products as $k => $v) {
          echo sprintf(MODULE_NAVBAR_WISHLIST_CART_PRODUCT, $v['id'], $v['quantity'], $v['name']);
        }
      echo '</div>' . PHP_EOL;
  //    echo '<div class="dropdown-divider"></div>' . PHP_EOL;
   //   echo '<a class="dropdown-item" href="' . tep_href_link('checkout_shipping.php', '', 'SSL') . '">' . MODULE_NAVBAR_SHOPPING_CART_CHECKOUT . '</a>' . PHP_EOL;
    }
    ?>
  </div>
</li>
<?php
/*
  Copyright (c) 2018, G Burton
  All rights reserved.

  Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

  1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.

  2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

  3. Neither the name of the copyright holder nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND >
*/
?>

