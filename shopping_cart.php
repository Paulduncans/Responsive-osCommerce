<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require("includes/application_top.php");

  if ($cart->count_contents() > 0) {
    include(DIR_WS_CLASSES . 'payment.php');
    $payment_modules = new payment;
  }

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_SHOPPING_CART);

  $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_SHOPPING_CART));

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<?php
  if ($cart->count_contents() > 0) {
?>

<?php echo tep_draw_form('cart_quantity', tep_href_link(FILENAME_SHOPPING_CART, 'action=update_product')); ?>

<div class="contentContainer">
  <div class="contentText">

<?php
    $any_out_of_stock = 0;
    $products = $cart->get_products();
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
// Push all attributes information in an array
      if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
        while (list($option, $value) = each($products[$i]['attributes'])) {
          echo tep_draw_hidden_field('id[' . $products[$i]['id'] . '][' . $option . ']', $value);
          $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix
                                      from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
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
?>

    <table class="table table-striped">
      <tbody>
<?php
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
      echo '<tr>';

      echo '  <td valign="top" align="center"><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products[$i]['id']) . '">' . tep_image(DIR_WS_IMAGES . $products[$i]['image'], $products[$i]['name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a></td>' .
           '  <td valign="top"><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products[$i]['id']) . '"><strong>' . $products[$i]['name'] . '</strong></a>';

      if (STOCK_CHECK == 'true') {
        $stock_check = tep_check_stock($products[$i]['id'], $products[$i]['quantity']);
        if (tep_not_null($stock_check)) {
          $any_out_of_stock = 1;

          $products_name .= $stock_check;
        }
      }

      if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
        reset($products[$i]['attributes']);
        while (list($option, $value) = each($products[$i]['attributes'])) {
          echo '<br /><small><i> - ' . $products[$i][$option]['products_options_name'] . ' ' . $products[$i][$option]['products_options_values_name'] . '</i></small>';
        }
      }

      echo '</td>';

      echo '  <td valign="top">' . tep_draw_input_field('cart_quantity[]', $products[$i]['quantity'], 'style="width: 50px;"') . tep_draw_hidden_field('products_id[]', $products[$i]['id']) . ' ' . tep_draw_button(NULL, 'glyphicon-refresh', NULL, NULL, NULL, 'btn btn-info') . ' ' . tep_draw_button(NULL, 'glyphicon glyphicon-remove', tep_href_link(FILENAME_SHOPPING_CART, 'products_id=' . $products[$i]['id'] . '&action=remove_product'), NULL, NULL, 'btn btn-danger') . '</td>';
      echo '  <td valign="top">' . $products_name . '</td>' .
           '  <td align="right" valign="top"><strong>' . $currencies->display_price($products[$i]['final_price'], tep_get_tax_rate($products[$i]['tax_class_id']), $products[$i]['quantity']) . '</strong></td>' .
           '</tr>';
    }
?>

      </tbody>
    </table>

    <p class="text-right"><strong><?php echo SUB_TITLE_SUB_TOTAL; ?> <?php echo $currencies->format($cart->show_total()); ?></strong></p>

<?php
    if ($any_out_of_stock == 1) {
      if (STOCK_ALLOW_CHECKOUT == 'true') {
?>

    <div class="alert alert-warning"><?php echo OUT_OF_STOCK_CAN_CHECKOUT; ?></div>

<?php
      } else {
?>

    <div class="alert alert-danger"><?php echo OUT_OF_STOCK_CANT_CHECKOUT; ?></div>

<?php
      }
    }
?>

  </div>

  <div class="buttonSet">
    <span class="buttonAction"><?php echo tep_draw_button(IMAGE_BUTTON_CHECKOUT, 'glyphicon-chevron-right', tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'), 'primary', NULL, 'btn btn-success'); ?></span>
  </div>

<?php
    $initialize_checkout_methods = $payment_modules->checkout_initialization_method();

    if (!empty($initialize_checkout_methods)) {
?>
  <div class="clearfix"></div>
  <p class="text-right"><?php echo TEXT_ALTERNATIVE_CHECKOUT_METHODS; ?></p>

<?php
      reset($initialize_checkout_methods);
      while (list(, $value) = each($initialize_checkout_methods)) {
?>

  <p class="text-right"><?php echo $value; ?></p>

<?php
      }
    }
?>

</div>

</form>

<?php
  } else {
?>

<div class="contentContainer">
  <div class="contentText">
    <?php echo TEXT_CART_EMPTY; ?>

    <p class="text-right"><?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'glyphicon-chevron-right', tep_href_link(FILENAME_DEFAULT)); ?></p>
  </div>
</div>

<?php
  }

  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
