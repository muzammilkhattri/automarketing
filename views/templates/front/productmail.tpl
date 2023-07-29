{*
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
*}

<div>
    <div style="width: 970px; padding-right: 15px; padding-left: 15px; margin-right: auto; margin-left: auto;">
    	<div style="padding: 18px; text-align: center;">
	        <a style="color: #337ab7; text-decoration: none; background-color: transparent;" href="{$shop_url|escape:'htmlall':'UTF-8'}" target="_blank">
	          <img style="vertical-align: middle; border: 0;" height="auto" src="{$shop_logo|escape:'htmlall':'UTF-8'}">
	        </a>
	    </div>
	    <br>
	    {if isset($getruledata_lang[0]['email_text']) AND $getruledata_lang[0]['email_text']}
	    <div>{$getruledata_lang[0]['email_text'] nofilter}</div>
	    {/if}
	    {if isset($rules_data[0]['discount_code']) AND $rules_data[0]['discount_code']}
        <div style=" text-align: center;">
            <h2 style="margin-bottom: 20px; font-size: 30px; margin-top: 20px; font-family: inherit; font-weight: 500;
		    line-height: 1.1; color: inherit;">{l s='Discount Code : ' mod='automarketing'}{$rules_data[0]['discount_code']|escape:'htmlall':'UTF-8'}</h2>
        </div>
        {/if}
		<div style="display: inline-block;  text-align: center;">
			{foreach $products_result as $product_result}
		    <div style="width: 16.66666667%; display: inline-block; vertical-align: top; margin: 25px 5px;">
		        <div style="font-family:'Jost', sans-serif; text-align: center;">
		            <div style="border-radius: 10px; overflow: hidden; position: relative;">
		                <a style="color: #337ab7; text-decoration: none; background-color: transparent;" href="{$product_result['link']|escape:'htmlall':'UTF-8'}" >
		                    <img style="vertical-align: middle; border: 0; display: block; width: 100%; height: auto; transition: all .3s ease 0s;" src="{$product_result['img_url']|escape:'htmlall':'UTF-8'}">
		                </a>
		            </div>
		            <div style="padding: 15px 15px 0;">
		                <h3 style="font-size: 14px; font-weight: 500; text-transform: capitalize; margin: 0 0 7px; font-family: inherit; line-height: 24px; color: inherit;">
		                	<a style="text-decoration: none; color: #222; transition: all 0.3s ease 0s; background-color: transparent;" href="{$product_result['link']|escape:'htmlall':'UTF-8'}">{$product_result['name']|escape:'htmlall':'UTF-8'}</a>
		                </h3>
		                <div style="color: #000; font-size: 17px; font-weight: 700;">{$product_result['product_price']|escape:'htmlall':'UTF-8'}
		                	{if $product_result['reduction'] neq 0}
		                	<span style="color: #666; font-size: 16px; font-weight: 400; text-decoration: line-through;">{$product_result['without_reduction_price']|escape:'htmlall':'UTF-8'}</span>
		                	{/if}
		                </div>

		            </div>
		        </div>
		    </div>
		    {/foreach}
		</div>
		{if isset($getruledata_lang[0]['email_text_after']) AND $getruledata_lang[0]['email_text_after']}
	    <div>{$getruledata_lang[0]['email_text_after'] nofilter}</div>
	    {/if}
        <div style="font-family:Open sans, arial, sans-serif; font-size:14px; line-height:25px; text-align:center; color:#363A41;" align="center">
        	<a href="{$shop_url|escape:'htmlall':'UTF-8'}" style="text-decoration: underline; color: #656565; font-size: 16px; font-weight: 600;">{$shop_name|escape:'htmlall':'UTF-8'}</a>
        </div>
        <div style="font-family:Open sans, arial, sans-serif; font-size:12px; line-height:25px; text-align:center; color:#656565;" align="center">{l s='Powered by' mod='automarketing'} <a href="https://www.prestashop.com/?utm_source=marchandprestashop&amp;utm_medium=e-mail&amp;utm_campaign=footer_1-7" >{l s='PrestaShop' mod='automarketing'}</a>
		</div>
	</div>
</div>


