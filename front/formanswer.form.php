<?php
include ("../../../inc/includes.php");

// Check if plugin is activated...
$plugin = new Plugin();
if ($plugin->isActivated("formcreator")) {
   $formanswer = new PluginFormcreatorFormanswer();

   // Edit an existing target ticket
   if(isset($_POST['update'])) {
      $formanswer->check($_POST['id'],'w');
      $formanswer->update($_POST);
      Html::back();

   } elseif(isset($_POST['refuse_formanswer'])) {
      $formanswer->check($_POST['id'],'w');

      $_POST['plugin_formcreator_forms_id'] = (int) $_POST['formcreator_form'];
      $_POST['status']                      = 'refused';
      $_POST['save_formanswer']             = true;
      $formanswer->saveAnswers($_POST);

      Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/formanswer.php');

   } elseif(isset($_POST['accept_formanswer'])) {
      $formanswer->check($_POST['id'],'w');

      $_POST['plugin_formcreator_forms_id'] = (int) $_POST['formcreator_form'];
      $_POST['status']                      = 'accepted';
      $_POST['save_formanswer']             = true;
      $formanswer->saveAnswers($_POST);

      Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/formanswer.php');

   } elseif(isset($_POST['save_formanswer'])) {
      $_POST['plugin_formcreator_forms_id'] = (int) $_POST['formcreator_form'];
      $_POST['status']                      = 'waiting';
      $formanswer->saveAnswers($_POST);
      Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/formanswer.php');

   // Show target ticket form
   } else {
      Html::header(
         __('Form Creator', 'formcreator'),
         $_SERVER['PHP_SELF'],
         'plugins',
         'formcreator',
         'options'
      );

      $formanswer->showForm($_REQUEST);

      Html::footer();
   }

// Or display a "Not found" error
} else {
   Html::displayNotFoundError();
}