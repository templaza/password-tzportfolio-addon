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

class PlgTZ_Portfolio_PlusContentPassword extends TZ_Portfolio_PlusPlugin
{

    protected $autoloadLanguage         = true;
    protected $ado_password_tmp         = array();
    protected $ado_password_cache       = array();
    protected $ado_password_dispatch    = array();

    public function __construct($subject, array $config = array())
    {
        parent::__construct($subject, $config);

        $this -> ado_password_tmp['allow_contexts'] = array(
            'com_tz_portfolio_plus.article',
            'com_tz_portfolio_plus.portfolio',
            'com_tz_portfolio_plus.date',
            'com_tz_portfolio_plus.search');
    }

    public function onUserBeforeDataValidation($form, &$data){
        $app            = JFactory::getApplication();
        $name           = $form -> getName();
        $contexts       = array('com_tz_portfolio_plus.category', 'com_tz_portfolio_plus.article');

        if(($app -> isClient('administrator') && in_array($name, $contexts))){
            if(is_array($data) && isset($data['rules'])) {
                unset($data['rules']);
            }
        }
    }
    public function onContentPrepareForm($form, $data){
        $app            = JFactory::getApplication();

        $name           = $form -> getName();
        $extension      = null;
        $contexts       = array(
            'com_tz_portfolio_plus.acl',
            'com_tz_portfolio_plus.category',
            'com_tz_portfolio_plus.article'
        );
        // Load form for module
        if($app -> isClient('administrator') && $name == 'com_modules.module'){
            $lang   = JFactory::getLanguage();
            $lang -> load('com_tz_portfolio_plus', JPATH_ADMINISTRATOR);
        }
        $result         = parent::onContentPrepareForm($form, $data);

        if(($app -> isClient('administrator') && !in_array($name, $contexts))){
            return $result;
        }

        $this->__passwordProcessForm($form, $data);

        return true;
    }

    protected function __getFormAccess($section, $file = 'admin/models/forms/access.xml'){
        if(!$section){
            return false;
        }

        $storeId    = __METHOD__;
        $storeId   .= ':'.$section;
        $storeId   .= ':'.$file;
        $storeId    = md5($storeId);

        if(isset($this -> ado_password_cache[$storeId])){
            return $this -> ado_password_cache[$storeId];
        }

        $filePath   = COM_TZ_PORTFOLIO_PLUS_ADDON_PATH.'/'.$this -> _type.'/'.$this -> _name.'/'.$file;
        if(file_exists($filePath)) {
            // Else return the actions from the xml.
            $xml = simplexml_load_file($filePath);
            $xpath  = "/access/section[@name='" . $section . "']/";

            // Get the elements from the xpath
            $elements = $xml->xpath($xpath . 'action[@name][@title][@description]');

            // If there some elements, analyse them
            if (!empty($elements))
            {
                $this -> ado_password_cache[$storeId]    = $elements;
                return $elements;
            }
        }
        return false;
    }

    protected function __passwordProcessForm(JForm $form, $data){

        $rulesField = $form->getFieldXml('rules');
        if($rulesField && method_exists($rulesField, 'addChild')) {
            $section    = (string) $rulesField['section'];

            if($elements = $this -> __getFormAccess($section)){
                foreach ($elements as $action)
                {
                    $_action    = $rulesField -> addChild('action');
                    $_action -> addAttribute('name', (string) $action['name']);
                    $_action -> addAttribute('title', (string) $action['title']);
                    $_action -> addAttribute('description', (string) $action['description']);
                }
            }
            $form->setField($rulesField, null, false);

            // Set value for password field form
            if($form -> getName() == 'com_tz_portfolio_plus.article' && $data){
                // Import helper
                JLoader::import('com_tz_portfolio_plus.addons.content.password.helpers.password',
                    JPATH_SITE.DIRECTORY_SEPARATOR.'components');

                if(is_array($data) && isset($data['id']) && $data['id']){
                    $attribs    = $data['attribs'];
                    if($passwordItem = TZ_Portfolio_Plus_Addon_PasswordHelper::getItem($data['id'])){
                        $attribs['ado_ct_passwords']['password']    = $passwordItem -> value;
                    }
                    $data['attribs']    = $attribs;
                }elseif(is_object($data) && isset($data -> id) && $data -> id){
                    $attribs    = $data -> attribs;

                    if($passwordItem = TZ_Portfolio_Plus_Addon_PasswordHelper::getItem($data -> id)){
                        $attribs['ado_ct_passwords']['password']    = $passwordItem -> value;
                    }
                    $data -> attribs    = $attribs;
                }
            }
        }
    }

    public function onContentBeforeSave($context,$table, $isnew){
        if($context == 'com_tz_portfolio_plus.article' || $context == 'com_tz_portfolio_plus.form'){

            // Get and remove password from attribs (params) of article
            $this -> ado_password_tmp ['password'] = null;
            $attribs = new Registry();
            $attribs -> loadString($table -> attribs);
            $arr    = $attribs -> toArray();

            $adoOptions = (array) $attribs -> get('ado_ct_passwords');

            if(is_array($adoOptions) && isset($adoOptions['password']) && !empty($adoOptions['password'])) {
                $this -> ado_password_tmp ['password'] = $adoOptions['password'];
            }

            unset($arr['ado_ct_passwords']);

            $nAttribs = new Registry();
            $nAttribs -> loadArray($arr);
            $table -> attribs   = $nAttribs -> toString();
        }
    }
    public function onContentAfterSave($context, $data, $isnew){
        // Process data password for model to store it.
        if(($context == 'com_tz_portfolio_plus.article' || $context == 'com_tz_portfolio_plus.form')
            && isset($this -> ado_password_tmp ['password'])
            && !empty($this -> ado_password_tmp['password'])) {
            $adoPasswords   = array('password' => $this -> ado_password_tmp['password']);

            // Get Addon's information when list menu types
            $addon  = TZ_Portfolio_PlusPluginHelper::getPlugin($this->_type, $this->_name);
            $adoPasswords['extension_id']   = $addon -> id;

            $data -> set('ado_ct_passwords', $adoPasswords);
        }

        parent::onContentAfterSave($context, $data, $isnew);
    }

    public function onTPContentBeforePrepare($context, &$item, &$params, $page = 0, $layout = 'default'){
        // Import helper
        JLoader::import('com_tz_portfolio_plus.addons.content.password.helpers.password',
            JPATH_SITE.DIRECTORY_SEPARATOR.'components');

        $this -> ado_password_tmp['context']    = $context;
        if(TZ_Portfolio_Plus_Addon_PasswordHelper::isLocked($item -> id)){
            list($extension, $vName) = explode('.', $context);

            if($extension == 'modules' || $extension == 'module'){
                $cloneParams        = $params;
                $adoProtectItems    = $params -> get('ado_ct_protect_addons');
            }else{
                $cloneParams    = $this -> params;
                $cloneParams -> merge($params);
                if($context == 'com_tz_portfolio_plus.article' && $layout != 'related') {
                    $adoProtectItems    = $cloneParams -> get('ado_ct_protect_addons');
                }else{
                    $adoProtectItems    = $cloneParams -> get('ado_ct_cat_protect_addons');
                }
            }
            $this -> ado_password_tmp['params_tmp']    = $cloneParams;

            // Exclude add-on protect
            if($adoProtectItems){

                $subjects_tmp   = array();
                $_observers = $this -> _subject -> get('_observers');

                foreach($adoProtectItems as $adoId){
                    if($adoItem = TZ_Portfolio_PlusPluginHelper::getPluginById($adoId)){
                        if($adoItem -> type != 'extrafields') {
                            $className  = 'PlgTZ_Portfolio_Plus' . ucfirst($adoItem -> type)
                                . ucfirst($adoItem->name);

                            if(class_exists($className)) {
                                $adoObj = new ReflectionClass($className);
                                $key    = array_search($adoObj -> getName(), $_observers);
                                $subjects_tmp[$key] = $_observers[$key];

                                // Detach add-on protect from _subject dispatcher
                                $detach = $this->_subject->detach($adoObj -> getName());
                            }
                        }
                    }
                }
                if(count($subjects_tmp)){
                    $this -> ado_password_tmp['_subject_tmp']   = $subjects_tmp;
                }
            }
        }
    }

    public function onTPExtraFieldPreapare(&$fieldObject, $article, $articleParams){
        // Exclude extrafield add-on protect
        if($fieldObject && isset($this -> ado_password_tmp['context'])) {
            $context    = $this -> ado_password_tmp['context'];
            // Import helper
            JLoader::import('com_tz_portfolio_plus.addons.content.password.helpers.password',
                JPATH_SITE.DIRECTORY_SEPARATOR.'components');

            if(TZ_Portfolio_Plus_Addon_PasswordHelper::isLocked($article -> id)){
                $cloneParams    = $this -> params;
                $cloneParams -> merge($articleParams);
                if($context == 'com_tz_portfolio_plus.article') {
                    $adoProtectItems    = $cloneParams -> get('ado_ct_protect_addons');
                }else{
                    $adoProtectItems    = $cloneParams -> get('ado_ct_cat_protect_addons');
                }

                // Exclude add-on protect
                if($adoProtectItems){
                    if($adoItem = TZ_Portfolio_PlusPluginHelper::getPlugin('extrafields', $fieldObject -> __get('fieldname'))){
                        if(in_array($adoItem -> id, $adoProtectItems)){
                            $fieldObject    = null;
                        }
                    }
                }
            }
        }

    }

    public function onTPContentAfterPrepare($context, &$item, &$params, $page = 0, $layout = 'default'){

        list($extension, $vName) = explode('.', $context);
        if($item){

            // Import helper
            JLoader::import('com_tz_portfolio_plus.addons.content.password.helpers.password',
                JPATH_SITE.DIRECTORY_SEPARATOR.'components');

            if(TZ_Portfolio_Plus_Addon_PasswordHelper::isLocked($item -> id)){
                $this -> ado_password_tmp['module_context'] = $context;
                $this -> ado_password_tmp['module_params']  = $params;

                $cloneParams    = $this -> params;

                $cloneParams -> merge($params);

                $p_prefix       = 'cat_';

                if($extension == 'modules' || $extension == 'module'){
                    $p_prefix       = '';
                    $protectItems   = $cloneParams -> get('ado_ct_protect_items', array("introtext","fulltext"));
                }else{
                    if($context == 'com_tz_portfolio_plus.article') {
                        $p_prefix       = '';
                        $protectItems   = $cloneParams -> get('ado_ct_protect_items', array("introtext","fulltext"));
                    }else{
                        if($context == 'com_tz_portfolio_plus.date') {
                            $p_prefix   = 'date_';
                        }
                        $protectItems   = $cloneParams -> get('ado_ct_cat_protect_items', array("introtext","fulltext"));
                    }
                }

                foreach($protectItems as $pitem){
                    if($extension == 'module' || $extension == 'modules'){
                        $params->set('show_' .$pitem, 0);
                    }else{
                        if($pitem == 'project_link') {
                            $params -> set('project_link', '');
                        }elseif($pitem == 'introtext') {
                            $item -> introtext  = '';
                            $params->set('show_' . $p_prefix . 'intro', 0);

                            if($context == 'com_tz_portfolio_plus.portfolio' && $params -> exists('show_search_intro')) {
                                $params->set('show_search_intro', 0);
                            }
                        }elseif($pitem != 'fulltext') {
                            $params->set('show_' . $p_prefix . $pitem, 0);

                            if($context == 'com_tz_portfolio_plus.portfolio' && $params -> exists('show_search_'.$pitem)) {
                                $params->set('show_search_'.$pitem, 0);
                            }
                        }

                    }
                }

                if(in_array('fulltext', $protectItems)){
                    $item -> fulltext       = '';
                    if($context == 'com_tz_portfolio_plus.article' && isset($item -> text)){
                        $item -> text = '';
                    }
                }

                // Attach the add-ons protection to old array's key again
                if(isset($this -> ado_password_tmp['_subject_tmp'])){
                    $methods        = $this -> _subject -> get('_methods');
                    $observers      = $this -> _subject -> get('_observers');
                    $subject_tmps   = $this -> ado_password_tmp['_subject_tmp'];

                    foreach($subject_tmps as $key => $subject) {
                        if(is_object($subject)){
                            $adoMethods = array_diff(get_class_methods(get_class($subject)), get_class_methods('JPlugin'));

                            foreach ($adoMethods as $method)
                            {
                                $method = strtolower($method);
                                $methods[$method][] = $key;
                            }
                        }
                        $observers = array_slice($observers, 0 , $key, true)
                            + array($key => $subject)
                            + array_slice($observers, $key, count($observers) - 1, true);

                    }
                    $this -> _subject -> set('_observers', $observers);
                    $this -> _subject -> set('_methods', $methods);
                }
            }

        }
    }

    public function onTPAddOnProcess(&$addon){
        if($addon  && isset($this -> ado_password_tmp['context'])) {
            $context = $this->ado_password_tmp['context'];
            if($context == 'com_tz_portfolio_plus.article' && isset($this -> ado_password_tmp['params_tmp'])) {
                $cloneParams = $this->ado_password_tmp['params_tmp'];
                $protectItems = $cloneParams->get('ado_ct_protect_addons',array());

                if($addon -> type != 'extrafields' && in_array($addon -> id, $protectItems)){
                    $addon  = null;
                }
            }
        }
    }

    public function onContentDisplayListView($context, &$article, $params, $page = 0, $layout = 'default', $module = null){
        return $this -> _adoPasswordDisplayHtml($context, $article, $params, $page, $layout, $module);
    }
    public function onContentDisplayArticleView($context, &$article, $params, $page = 0, $layout = 'default', $module = null){
        return $this -> _adoPasswordDisplayHtml($context, $article, $params, $page, $layout, $module);
    }

    protected function  _adoPasswordDisplayHtml($context, &$article, $params, $page = 0, $layout = 'default', $module = null){
        if($article){
            // Import helper
            JLoader::import('com_tz_portfolio_plus.addons.content.password.helpers.password',
                JPATH_SITE.DIRECTORY_SEPARATOR.'components');

            if(TZ_Portfolio_Plus_Addon_PasswordHelper::isLocked($article -> id)){
                list($extension, $vName)   = explode('.', $context);

                if($extension == 'module' || $extension == 'modules'){
                    if($path = $this -> getModuleLayout($this -> _type, $this -> _name, $extension, $vName, $layout)){

                        JModelLegacy::addIncludePath(COM_TZ_PORTFOLIO_PLUS_ADDON_PATH.'/'.$this -> _type
                            .'/'.$this -> _name.'/models');

                        $__model         = JModelLegacy::getInstance('Password', 'PlgTZ_Portfolio_PlusContentPasswordModel',
                            array('ignore_request' => true));
                        $addon = TZ_Portfolio_PlusPluginHelper::getPlugin($this -> _type, $this -> _name);
                        $__model -> set('addon', $addon);
                        $__model -> set('article', $article);
                        $__model -> set('trigger_params', $params);

                        $passwordItem   = $__model -> getPasswordItem();

                        $this -> setVariable('passwordItem', $passwordItem);

                        // Display html
                        ob_start();
                        require $path;
                        $html = ob_get_contents();
                        ob_end_clean();
                        $html = trim($html);

                        return $html;
                    }
                }else{
                    if($html = $this -> _getViewHtml($context,$article, $params, $layout)){
                        return $html;
                    }
                }
            }
        }
        return false;
    }
//    public function onAfterDispatch(){
//
//        $app    = JFactory::getApplication();
//        $input  = $app -> input;
//
//        $option = $input -> get('option');
//        $view   = $input -> get('view');
//
//        if($app -> isClient('site') && $option == 'com_tz_portfolio_plus' && $view == 'article') {
//            $id = $input -> getInt('id');
//
//            // Import helper
//            JLoader::import('com_tz_portfolio_plus.addons.content.password.helpers.password',
//                JPATH_SITE.DIRECTORY_SEPARATOR.'components');
//
//            if($id && TZ_Portfolio_Plus_Addon_PasswordHelper::isLocked($id)) {
//
//                if(count($this -> ado_password_dispatch)){
//                    tzportfolioplusimport('plugin.modelitem');
//                    if($html = $this -> _getViewHtml($option.'.'.$view ,
//                        $this -> ado_password_dispatch['item'],
//                        $this -> ado_password_dispatch['params'], $this -> ado_password_dispatch['layout'])){
//                        $doc = JFactory::getDocument();
//                        $buf    = $doc -> getBuffer( 'component');
//                        $buf    = trim($buf);
/*                        $buf    = preg_replace('#^(<div.*?>)([\n|\s]+)?(.*)?([\n|\s]+)?(</div>)$#is', '$1'.$html.'$5', $buf);*/
//                        $doc -> setBuffer($buf, 'component');
//                    }
//                }
//            }
//        }
//
//        return true;
//    }
}