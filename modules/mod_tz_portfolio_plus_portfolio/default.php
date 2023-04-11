<?php
/*------------------------------------------------------------------------
# plg_extravote - ExtraVote Plugin
# ------------------------------------------------------------------------
# author    Joomla!Vargas
# copyright Copyright (C) 2010 joomla.vargas.co.cr. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://joomla.vargas.co.cr
# Technical Support:  Forum - http://joomla.vargas.co.cr/forum
-------------------------------------------------------------------------*/

// No direct access
defined('_JEXEC') or die;

if(isset($passwordItem) && $passwordItem){
        ?>
    <div class="ado-ct-password">
        <div class="ado-ct-password__message mb-1 badge badge-warning text-wrap"><?php echo $passwordItem -> message_protection; ?></div>
    </div>
<?php
}