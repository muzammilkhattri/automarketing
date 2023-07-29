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

if (!defined('_PS_VERSION_')) {
    exit;
}
require_once(_PS_MODULE_DIR_."automarketing/classes/AutomarketingModel.php");
require_once(_PS_MODULE_DIR_."automarketing/classes/AutomarketingdetailModel.php");
class Automarketing extends Module
{
    public function __construct()
    {
        $this->name = 'automarketing';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Muzammil';
        $this->is_eu_compatible = 1;
        $this->need_instance = 0;
        $this->module_key = '881a1d550fbf7fdded309afaae1cb7c0';
        

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Auto Marketing');
        $this->description = $this->l('This module allows to auto marketing after order.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall my module?');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
            $this->registerHook('displayOrderConfirmation') &&
            $this->registerHook('backOfficeHeader') && 
            $this->registerHook('header');
    }

    public function uninstall()
    {  
        include(dirname(__FILE__).'/sql/uninstall.php');
        Configuration::deleteByName('automarketing_LIVE_MODE');

        return parent::uninstall() && 
            $this->unregisterHook('displayOrderConfirmation') &&
            $this->unregisterHook('backOfficeHeader') && 
            $this->unregisterHook('header');
            
    }

    /**
     * Load the form
     */
    public function getContent()
    {
        if (!$this->active) {
            $this->context->controller->errors[] = $this->l('Access Denied...Module is disabled.');
        }
        else
        {
            $frontcroncontrollerlink = Context::getContext()->link->getModuleLink($this->name, 'automarketingmail',array('token' => "00123456789"));
            $Cron_URL ="Cron Url :".$frontcroncontrollerlink;
            $this->context->smarty->assign(array(
                'Cron_URL' => $Cron_URL,
            ));

            if (((bool)Tools::isSubmit('add'.$this->name)) == true) 
            {
                return $this->renderForm();
            }

            if (((bool)Tools::isSubmit('update'.$this->name)) == true) 
            {
                return $this->renderForm();
            }

            $this->postProcess();

            return $this->display(__FILE__, 'views/templates/admin/cron.tpl'). $this->getConfigForm(). $this->renderList();
            
        }
    }

    protected function postProcess()
    {
        if(Tools::isSubmit('Submitconfig'.$this->name))
        {
          $this->processConfigSave();
        }
       
        if(Tools::isSubmit('Submitpost'.$this->name))
        {
          $this->processSave();
        }

        if (Tools::isSubmit('status'.$this->name)) {
            if (!AutomarketingModel::setStatus('rule_active', (int)Tools::getValue('id'))) {
                $this->context->controller->errors[] = $this->l('Status update failed.');
            } else {
              
                $this->context->controller->confirmations[] = $this->l('Status updated successfully.');
            }
        }
       
        if (Tools::isSubmit('delete'.$this->name)) {
            if (!Validate::isLoadedObject($id = new AutomarketingModel((int)Tools::getValue('id')))) {
                
            } else {

                if (!$id->delete()) {
                    $this->context->controller->errors[] = $this->l('Rule cannot be deleted.');
                } 
                else 
                {
                    $this->context->controller->confirmations[] = $this->l('Rule deleted successfully.');
                }
            }
        }

        if (Tools::isSubmit('submitBulkdelete'.$this->name)) {
        
            $postBox = Tools::getValue('automarketingBox');
            if (isset($postBox) && $postBox) {
                $deleted = 0;
                foreach ($postBox as $posts) {
                    if (!Validate::isLoadedObject($AutomarketingModel = new AutomarketingModel((int)$posts))) {
                        
                        break;
                    } 
                    else {

                        if ($AutomarketingModel->delete()) {
                            $deleted++;
                        }
                    }
                }
                if (count($this->context->controller->errors)) {
                    $this->context->controller->errors;
                } else {
                    $this->context->controller->confirmations[] = sprintf($this->l('Rules deleted successfully.'), $deleted);
                }
            }
        }
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */

    protected function renderList()
    {
        $fields_list = array(
            'id' => array(
                'title' => $this->l('ID'),
                'width' => 120,
                'type' => 'int',
                'search' => false,
                'orderby' => false
            ),
            'priority' => array(
                'title' => $this->l('Priority'),
                'width' => 120,
                'type' => 'int',
                'search' => false,
                'orderby' => false
            ),
            'title' => array(
                'title' => $this->l('Title'),
                'width' => 140,
                'type' => 'text',
                'search' => false,
                'orderby' => false
            ),
            'rule_active' => array(
                'title' => $this->l('Status'),
                'width' => 140,
                'active' => 'status',
                'type' => 'bool',
                'search' => false,
                'orderby' => false,
            ),    
            'daysafterorder' => array(
                'title' => $this->l('After Order Days'),
                'width' => 140,
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ),
            'product_type' => array(
                'title' => $this->l('Product Type'),
                'width' => 140,
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ),
            'date_add' => array(
                'title' => $this->l('Added Date'),
                'width' => 140,
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ),
            'date_update' => array(
                'title' => $this->l('Updated Date'),
                'width' => 140,
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ), 
        );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->identifier = 'id';
        $helper->actions = array('edit', 'delete');
        $helper->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?')
            )
        );

        $helper->listTotal = count(AutomarketingModel::getPosts());
        $helper->show_toolbar = true;
        $helper->toolbar_btn['new'] =  array(
            'href' => AdminController::$currentIndex.'&configure='.$this->name.'&add'.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Add new rule')
        );

        $helper->title = "Rules";
        $helper->table = "automarketing";
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        return $helper->generateList(AutomarketingModel::getPosts(), $fields_list);
    }

    /**
     * Create the structure of your form.
     */
    protected function renderForm()
    {   
        if (Tools::getValue('id')) {
            $get_categories = new AutomarketingModel((int)Tools::getValue('id'));
            $selected_categories = explode(',', $get_categories->category);
        } else {
            $selected_categories = array();
        }

        $productshown = array(
            array('id' => '1', 'name' => $this->l('1')),
            array('id' => '2', 'name' => $this->l('2')),
            array('id' => '3', 'name' => $this->l('3')),
            array('id' => '4', 'name' => $this->l('4')),
            array('id' => '5', 'name' => $this->l('5')),
            array('id' => '6', 'name' => $this->l('6')),
            array('id' => '7', 'name' => $this->l('7')),
            array('id' => '8', 'name' => $this->l('8')),
            array('id' => '9', 'name' => $this->l('9')),
            array('id' => '10', 'name' => $this->l('10')),
        );

        $Days = array(
            array('id' => '0', 'name' => $this->l('0')),
           array('id' => '1', 'name' => $this->l('1')),
            array('id' => '2', 'name' => $this->l('2')),
            array('id' => '3', 'name' => $this->l('3')),
            array('id' => '4', 'name' => $this->l('4')),
            array('id' => '5', 'name' => $this->l('5')),
            array('id' => '6', 'name' => $this->l('6')),
            array('id' => '7', 'name' => $this->l('7')),
            array('id' => '8', 'name' => $this->l('8')),
            array('id' => '9', 'name' => $this->l('9')),
            array('id' => '10', 'name' => $this->l('10')),
        );

        $product_type = array(
            array('id' => 'Related products', 'name' => $this->l('Related products of the last ordered products')),
            array('id' => 'Products of same category', 'name' => $this->l('Products of same category of the last ordered products')),
            array('id' => 'Products of specific category', 'name' => $this->l('Products of specific category')),
            array('id' => 'Bestseller products', 'name' => $this->l('Bestseller products')),
        );

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Add Rule'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'id'
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Rule Enable'),
                        'name' => 'rule_active',
                        'is_bool' => true,
                        'desc' => $this->l('Enable/Disable this rule.'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type'          => 'text',
                        'label'         => $this->l('Title'),
                        'name'          => 'title',
                        'desc' => $this->l('Enter title for rule'),
                    ),
                    array(
                        'type'          => 'text',
                        'label'         => $this->l('Priority'),
                        'name'          => 'priority',
                        'validate' => 'isInt',
                        'required' => true,
                        'desc' => $this->l('Value must be numeric.'),
                    ),
                    array(
                        'type'  => 'group',
                        'label' => $this->l('Groups'),
                        'name'  => 'groupBox[]',
                        'values' => Group::getGroups(Context::getContext()->language->id),
                        'info_introduction' => $this->l('You now have three default customer groups.'),
                        'desc' => $this->l('Select customer group(s) which you want for rule.'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Choose Days After Order'),
                        'name' => 'daysafterorder',
                        'desc' => $this->l('Choose how many days after order to email.'),
                        'options' => array(
                            'query' =>  $Days,
                            'id' => 'id',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Products shown in mail'),
                        'name' => 'productshownmail',
                        'desc' => $this->l('Choose how many products should be shown in the email.'),
                        'options' => array(
                            'query' =>  $productshown,
                            'id' => 'id',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Product Type'),
                        'name' => 'product_type',
                        'desc'   => $this->l('Choose type which you want for rule.'),
                        'options' => array(
                            'query' =>  $product_type,
                            'id' => 'id',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type'  => 'categories',
                        'label' => $this->l('Categories'),
                        'name'  => 'category',
                        'col' => 6,
                        'desc' => $this->l('Select categories which you want for rule.'),
                        'tree'  => array(
                            'id' => 'type_category',
                            'use_checkbox' => true,
                            'disabled_categories' => null,
                            'selected_categories' => $selected_categories,
                            'root_category' => Context::getContext()->shop->getCategory()
                        )
                    ),
                    array(
                        'type'          => 'text',
                        'label'         => $this->l('Enter Email Subject'),
                        'name'          => 'email_subject',
                        'desc'   => $this->l('Enter email subject'),
                    ),
                    array(
                        'type'          => 'textarea',
                        'label'         => $this->l('Email Text before the product'),
                        'name'          => 'email_text',
                        'desc'   => $this->l('Enter Text'),
                        'lang' => true,
                        'col' => 8,
                        'rows' => 10,
                        'class' => 'rte',
                        'autoload_rte' => true,
                    ),
                    array(
                        'type'          => 'textarea',
                        'label'         => $this->l('Email Text after the product'),
                        'name'          => 'email_text_after',
                        'desc'   => $this->l('Enter Text'),
                        'lang' => true,
                        'col' => 8,
                        'rows' => 10,
                        'class' => 'rte',
                        'autoload_rte' => true,
                    ),
                    array(
                        'type'          => 'text',
                        'label'         => $this->l('Enter Discount Code'),
                        'name'          => 'discount_code',
                        'desc'   => $this->l('Enter Discount Code'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
                'buttons' => array(
                    'cancel' => array(
                        'title' => $this->l('Cancel'),
                        'class' => 'btn btn-default',
                        'icon' => 'process-icon-cancel',
                        'href' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
                    ),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->show_cancel_button = false;
        $helper->table = 'automarketing';
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = 'id';
        $helper->submit_action = 'Submitpost'.$this->name;
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->back_url = $helper->currentIndex.'&token='.$helper->token;
        
        $helper->tpl_vars = array(
            'fields_value' => $this->getFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'context_link' => Context::getContext()->link,
        );

        return $helper->generateForm(array($fields_form));
    }

    protected function getFormValues()
    {
        $fields_value = array();
        $fields_value = array(
            'email_text' => Tools::getValue('email_text'),
            'email_text_after' => Tools::getValue('email_text_after'),
        );

        if (Tools::getValue('id') && (Tools::isSubmit('Submitpost'.$this->name) === false)) {
            $postBlock = new AutomarketingModel((int)Tools::getValue('id'));
            $fields_value['id'] = $postBlock->id;
            $fields_value['rule_active'] = $postBlock->rule_active;
            $fields_value['title'] = $postBlock->title;
            $fields_value['priority'] = $postBlock->priority;
            $fields_value['groupBox'] = explode(",",$postBlock->groupBox);
            $fields_value['category'] = explode(',', $postBlock->category);
            $fields_value['productshownmail'] =  $postBlock->productshownmail;
            $fields_value['daysafterorder'] =  $postBlock->daysafterorder;
            $fields_value['product_type'] =  $postBlock->product_type;
            $fields_value['email_text'] = $postBlock->email_text;
            $fields_value['email_text_after'] = $postBlock->email_text_after;
            $fields_value['email_subject'] = $postBlock->email_subject;
            $fields_value['discount_code'] =  $postBlock->discount_code;
            $accessed_groups_ids= $fields_value['groupBox'];
        }
        else
        {
            $fields_value['id'] = Tools::getValue('id');
            $fields_value['rule_active']   = Tools::getValue('rule_active');
            $fields_value['title'] = Tools::getValue('title');
            $fields_value['priority']   = Tools::getValue('priority');
            $fields_value['productshownmail']= Tools::getValue('productshownmail');
            $fields_value['daysafterorder'] =  Tools::getValue('daysafterorder');
            $fields_value['product_type'] =  Tools::getValue('product_type');
            $fields_value['discount_code'] =  Tools::getValue('discount_code');
            $fields_value['email_subject'] =  Tools::getValue('email_subject');

            /* getting multilingual fields*/
            $languages = Language::getLanguages(false);
            foreach ($languages as $lang) {
                $email_text = Tools::getValue('email_text_'.$lang['id_lang']);

                // getting message
                if (!empty($email_text) && !Validate::isCleanHtml($email_text)) {
                    $this->context->controller->errors[] = sprintf(
                        $this->l('Invalid text content in %s.'),
                        Language::getIsoById($lang['id_lang'])
                    );
                } else {
                    $fields_value['email_text'][(int)$lang['id_lang']] = $email_text;
                }
            }

            /* getting multilingual fields*/
            $languages = Language::getLanguages(false);
            foreach ($languages as $lang) {
                $email_text_after = Tools::getValue('email_text_after_'.$lang['id_lang']);

                // getting message
                if (!empty($email_text_after) && !Validate::isCleanHtml($email_text_after)) {
                    $this->context->controller->errors[] = sprintf(
                        $this->l('Invalid text content in %s.'),
                        Language::getIsoById($lang['id_lang'])
                    );
                } else {
                    $fields_value['email_text_after'][(int)$lang['id_lang']] = $email_text_after;
                }
            }
            
            if (!Tools::getValue('groupBox')) 
            {
                $fields_value['groupBox']  = Tools::getValue('groupBox');
            }
            else
            {
                $fields_value['groupBox']  = implode(",",Tools::getValue('groupBox'));
            }

            if (!Tools::getValue('category')) {
                $fields_value['category'] = Tools::getValue('category');
            } else {
                $fields_value['category'] = implode(',', Tools::getValue('category'));
            }
            
            $accessed_groups_ids = Tools::getValue('groupBox');
        }
         if (!is_array($accessed_groups_ids)) {
            $accessed_groups_ids = array();
        }
        // Added values of object Group
        $groups = Group::getGroups($this->context->language->id);
        if (empty($accessed_groups_ids)) {
            $preselected = array(Configuration::get('PS_UNIDENTIFIED_GROUP'), Configuration::get('PS_GUEST_GROUP'), Configuration::get('PS_CUSTOMER_GROUP'));
            $accessed_groups_ids = array_merge($accessed_groups_ids, $preselected);
        }
        foreach ($groups as $group) {
            $fields_value['groupBox_'.$group['id_group']] = (isset($accessed_groups_ids) && $accessed_groups_ids)? Tools::getValue('groupBox_'.$group['id_group'], (in_array($group['id_group'], $accessed_groups_ids))) : true;
        }
        return $fields_value;
    }

    protected function processSave()
    {
        $call_back = 'add';
        $form_values = $this->getFormValues();

        if (!isset($form_values) && !$form_values) {
            return $this->context->controller->errors[] = $this->l('Empty post values.');
        }
        if ($form_values['priority'] < 0) {
            $this->context->controller->errors[] = $this->l('Priority value is not valid.');
        }
        if (($form_values['priority']) == 0) {
            $this->context->controller->errors[] = $this->l('Priority value is not valid.');
        }
        if (!is_numeric($form_values['priority'])) {
            $this->context->controller->errors[] = $this->l('Priority value is not valid.');
        }
        
        if (count($this->context->controller->errors)) {
            return $this->context->controller->errors;
        }
        $dt = date("Y-m-d h:i:s");
        if (($id = Tools::getValue('id'))) {
           
            $call_back = 'update';
    
            $AutomarketingModel= new AutomarketingModel((int)$id);
            $AutomarketingModel->rule_active = $form_values['rule_active'];
            $AutomarketingModel->title = $form_values['title'];
            $AutomarketingModel->priority = $form_values['priority'];
            $AutomarketingModel->groupBox     = $form_values['groupBox'];
            $AutomarketingModel->category = $form_values['category'];
            $AutomarketingModel->productshownmail = $form_values['productshownmail'];
            $AutomarketingModel->daysafterorder = $form_values['daysafterorder'];
            $AutomarketingModel->product_type = $form_values['product_type'];
            $AutomarketingModel->email_text = $form_values['email_text'];
            $AutomarketingModel->email_subject = $form_values['email_subject'];
            $AutomarketingModel->email_text_after = $form_values['email_text_after'];
            $AutomarketingModel->discount_code = $form_values['discount_code'];
            $AutomarketingModel->date_update = $dt;
            
        } else {
            $AutomarketingModel = new AutomarketingModel();
            $AutomarketingModel->rule_active = $form_values['rule_active'];
            $AutomarketingModel->title = $form_values['title'];
            $AutomarketingModel->priority = $form_values['priority'];
            $AutomarketingModel->groupBox     = $form_values['groupBox'];
            $AutomarketingModel->category = $form_values['category'];
            $AutomarketingModel->productshownmail = $form_values['productshownmail'];
            $AutomarketingModel->daysafterorder = $form_values['daysafterorder'];
            $AutomarketingModel->product_type = $form_values['product_type'];
            $AutomarketingModel->email_text = $form_values['email_text'];
            $AutomarketingModel->email_subject = $form_values['email_subject'];
            $AutomarketingModel->email_text_after = $form_values['email_text_after'];
            $AutomarketingModel->discount_code = $form_values['discount_code'];
            $AutomarketingModel->date_add = $dt;
            
        }

        if (!call_user_func(array($AutomarketingModel, $call_back))) {
            $this->context->controller->errors = sprintf($this->l('Something went wrong while performing operation %s'), $call_back);
        } else {

            if($call_back=='add')
            {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&conf=3&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name);
            }
            else
            {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&conf=4&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name);
            }
            
        }
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('General Setting'),
                    'icon' => 'icon-cogs'
                ),
           
                'input' => array(

                        array(
                        'type' => 'switch',
                        'label' => $this->l('Module Active'),
                        'name' => 'automarketing_LIVE_MODE',
                        'desc' => $this->l('Activate/Deactivate this Module'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Activate')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Deactivate')
                            ),

                           ),
                        ),
                    ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            )
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $this->fields_form = array();

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'Submitconfig'.$this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),  /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
       return $helper->generateForm(array($fields_form));
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $fields_value = array();

        if((Tools::isSubmit('Submitconfig'.$this->name) === false))
        {
            $fields_value['automarketing_LIVE_MODE'] = Configuration::get('automarketing_LIVE_MODE');
        }
        else
        {
            $fields_value['automarketing_LIVE_MODE'] = Tools::getValue('automarketing_LIVE_MODE');
        }   

        return $fields_value;
    }

    /**
     * Save form data.
     */
    protected function processConfigSave()
    {
        $form_values = $this->getConfigFormValues();

        Configuration::updateValue('automarketing_LIVE_MODE', $form_values['automarketing_LIVE_MODE']);
        
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&conf=4&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name);
    }

    public function flushCache()
    {
        Tools::clearSmartyCache();
        Tools::clearXMLCache();
        Media::clearCache();
        Tools::generateIndex();
        if (true === Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=') &&
            is_callable(array('Tools', 'clearAllCache'))) {
            Tools::clearAllCache();
        }
    }

    public function hookBackOfficeHeader()
    {
        if (!$this->active) {
            return;
        }

        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/am_backoffice.js');
        }
    }

    public function hookDisplayOrderConfirmation($params)
    {
        $automarketing_LIVE_MODE = Configuration::get('automarketing_LIVE_MODE');
        if ($automarketing_LIVE_MODE == 1 ) {
            $context=Context::getContext();
            $id_group = $this->context->customer->id_default_group;
            $id_order = $params['order']->id;

            $order = new Order($id_order);
            $id_customer = $order->id_customer;

            $customer =  new Customer($order->id_customer);
            $customer_name = $customer->firstname.$customer->lastname;
            $customer_email = $customer->email;

            $date_add = $order->date_add;

            $getrules = AutomarketingModel::getRules($id_group);

            if ($getrules) {
                foreach ($getrules as $getrule) {
                    $am_number_days = $getrule['daysafterorder'];
                    $date = strtotime($date_add);
                    $date = strtotime("+".$am_number_days." Days", $date);
                    $email_date = date('Y-m-d', $date);
                

                    if (!(AutomarketingdetailModel::getDataCheck($id_order,$id_customer,$getrule['id']))) {
                         $AutomarketingdetailModel = new AutomarketingdetailModel();
                         $AutomarketingdetailModel->id_order = $params['order']->id;
                         $AutomarketingdetailModel->customer_id = $id_customer;
                         $AutomarketingdetailModel->rule_id = $getrule['id'];
                         $AutomarketingdetailModel->customer_email = $customer_email;
                         $AutomarketingdetailModel->order_date = $date_add;
                         $AutomarketingdetailModel->email_date = $email_date;
                         $AutomarketingdetailModel->save();
                    }
                }
            }
        }
    }
    
}
