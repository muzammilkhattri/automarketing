<?php
/**
* 2007-2022 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2022 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AutomarketingdetailModel extends ObjectModel
{
	public $id_detail;
    public $id_order;
    public $customer_id;
    public $customer_email;
    public $order_date;
    public $email_date;
    public $rule_id;
    
	public static $definition = array(
        'table'     => 'automarketingdetail',
        'primary'   => 'id_detail',
        'multilang' => false,
        'fields'    => array(
            'id_order'            => array('type' => self::TYPE_HTML),
            'customer_id'            => array('type' => self::TYPE_HTML),
            'customer_email'      => array('type' => self::TYPE_HTML),
            'order_date'         => array('type' => self::TYPE_HTML),
            'email_date'         => array('type' => self::TYPE_HTML),
            'rule_id'         => array('type' => self::TYPE_HTML),
    ));

	public static function getdataforemail()
	{
		return Db::getInstance()->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.'automarketingdetail`');
	}

    public static function getDataCheck($id_order,$id_customer, $rule_id)
    {
        return Db::getInstance()->executeS('SELECT *
            FROM ' . _DB_PREFIX_ . 'automarketingdetail
            WHERE `id_order`=' . $id_order . ' AND
                `rule_id`=' . $rule_id . ' AND   
                `customer_id`=' . $id_customer);
    }

}