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

// Import addon_data model
JLoader::import('com_tz_portfolio_plus.models.addon_data',JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components');

//class PlgTZ_Portfolio_PlusContentModelPassword extends TZ_Portfolio_PlusPluginModelAdmin{
class PlgTZ_Portfolio_PlusContentModelPassword extends TZ_Portfolio_PlusModelAddon_Data{
    public function save($articleTable){

        // Import defines
        JLoader::import('com_tz_portfolio_plus.addons.content.password.includes.defines',
            JPATH_SITE.DIRECTORY_SEPARATOR.'components');


        $data               = array();
        $adoPasswordDatas   = $articleTable -> get('ado_ct_passwords');

        $table  = $this -> getTable();

        if($table -> load(array(
            'element'       => TP_ADDON_CONTENT_PASSWORD_KEY_CONTENT_PASSWORD,
            'content_id'    => $articleTable -> id,
            'extension_id' => $adoPasswordDatas['extension_id']
        ))){
            $data['id'] = $table -> id;
        }

        $data['value']          = $adoPasswordDatas['password'];
        $data['element']        = TP_ADDON_CONTENT_PASSWORD_KEY_CONTENT_PASSWORD;
        $data['published']      = 1;
        $data['content_id']     = $articleTable -> id;
        $data['extension_id']   = $adoPasswordDatas['extension_id'];

        return parent::save($data);
    }
}