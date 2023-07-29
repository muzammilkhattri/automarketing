<?php
/**
 * 2021 Addify
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
 *  @author    Addify
 *  @copyright 2021 Addify
 *  @license   http://opensource.org/licenses/afl-3.0.php
 */

class AutomarketingautomarketingmailModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::postProcess()
	 */
    public function initContent()
    {
        parent::initContent();
        $this->setTemplate('module:automarketing/views/templates/front/emailtemplate.tpl');  
    }

	public function postProcess()
	{
        if (Configuration::get('automarketing_LIVE_MODE') == 1) {
            $Token = Tools::getValue('token');
            if ($Token == "00123456789") {
                
                $getdataforemailS = AutomarketingdetailModel::getdataforemail();

                $today_date = date('Y-m-d');
           
                if ($getdataforemailS) {

                    foreach ($getdataforemailS as $getdataforemail) {

                        if ($getdataforemail['email_date'] == $today_date) {
                            
                            $order_id = $getdataforemail['id_order'];
                            $context=Context::getContext();
                            $order = new Order($order_id);
                            $id_customer = $order->id_customer;
                            $customer = new Customer($id_customer);
                            $shop = new Shop($order->id_shop);
                            $shop_name = $shop->name;
                            $shop_url = $shop->getBaseURL(true);
                            $shop_logo = _PS_BASE_URL_.__PS_BASE_URI__.'img/'.Configuration::get('PS_LOGO', null, $shop->id);
                            
                            $id_order_state = $order->current_state;
                            $id_lang = $order->id_lang;

                            $cartproducts = $order->getCartProducts();
                            $cart_pro_ids = [];
                            foreach ($cartproducts as $value_id) {
                                $cart_pro_ids[] = $value_id['id_product'];
                            }

                            $getruledata = AutomarketingModel::getRuleById($getdataforemail['rule_id']);
                            $getruledata_lang = AutomarketingModel::getTextDisplay($getdataforemail['rule_id'],$id_lang);
                            $final_product_result = [];
                            $product_results = [];
                             $id_currency = $order->id_currency;
                            $iso_code = AutomarketingModel::getIsoCodeById($id_currency);
                            $locale = AutomarketingModel::getContextLocale($context);
                            $final_products = [];

                            if ($getruledata[0]['product_type'] == 'Related products') {
                
                                foreach ($cart_pro_ids as $key => $cart_pro_id) {
                                    $product = new Product($cart_pro_id);
        
                                    if ($product->getAccessories($id_lang)) {
                                        $product_results = array_merge($product->getAccessories($id_lang),$product_results);
                                    }
                                }

                                $final_product_result = array();
                                foreach ($product_results as $key => $final_product){
                                  if(!in_array($final_product, $final_product_result))
                                    $final_product_result[$key]=$final_product;
                                }

                                
                                if (count($final_product_result) > $getruledata[0]['productshownmail'] ) {
                                    for ($i=0; $i < $getruledata[0]['productshownmail']; $i++) { 
                                        $final_products[] = $final_product_result[$i];
                                    }
                                }else{
                                    $final_products = $final_product_result;
                                }

                                foreach ($final_products as $key => $result) {

                                    $product_attribute_id = $result['id_product_attribute'];
                                    $id_product = $result['id_product'];
                                   
                                    $image_data = Product::getCover($id_product);

                                    $link_rewrite = $result['link_rewrite'];
                                    $current_url_protocol = Tools::getCurrentUrlProtocolPrefix();
                                    $link = new Link();

                                    $img_url = $current_url_protocol.$link->getImageLink($link_rewrite, $image_data['id_image']);
                                    $product_price = $locale->formatPrice($result['price'],$iso_code);
                                    $without_reduction_price = $locale->formatPrice($result['price_without_reduction'],$iso_code);

                                    $final_products[$key]['img_url'] = $img_url;
                                    $final_products[$key]['product_price'] = $product_price;
                                    $final_products[$key]['without_reduction_price'] = $without_reduction_price;
                                }
                            }
                            else if ($getruledata[0]['product_type'] == 'Products of same category') {
                                foreach ($cart_pro_ids as $key => $cart_pro_id) {
                                    $categorys = Product::getProductCategories($cart_pro_id);
                                    $category = end($categorys);
                                    $products = Product::getProducts($id_lang,0,0,'id_product','ASC',$category,true);
                                    $product_results = array_merge($products,$product_results);
                                }

                                $final_product_result = array();
                                foreach ($product_results as $key => $final_product){
                                  if(!in_array($final_product, $final_product_result))
                                    $final_product_result[$key]=$final_product;
                                }

                                if (count($final_product_result) > $getruledata[0]['productshownmail'] ) {
                                    for ($i=0; $i < $getruledata[0]['productshownmail']; $i++) { 
                                        $final_products[] = $final_product_result[$i];
                                    }
                                }else{
                                    $final_products = $final_product_result;
                                }
     
                                foreach ($final_products as $key => $result) {

                                    $id_product = $result['id_product'];
                                
                                    $image_data = Product::getCover($id_product);
                                    $link = new Link();
                                    
                                    $link_rewrite = $result['link_rewrite'];
                                    $current_url_protocol = Tools::getCurrentUrlProtocolPrefix();
                                    $img_url = $link->getImageLink($link_rewrite, $image_data['id_image']);
                                    $img_url = $current_url_protocol.$img_url;
                                    $product_price = $locale->formatPrice($result['price'],$iso_code);
                                    $without_reduction_price = '';
                                    $link = $link->getProductLink($id_product);

                                    $final_products[$key]['img_url'] = $img_url;
                                    $final_products[$key]['product_price'] = $product_price;
                                    $final_products[$key]['without_reduction_price'] = $without_reduction_price;
                                    $final_products[$key]['reduction'] = 0;
                                    $final_products[$key]['link'] = $link;
                                }
                            }
                            else if ($getruledata[0]['product_type'] == 'Products of specific category') {
                                $rule_categorys = explode(',', $getruledata[0]['category']);
                                foreach ($rule_categorys as $key => $rule_category) {
                                    $products = Product::getProducts($id_lang,0,0,'id_product','ASC',$rule_category,true);
                                    $product_results = array_merge($products,$product_results);
                                }

                                $final_product_result = array();
                                foreach ($product_results as $key => $final_product){
                                  if(!in_array($final_product, $final_product_result))
                                    $final_product_result[$key]=$final_product;
                                }

                                if (count($final_product_result) > $getruledata[0]['productshownmail'] ) {
                                    for ($i=0; $i < $getruledata[0]['productshownmail']; $i++) { 
                                        $final_products[] = $final_product_result[$i];
                                    }
                                }else{
                                    $final_products = $final_product_result;
                                }

                                foreach ($final_products as $key => $result) {

                                    $id_product = $result['id_product'];
                                
                                    $image_data = Product::getCover($id_product);
                                    
                                    $link_rewrite = $result['link_rewrite'];
                                    $current_url_protocol = Tools::getCurrentUrlProtocolPrefix();
                                    $link = new Link();

                                    $img_url = $current_url_protocol.$link->getImageLink($link_rewrite, $image_data['id_image']);
                                    $product_price = $locale->formatPrice($result['price'],$iso_code);
                                    $without_reduction_price = '';
                                    $link = $link->getProductLink($id_product);

                                    $final_products[$key]['img_url'] = $img_url;
                                    $final_products[$key]['product_price'] = $product_price;
                                    $final_products[$key]['without_reduction_price'] = $without_reduction_price;
                                    $final_products[$key]['reduction'] = 0;
                                    $final_products[$key]['link'] = $link;
                                }
                            }
                            else if ($getruledata[0]['product_type'] == 'Bestseller products') {
                                $all_product = ProductSale::getBestSales($id_lang);

                                $final_product_result = array();
                                foreach ($all_product as $key => $final_product){
                                  if(!in_array($final_product, $final_product_result))
                                    $final_product_result[$key]=$final_product;
                                }

                                if (count($final_product_result) > $getruledata[0]['productshownmail'] ) {
                                    for ($i=0; $i < $getruledata[0]['productshownmail']; $i++) { 
                                        $final_products[] = $final_product_result[$i];
                                    }
                                }else{
                                    $final_products = $final_product_result;
                                }

                                foreach ($final_products as $key => $result) {

                                    $product_attribute_id = $result['id_product_attribute'];
                                    $id_product = $result['id_product'];
                                   
                                    $image_data = Product::getCover($id_product);

                                    $link_rewrite = $result['link_rewrite'];
                                    $current_url_protocol = Tools::getCurrentUrlProtocolPrefix();
                                    $link = new Link();

                                    $img_url = $current_url_protocol.$link->getImageLink($link_rewrite, $image_data['id_image']);
                                    $product_price = $locale->formatPrice($result['price'],$iso_code);
                                    $without_reduction_price = $locale->formatPrice($result['price_without_reduction'],$iso_code);

                                    $final_products[$key]['img_url'] = $img_url;
                                    $final_products[$key]['product_price'] = $product_price;
                                    $final_products[$key]['without_reduction_price'] = $without_reduction_price;
                                }
                            }

                            if ($final_products) {

                                $this->context->smarty->assign(array(
                                    'rules_data' => $getruledata,
                                    'getruledata_lang' => $getruledata_lang,
                                    'products_result' => $final_products,
                                    'shop_name' => $shop_name,
                                    'shop_url' => $shop_url,
                                    'shop_logo' => $shop_logo,
                                ));

                                $products = $this->context->smarty->fetch(_PS_MODULE_DIR_.'automarketing/views/templates/front/productmail.tpl');

                                // Send an e-mail to customer (one order = one email)
                                if ($id_order_state != Configuration::get('PS_OS_ERROR') && $id_order_state != Configuration::get('PS_OS_CANCELED') && $id_customer) {
                                
                                    $data = [
                                            '{firstname}' => $customer->firstname,
                                            '{lastname}' => $customer->lastname,
                                            '{email}' => $customer->email,
                                            '{products}' => $products,
                                            '{shop_name}' => $shop_name,
                                            
                                        ];


                                    if (Validate::isEmail($customer->email)) {
                                        Mail::Send(
                                            (int) $order->id_lang,
                                            'automarketingemail',
                                            $getruledata[0]['email_subject'],
                                            $data,
                                            $customer->email,
                                            $customer->firstname . ' ' . $customer->lastname,
                                            null,
                                            null,
                                            null,
                                            null,
                                           _PS_MODULE_DIR_.'automarketing/mails/',
                                            false,
                                            (int) $order->id_shop
                                        );
                                        
                                    }
                                }
                            }
                        } 
                    }
                }
        	}
        }
    }
}
