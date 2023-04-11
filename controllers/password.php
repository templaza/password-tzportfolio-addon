<?php
/*------------------------------------------------------------------------

# TZ Portfolio Plus Extension

# ------------------------------------------------------------------------

# Author:    DuongTVTemPlaza

# Copyright: Copyright (C) 2011-2019 TZ Portfolio.com. All Rights Reserved.

# @License - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Website: http://www.tzportfolio.com

# Technical Support:  Forum - https://www.tzportfolio.com/help/forum.html

# Family website: http://www.templaza.com

# Family Support: Forum - https://www.templaza.com/Forums.html

-------------------------------------------------------------------------*/

// no direct access
defined('_JEXEC') or die;

tzportfolioplusimport('controller.form');

class PlgTZ_Portfolio_PlusContentPasswordControllerPassword extends TZ_Portfolio_Plus_AddOnControllerForm
{
    public function login(){

        $app    = JFactory::getApplication();
        $session= JFactory::getSession();
        $model  = $this -> getModel();
        $data   = $this->input->post->get('jform', array(), 'array');
        $return = $this -> input -> post -> get('return', '','default', 'base64');
        $context= "$this->option.addon.content.password.edit.$this->context";

        $articleId = $this->input->getInt('id');

        // Validate the posted data.
        // Sometimes the form needs some posted data, such as for plugins and modules.
        $form = $model->getForm($data, false);

        if (!$form)
        {
//            $app->enqueueMessage($model->getError(), 'error');
            $this -> setMessage($model->getError(), 'error');

            return false;
        }

        // Test whether the data is valid.
        $validData = $model->validate($form, $data);

        // Check for validation errors.
        if ($validData === false)
        {
            // Get the validation messages.
            $errors = $model->getErrors();

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
            {
                if ($errors[$i] instanceof Exception)
                {
                    $this -> setMessage($errors[$i]->getMessage(), 'warning');
//                    $app->enqueueMessage($errors[$i]->getMessage(), 'warning');
                }
                else
                {
                    $this -> setMessage($errors[$i], 'warning');
//                    $app->enqueueMessage($errors[$i], 'warning');
                }
            }

            // Save the data in the session.
            $app->setUserState($context . '.data', $data);

            // Redirect back to the edit screen.
            $this -> setRedirect($this -> getReturnPage());
//            $this->setRedirect(
//                JRoute::_($return?base64_decode($return):$this -> getAddonRedirect()
//                    , false
//                )
//            );

            return false;
        }


        // Attempt to save the data.
        if (!$model-> login($validData))
        {
            // Save the data in the session.
            $app->setUserState($context . '.data', $validData);

            // Redirect back to the edit screen.
//            $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
            $this->setMessage($model->getError(), 'error');

            $addonIdURL		= ($addon_id = $this->input -> getInt('addon_id'))?'&addon_id='.$addon_id:'';
            $view           = $this -> input -> getCmd('view');
            $this -> setRedirect($this -> getReturnPage());
//            $this->setRedirect(
//                JRoute::_('index.php?option=' . $this->option . '&view='.$view
//                    .$addonIdURL.'&addon_view=' . $this->view_item. $this->getRedirectToItemAppend($recordId, $urlVar)
//                    , false
//                )
//            );

            return false;
        }

        // Redirect back to the edit screen.
//        $this -> setMessage('');
        $this -> setRedirect($this -> getReturnPage());
//        $this->setRedirect(
//            JRoute::_($this -> getAddonRedirect(). $this->getRedirectToItemAppend($recordId, $urlVar)
//                , false
//            )
//        );

        // Invoke the postSave method to allow for the child class to access the model.
        $this->postSaveHook($model, $validData);

        return true;
    }


    protected function getReturnPage()
    {
        $return = $this->input->get('return', null, 'base64');

//        if (empty($return) || !JUri::isInternal(base64_decode($return)))
        if (empty($return))
        {
            return JUri::base();
        }
        else
        {
            return base64_decode($return);
        }
    }
}