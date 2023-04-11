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

class PlgTZ_Portfolio_PlusContentPasswordModelArticle extends TZ_Portfolio_PlusPluginModelItem{

    protected $cache;

    public function getFormPassword(){

        $model  = JModelLegacy::getInstance('Password','PlgTZ_Portfolio_PlusContentPasswordModel',
            array('ignore_request' => true));

        if(!$model){
            return false;
        }

        $form   = $model -> getForm();

        return $form;
    }

    public function getPasswordItem(){

        $model  = JModelLegacy::getInstance('Password','PlgTZ_Portfolio_PlusContentPasswordModel',
            array('ignore_request' => true));

        if(!$model){
            return false;
        }

        $model -> set('article', $this -> article);
        $model -> set('addon', $this -> addon);
        $model -> set('trigger_params', $this -> trigger_params);

        return $model -> getPasswordItem();

//        $storeId    = __METHOD__;
//        $storeId    = md5($storeId);
//
//        if(isset($this -> cache[$storeId])){
//            return $this -> cache[$storeId];
//        }
//
//        if(!$this -> article){
//            return false;
//        }
//
//        $adoParams  = $this -> addon -> params;
//        if(is_string($adoParams)){
//            $adoParams  = new Registry($adoParams);
//        }
//
//        if(!$this -> article -> id || ($this -> article -> id && !TZ_Portfolio_Plus_Addon_PasswordHelper::hasPassword($this -> article -> id))){
//            return false;
//        }
//        $trigParams = $this -> trigger_params;
//
//        $item   = new stdClass();
//        $item -> password   = TZ_Portfolio_Plus_Addon_PasswordHelper::getPassword($this -> article -> id);
//        $item -> message_protection = $adoParams -> get('password_message_protect', 'This is private content, protected by Password');
//        if($msProtection = $trigParams -> get('ado_ct_password_msg_protection')){
//            $item -> message_protection = $msProtection;
//        }
//
//        $this -> cache[$storeId]    = $item;
//
//        return $item;
    }
}