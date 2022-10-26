<?php
/**
 * Package Rabatt fuer Zahlungsart
 * @copyright Copyright 2022 webchills (www.webchills.at) 
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * Zen Cart German Version - www.zen-cart-pro.at
 * @copyright Portions Copyright 2003 osCommerce
 * @license https://www.zen-cart-pro.at/license/3_0.txt GNU General Public License V3.0
 * @version $Id: loader_ot_payment_method_discount 2022-10-26 17:06:16Z webchills $
 */  
                            
if (defined('MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_ENABLED') && (MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_ENABLED == 'true')) {                                                            
  $loaders[] = array('conditions' => array('pages' => array('checkout', 'quick_checkout')),
										  'jscript_files' => array(
										  'jquery/jquery_ot_payment_method_discount.js' => 1										
                      )
                    );  
}