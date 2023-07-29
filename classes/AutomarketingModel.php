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

class AutomarketingModel extends ObjectModel
{
	public $id;
    public $rule_active;
    public $title;
    public $priority;
    public $groupBox;
    public $category;
    public $daysafterorder;
    public $productshownmail;
    public $product_type;
    public $email_text;
    public $email_subject;
    public $email_text_after;
    public $discount_code;
    public $date_add;
    public $date_update;
    
	public static $definition = array(
        'table'     => 'automarketing',
        'primary'   => 'id',
        'multilang' => true,
        'fields'    => array(
            'title'            => array('type' => self::TYPE_HTML),
            'rule_active'      => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'priority'         => array('type' => self::TYPE_INT),
            'groupBox'         => array('type' => self::TYPE_HTML),
            'category'         => array('type' => self::TYPE_HTML),
            'daysafterorder'         => array('type' => self::TYPE_HTML),
            'productshownmail'          => array('type' => self::TYPE_HTML),
            'product_type'          => array('type' => self::TYPE_HTML),
            'discount_code'          => array('type' => self::TYPE_HTML),
            'email_subject'          => array('type' => self::TYPE_HTML),
            'date_add'         => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_update'      => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),

             // multi-lingual
           'email_text'   => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'),
           'email_text_after'   => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'),
    ));

    public static function getPosts()
    {
        return Db::getInstance()->executeS('
            SELECT cr.*, crc.`email_text`
            FROM `'._DB_PREFIX_.'automarketing` cr
            '.Shop::addSqlAssociation('automarketing', 'cr').'
            LEFT JOIN `'._DB_PREFIX_.'automarketing_lang` crc
                ON (cr.`id` = crc.`id`)
            GROUP BY cr.`id`');
    }

    public static function getRuleById($rule_id)
    {
        return Db::getInstance()->executeS('
            SELECT *
            FROM `'._DB_PREFIX_.'automarketing` WHERE id = ' . $rule_id);
    }

    public static function getTextDisplay($id, $lang)
    {
        return Db::getInstance()->executeS('SELECT `email_text`
            FROM `'._DB_PREFIX_.'automarketing_lang` WHERE `id` = '.(int)$id.' AND `id_lang` = '.(int)$lang);
    }

    public static function getRules($group_id)
    {
        $results = Db::getInstance()->executeS('
            SELECT *
            FROM `'._DB_PREFIX_.'automarketing` WHERE rule_active = 1 ORDER BY priority ASC');

        if ($results) {
            $finl_rules = [];
            foreach ($results as $result) {
                if(!empty($result['groupBox'])){
                    $groups_ids = explode(",",$result['groupBox']);
                    if (in_array($group_id, $groups_ids)) {

                             $finl_rules[] =  $result;
                    }
                }
            }
            return $finl_rules;
        }
    }

    public static function setStatus($status, $id)
    {
        if (!$id || empty($status)) {
            return false;
        }
        return (bool)Db::getInstance()->execute('UPDATE '._DB_PREFIX_.self::$definition['table'].'
            SET `'.pSQL($status).'` = !'.pSQL($status).'
            WHERE id = '.(int)$id);
    }

    public static function getIsoCodeById(int $id, bool $forceRefreshCache = false)
    {
        $cacheId = 'Currency::getIsoCodeById' . pSQL($id);
        if ($forceRefreshCache || !Cache::isStored($cacheId)) {
            $resultIsoCode = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `iso_code` FROM ' . _DB_PREFIX_ . 'currency WHERE `id_currency` = ' . (int) $id);
            Cache::store($cacheId, $resultIsoCode);

            return $resultIsoCode;
        }

        return Cache::retrieve($cacheId);
    }

    public static function getContextLocale(Context $context)
    {
        $locale = $context->getCurrentLocale();
        if (null !== $locale) {
            return $locale;
        }

        $containerFinder = new ContainerFinder($context);
        $container = $containerFinder->getContainer();
        if (null === $context->container) {
            $context->container = $container;
        }

        /** @var LocaleRepository $localeRepository */
        $localeRepository = $container->get(self::SERVICE_LOCALE_REPOSITORY);
        $locale = $localeRepository->getLocale(
            $context->language->getLocale()
        );

        return $locale;
    }
}