<?php
/**
 * 2007-2022 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2022 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' ._DB_PREFIX_.'automarketing` (
            `id`             INT(11) NOT NULL AUTO_INCREMENT,
            `rule_active`                 TINYINT(2),
            `title`                       TEXT,
            `priority`                    int,
            `groupBox`                    TEXT,
            `category`                    TEXT,
            `daysafterorder`           TEXT,
            `productshownmail`            TEXT,
            `product_type`            TEXT,
            `discount_code`      TEXT,
            `email_subject`      TEXT,
            `date_add`                    DATE,
            `date_update`                 DATE,
            PRIMARY KEY                     (`id`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' ._DB_PREFIX_.'automarketing_lang` (
            `id`            INT(11) NOT NULL,
            `id_lang`                       INT(11) NOT NULL,
            `email_text`                       TEXT,
             `email_text_after`                       TEXT,
            PRIMARY KEY                     (`id`, `id_lang`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' ._DB_PREFIX_.'automarketingdetail` (
            `id_detail`             INT(11) NOT NULL AUTO_INCREMENT,
            `id_order`             TEXT,
            `customer_id`                 TEXT,
            `customer_email`                       TEXT,
            `order_date`                    TEXT,
            `email_date`                    TEXT,
            `rule_id`                    TEXT,
            PRIMARY KEY                     (`id_detail`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';


foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}