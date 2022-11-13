<?php
/**
 * Package Rabatt fuer Zahlungsart
 * @copyright Copyright 2022 webchills (www.webchills.at)
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * Zen Cart German Version - www.zen-cart-pro.at
 * @copyright Portions Copyright 2003 osCommerce
 * @license https://www.zen-cart-pro.at/license/3_0.txt GNU General Public License V3.0
 * @version $Id: ot_payment_method_discount.php 2022-11-13 19:21:16Z webchills $
 */
 

if (!defined('MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_PAYMENT_MODULES')) define('MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_PAYMENT_MODULES', 'eustandardtransfer');
if (!defined('MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_ENABLED')) define('MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_ENABLED', 'false');
if (!defined('MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_SHIPPING')) define('MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_SHIPPING', 'true');
 
  class ot_payment_method_discount extends base
   {
    public $title;
    public  $output;
    protected $_check;
    protected $isEnabled = false;
    

    public function __construct() 
    {
      $this->code = 'ot_payment_method_discount';
      $this->title = MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_TITLE;
      $this->description = MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_DESCRIPTION;
      $this->sort_order = defined('MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_SORT_ORDER') ? (int)MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_SORT_ORDER : null;
      if ($this->sort_order === null) {
            return false;
        }
      $this->isEnabled = (defined('MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_ENABLED') && MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_ENABLED == 'true');
      

      $this->payment_modules = explode(',', MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_PAYMENT_MODULES); 
      $this->output = array();
    }
    
    public function process() {
      global $order, $currencies;

      if ($this->isEnabled) {
        switch (MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_DESTINATION) {
          case 'national':
            if ($order->delivery['country_id'] == STORE_COUNTRY) {
                $pass = true;
            }
              break;
          case 'international':
            if ($order->delivery['country_id'] != STORE_COUNTRY) $pass = true; break;
          case 'both':
            $pass = true; break;
          default:
            $pass = false; break;
        }

        if (($pass == true) &&  (isset($_SESSION['payment']) && in_array($_SESSION['payment'], $this->payment_modules))) {
          $charge_it = 'true';
          if ($charge_it == 'true') {           
                       
            $key = array_search($_SESSION['payment'], $this->payment_modules);
            $this->payment_discounts = explode(',', MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_AMOUNT);
            $this->payment_discount = $this->payment_discounts[$key]; 
            
           $payment_module_discount = '';
           $discountbasis = 0;

          
            if (MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_SHIPPING === 'false') {
            	  
            	 $discountbasis = zen_round(($order->info['total'] - $order->info['shipping_cost']),2);
            	} else {
            		$discountbasis = zen_round($order->info['total'], 2);
            	}
		
	    // calculate from flat fee or percentage
            if (substr($this->payment_discount, -1) == '%') {
	          $payment_module_discount = (($discountbasis) * ((int)$this->payment_discount/100));             
            } else {
            
              $payment_module_discount = $this->payment_discount;
            }   
            $order->info['total'] += $payment_module_discount;          
            

            $this->output[] = array('title' => $this->title . ':',
                                    'text' => $currencies->format($payment_module_discount, true, $order->info['currency'], $order->info['currency_value']),
                                    'value' => $payment_module_discount);
          }
        }
      }
    }
    
    
    public function check() 
    {
        if (!isset($this->_check)) {
            $check = $GLOBALS['db']->Execute(
                "SELECT configuration_value
                   FROM " . TABLE_CONFIGURATION . "
                  WHERE configuration_key = 'MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_STATUS'
                  LIMIT 1"
            );
            $this->_check = $check->RecordCount();
        }
        return $this->_check;
    }

    public function keys()
     {
      return array(
      'MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_STATUS',
      'MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_SORT_ORDER',
      'MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_AMOUNT',
      'MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_ENABLED',
      'MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_SHIPPING',           
      'MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_PAYMENT_MODULES', 
      'MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_DESTINATION');
    }

    public function install() {
      global $db;
      $db->Execute('insert into ' . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('This module is installed', 'MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_STATUS', 'true', '', '6', '1','zen_cfg_select_option(array(\'true\'), ', now())");
      $db->Execute('insert into ' . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_SORT_ORDER', '298', 'Sort order of display.', '6', '2', now())");
      $db->Execute('insert into ' . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable discount for payment method?', 'MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_ENABLED', 'false', 'Do you want to allow payment module discounts?', '6', '3', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
      $db->Execute('insert into ' . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added) values ('Payment Modules', 'MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_PAYMENT_MODULES', 'eustandardtransfer', 'Enter the payment module codes separate by commas (no spaces)', '6', '4', '', now())");
      $db->Execute('insert into ' . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added) values ('Amount', 'MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_AMOUNT', '-2%', 'This module calculates either a percentage or fixed discount. For a percentage discount you always enter your desired discount with % behind it<br/>Examples:<br/>Discount of 3%: -3%<br/>Discount of 10 percent: -10%<br/>If you provide discounts for different payment modules below, e.g. eustandardtransfer,sofort_su, then define them comma-separated, e.g.:<br/>-10%,-2%<br/><br/>To deduct a fixed amount, omit the percent sign and define e.g. for a deduction of 2 euros:<br/>-2.0000', '6', '5', '', now())");
      $db->Execute('insert into ' . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable discount for the following destinations', 'MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_DESTINATION', 'both', 'Attach payment module discount for orders sent to the set destination.', '6', '6', 'zen_cfg_select_option(array(\'national\', \'international\', \'both\'), ', now())");
      $db->Execute('insert into ' . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Include shipping costs?', 'MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_SHIPPING', 'true', 'Do you want to calculate the discount for the sum of subtotal and shipping costs?', '6', '7', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");

      
      // www.zen-cart-pro.at languages_id==43 START
      $db->Execute('insert into ' . TABLE_CONFIGURATION_LANGUAGE   . " (configuration_title, configuration_key, configuration_language_id, configuration_description, date_added) values ('Dieses Modul ist installiert.', 'MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_STATUS', '43', 'true', now())");
      $db->Execute('insert into ' . TABLE_CONFIGURATION_LANGUAGE   . " (configuration_title, configuration_key, configuration_language_id, configuration_description, date_added) values ('Sortierreihenfolge', 'MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_SORT_ORDER', '43', 'Voreinstellung: 298', now())");
      $db->Execute('insert into ' . TABLE_CONFIGURATION_LANGUAGE   . " (configuration_title, configuration_key, configuration_language_id, configuration_description, date_added) values ('Wollen Sie den Rabatt aktivieren?', 'MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_ENABLED', '43', 'Um Rabatt für eine Zahlungsart zu aktivieren, müssen Sie hier auf true stellen.', now())");
      $db->Execute('insert into ' . TABLE_CONFIGURATION_LANGUAGE   . " (configuration_title, configuration_key, configuration_language_id, configuration_description, date_added) values ('Zahlungsarten für Rabatt', 'MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_PAYMENT_MODULES', '43', 'Tragen Sie hier die Zahlungsmodule für die der Rabatt gewährt werden soll ein mit Komma getrennt und ohne Leerzeichen.', now())");
      $db->Execute('insert into ' . TABLE_CONFIGURATION_LANGUAGE   . " (configuration_title, configuration_key, configuration_language_id, configuration_description, date_added) values ('Rabattbetrag', 'MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_AMOUNT', '43', 'Dieses Modul berechnet entweder einen prozentuellen oder fixen Rabatt. Für einen prozentuellen Rabatt tragen Sie immer Ihren gewünschten Rabatt mit % dahinter ein<br/>Beispiele:<br/>Rabatt von 3%: -3%<br/>Rabatt von 10 Prozent: -10%<br/>Wenn Sie unten Rabatte für unterschiedliche Zahlungsmodule vorsehen, z.B. eustandardtransfer,sofort_su, dann definieren Sie diese kommagetrennt, z.B. so:<br/>-10%,-2%<br/><br/>Um einen Fixbetrag abziehen zu lassen, lassen Sie das Prozentzeichen weg und definieren z.B. für einen Abzug von 2 Euro:<br/>-2.0000', now())");
      $db->Execute('insert into ' . TABLE_CONFIGURATION_LANGUAGE   . " (configuration_title, configuration_key, configuration_language_id, configuration_description, date_added) values ('Für welche Destinationen soll der Rabatt gelten?', 'MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_DESTINATION', '43', 'National, International oder für beide (both)?', now())");
      $db->Execute('insert into ' . TABLE_CONFIGURATION_LANGUAGE   . " (configuration_title, configuration_key, configuration_language_id, configuration_description, date_added) values ('Versandkosten berücksichtigen?', 'MODULE_ORDER_TOTAL_PAYMENT_METHOD_DISCOUNT_SHIPPING', '43', 'Wollen Sie bei der Berechnung des Rabatts die Versandkosten berücksichtigen?<br/>Zwischensumme+Versandkosten als Basis der Rabattberechnung = true<br/>nur Zwischensumme als Basis der Rabattberechnung = false', now())");
      

      // www.zen-cart-pro.at languages_id==43  END
    }

    public function remove() {
	  global $db;
      $db->Execute('delete from ' . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
      // www.zen-cart-pro.at languages_id == delete all
      $db->Execute('delete from ' . TABLE_CONFIGURATION_LANGUAGE . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
  }