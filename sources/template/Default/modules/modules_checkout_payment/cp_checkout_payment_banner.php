<?php
/**
 *
 *  @copyright 2008 - https://www.clicshopping.org
 *  @Brand : ClicShopping(Tm) at Inpi all right Reserved
 *  @Licence GPL 2 & MIT
 *  @licence MIT - Portion of osCommerce 2.4
 *  @Info : https://www.clicshopping.org/forum/trademark/
 *
 */

  use ClicShopping\Apps\Marketing\BannerManager\Classes\Shop\Banner;
  use ClicShopping\OM\Registry;
  use ClicShopping\OM\CLICSHOPPING;


  class cp_checkout_payment_banner {
    public $code;
    public $group;
    public string $title;
    public string $description;
    public ?int $sort_order = 0;
    public bool $enabled = false;

    public function __construct() {
      $this->code = get_class($this);
      $this->group = basename(__DIR__);

      $this->title = CLICSHOPPING::getDef('module_checkout_payment_banner_tile');
      $this->description = CLICSHOPPING::getDef('module_checkout_payment_banner_description');

      if (defined('MODULE_CHECKOUT_PAYMENT_BANNER_STATUS')) {
        $this->sort_order = MODULE_CHECKOUT_PAYMENT_BANNER_SORT_ORDER;
        $this->enabled = (MODULE_CHECKOUT_PAYMENT_BANNER_STATUS == 'True');
      }
     }

    public function execute() {

      $CLICSHOPPING_Template = Registry::get('Template');
      $CLICSHOPPING_Service = Registry::get('Service');
      $CLICSHOPPING_Banner = Registry::get('Banner');

      if (isset($_GET['Checkout']) && isset($_GET['Billing'])) {

        $content_width = (int)MODULE_CHECKOUT_PAYMENT_BANNER_CONTENT_WIDTH;
        $payment_process_order_banner = '';
        
        if ($CLICSHOPPING_Service->isStarted('Banner')) {
          if ($banner = $CLICSHOPPING_Banner->bannerExists('dynamic',  MODULE_CHECKOUT_PAYMENT_BANNER_BANNER_GROUP)) {
            $payment_process_order_banner = $CLICSHOPPING_Banner->displayBanner('static', $banner) . '<br /><br />';
          }
        }

        $payment_process = '  <!-- processing_payment_information -->' . "\n";

        ob_start();
        require_once($CLICSHOPPING_Template->getTemplateModules($this->group . '/content/checkout_payment_banner'));

        $payment_process .= ob_get_clean();

        $payment_process .= '<!--  processing_payment_information -->' . "\n";

        $CLICSHOPPING_Template->addBlock($payment_process, $this->group);
      }
    } // public function execute

    public function isEnabled() {
      return $this->enabled;
    }

    public function check() {
      return defined('MODULE_CHECKOUT_PAYMENT_BANNER_STATUS');
    }

    public function install() {
      $CLICSHOPPING_Db = Registry::get('Db');

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Do you want to enable this module ?',
          'configuration_key' => 'MODULE_CHECKOUT_PAYMENT_BANNER_STATUS',
          'configuration_value' => 'True',
          'configuration_description' => 'Do you want to enable this module in your shop ?',
          'configuration_group_id' => '6',
          'sort_order' => '1',
          'set_function' => 'clic_cfg_set_boolean_value(array(\'True\', \'False\'))',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Please indicate the group where the banner belongs',
          'configuration_key' => 'MODULE_CHECKOUT_PAYMENT_BANNER_BANNER_GROUP',
          'configuration_value' => SITE_THEMA.'_checkout_payment',
          'configuration_description' => 'Please indicate the group where the banner belongs <br /> <br /> <strong> Note: </strong> <br /> <i> The group will be indicated when creating the banner in the Marketing section / Banner management </i>',
          'configuration_group_id' => '6',
          'sort_order' => '3',
          'set_function' => '',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Please select the width of the display?',
          'configuration_key' => 'MODULE_CHECKOUT_PAYMENT_BANNER_CONTENT_WIDTH',
          'configuration_value' => '12',
          'configuration_description' => 'Please enter a number between 1 and 12',
          'configuration_group_id' => '6',
          'sort_order' => '1',
          'set_function' => 'clic_cfg_set_content_module_width_pull_down',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Sort order',
          'configuration_key' => 'MODULE_CHECKOUT_PAYMENT_BANNER_SORT_ORDER',
          'configuration_value' => '120',
          'configuration_description' => 'Sort order of display. Lowest is displayed first. The sort order must be different on every module',
          'configuration_group_id' => '6',
          'sort_order' => '4',
          'set_function' => '',
          'date_added' => 'now()'
        ]
      );

      return $CLICSHOPPING_Db->save('configuration', ['configuration_value' => '1'],
                                                ['configuration_key' => 'WEBSITE_MODULE_INSTALLED']
      );
    }

    public function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }

    public function keys() {
      return array(
        'MODULE_CHECKOUT_PAYMENT_BANNER_STATUS',
        'MODULE_CHECKOUT_PAYMENT_BANNER_BANNER_GROUP',
        'MODULE_CHECKOUT_PAYMENT_BANNER_CONTENT_WIDTH',
        'MODULE_CHECKOUT_PAYMENT_BANNER_SORT_ORDER'
      );
    }
  }
?>