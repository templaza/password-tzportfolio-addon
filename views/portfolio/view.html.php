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

// No direct access.
defined('_JEXEC') or die;

class PlgTZ_Portfolio_PlusContentPasswordViewPortfolio extends JViewLegacy{

    protected $addon;
    protected $item     = null;
    protected $params   = null;
    protected $audio    = null;
    protected $passwordItem = null;

    /* To add script once */
    protected $head     = array();

    public function display($tpl = null){
        $this -> item           = $this -> get('Item');
        $this -> passwordItem   = $this -> get('PasswordItem');
        $state                  = $this -> get('State');
        $params                 = $state -> get('params');
        $this -> params         = $params;

        parent::display($tpl);
    }
}