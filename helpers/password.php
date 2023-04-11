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

// No direct access
defined('_JEXEC') or die;

// Import defines
JLoader::import('com_tz_portfolio_plus.addons.content.password.includes.defines',
    JPATH_SITE.DIRECTORY_SEPARATOR.'components');

class TZ_Portfolio_Plus_Addon_PasswordHelper{
    protected static $cache = array();

    /* Get password by article
     * @article is object or id number
     */
    public static function getItem($article){
        $articleId  = null;

        $storeId    = __METHOD__;
        if(is_object($article)) {
            $articleId  = $article -> id;
        }else{
            $articleId  = $article;
        }
        $storeId    .= ':'.$articleId;
        $storeId    = md5($storeId);

        if(isset(self::$cache[$storeId])){
            return self::$cache[$storeId];
        }

        if(!$articleId){
            return false;
        }

        $db     = JFactory::getDbo();
        $query  = null;
        $query  = self::_getQuery(TP_ADDON_CONTENT_PASSWORD_KEY_CONTENT_PASSWORD);

        $query -> where('ad.content_id = '.$articleId);

        $db -> setQuery($query);

        $item   = $db -> loadObject();
        if(!$item){
            return false;
        }

        self::$cache[$storeId] = $item;
        return $item;
    }

    /* Check the article has password */
    public static function getPassword($article){
        $item = self::getItem($article);
        if(!$item){
            return false;
        }
        if(isset($item -> value) && !empty($item -> value)){
            return $item -> value;
        }
        return false;
    }

    /* Check the article has password */
    public static function hasPassword($article){
        $item = self::getItem($article);
        if(!$item){
            return false;
        }
        if(isset($item -> value) && !empty($item -> value)){
            return true;
        }
        return false;
    }

    /* Check the article has view */
    public static function isLocked($article){
        $item = self::getItem($article);
        if(!$item){
            return false;
        }

        if(self::hasPassword($article)){

            $user   = JFactory::getUser();
            $asset  = 'com_tz_portfolio_plus.article.'.$item -> content_id;

            if($user -> authorise('addon.content.password.read', $asset)){
                return false;
            }

            $session        = JFactory::getSession();
            $sessionPass    = $session -> get('com_tz_portfolio_plus.addon.content.password.article.'
                .$item -> content_id);

            if($sessionPass && $item -> content_id == $sessionPass -> content_id){
                return false;
            }
        }
        return true;
    }

    protected static function _getQuery($adoElement){
        $db     = JFactory::getDbo();
        $query  = $db -> getQuery(true);

        $query -> select('ad.*');
        $query -> from('#__tz_portfolio_plus_addon_data AS ad');
        $query -> join('INNER', '#__tz_portfolio_plus_extensions AS e ON e.id = ad.extension_id');
        $query -> join('INNER', '#__tz_portfolio_plus_content AS c ON c.id = ad.content_id');
        $query -> where('e.folder = '.$db -> quote('content'));
        $query -> where('e.element = '.$db -> quote('password'));
        $query -> where('ad.element = '.$db -> quote($adoElement));

        return $query;
    }
}