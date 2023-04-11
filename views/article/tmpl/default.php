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

if($this -> form || $this -> passwordItem){

?>
<div class="ado-ct-password">

    <?php if($passwordItem = $this -> passwordItem){ ?>
        <div class="ado-ct-password__message mb-2 badge badge-warning text-wrap"><?php echo $passwordItem -> message_protection; ?></div>
    <?php } ?>

    <?php if($form = $this -> form){ ?>
    <form action="<?php echo $this -> item -> link;?>"
          method="post"
          class="form-validate ado-ct-password__form">
        <div class="input-group input-group-sm mb-3">
            <div class="input-group-prepend input-group-addon">
                <span class="input-group-text"><i class="tps tp-lock"></i></span>
            </div>
            <?php echo $form -> getInput('password');?>
            <div class="input-group-append input-group-btn">
                <button type="submit" class="btn btn-outline-secondary"><?php echo JText::_('TP_ADDON_CONTENT_PASSWORD_VIEW'); ?></button>
            </div>
        </div>

        <input type="hidden" name="option" value="com_tz_portfolio_plus"/>
        <input type="hidden" name="view" value="addon"/>
        <input type="hidden" name="id" value="<?php echo $this -> item -> id;?>"/>
        <input type="hidden" name="return" value="<?php echo base64_encode($this -> item -> link);?>" />
        <input type="hidden" name="addon_task" value="password.login"/>
        <?php if($addon = $this -> state -> get($this -> getName().'.addon')){?>
        <input type="hidden" name="addon_id" value="<?php echo $addon -> id;?>"/>
        <?php } ?>
    </form>
    <?php } ?>
</div>
<?php }?>