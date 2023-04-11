<?php
/*------------------------------------------------------------------------

# Password Content Add-On

# ------------------------------------------------------------------------

# Author:    DuongTVTemPlaza

# @License - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Website: http://www.tzportfolio.com

# Technical Support:  Forum - https://www.tzportfolio.com/help/forum.html

# Family website: http://www.templaza.com

# Family Support: Forum - https://www.templaza.com/Forums.html

# Copyright: Copyright (C) 2011-2019 TZ Portfolio (http://www.tzportfolio.com). All Rights Reserved.

-------------------------------------------------------------------------*/

// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

JLoader::import('com_tz_portfolio_plus.addons.content.password.includes.defines',
    JPATH_SITE.DIRECTORY_SEPARATOR.'components');
// Import addon_data model
JLoader::import('com_tz_portfolio_plus.models.addon_data',JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components');

class PlgTZ_Portfolio_PlusContentPasswordModelPassword extends TZ_Portfolio_PlusModelAddon_Data{

    protected $cache;
    protected $addon            = null;
    protected $article          = null;

    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        JForm::addFormPath(COM_TZ_PORTFOLIO_PLUS_ADDON_PATH.DIRECTORY_SEPARATOR
            .'content/password/models/forms');
        $form = $this->loadForm('com_tz_portfolio_plus.content.'.$this -> getName().'.password', $this -> getName(),
            array('control' => 'jform', 'load_data' => true));

        if (empty($form))
        {
            return false;
        }

        return $form;
    }

    public function getPasswordItem(){
        if(!$this -> article){
            return false;
        }

        $storeId    = __METHOD__;
        $storeId   .= serialize($this -> article -> id);
        $storeId    = md5($storeId);

        if(isset($this -> cache[$storeId])){
            return $this -> cache[$storeId];
        }


        $adoParams  = $this -> addon -> params;
        if(is_string($adoParams)){
            $adoParams  = new Registry($adoParams);
        }

        if(!$this -> article -> id || ($this -> article -> id && !TZ_Portfolio_Plus_Addon_PasswordHelper::hasPassword($this -> article -> id))){
            return false;
        }
        $trigParams = $this -> trigger_params;

        $item   = new stdClass();
        $item -> password   = TZ_Portfolio_Plus_Addon_PasswordHelper::getPassword($this -> article -> id);
        $item -> message_protection = $adoParams -> get('password_message_protect', 'This is private content, protected by Password');
        if($msProtection = $trigParams -> get('ado_ct_password_msg_protection')){
            $item -> message_protection = $msProtection;
        }

        $this -> cache[$storeId]    = $item;

        return $item;
    }

    public function login($data){

        $input      = JFactory::getApplication() -> input;
        $table      = $this->getTable();
        $key        = $table->getKeyName();
        $pk         = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
//        $articleId  = $input -> get('id');

        $loadData   = array(
            'element'       => TP_ADDON_CONTENT_PASSWORD_KEY_CONTENT_PASSWORD,
            'content_id'    => $pk,
            'value'         => $data['password']
        );

        if(!$table -> load($loadData)) {
            $this -> setError(JText::_('TP_ADDON_CONTENT_PASSWORD_INVALID_PASSWORD_PROVIDED'));
            return false;
        }

        $sessionData    = new stdClass();

        $sessionData -> id          = $table -> get('id');
        $sessionData -> element     = $table -> get('element');
//        $sessionData -> password    = $table -> get('value');
        $sessionData -> content_id  = (int) $table -> get('content_id');

        $session = JFactory::getSession();
        $session->set('com_tz_portfolio_plus.addon.content.password.article.' . $pk, $sessionData);

        return true;
    }
}