<?php
class PluginFormcreatorTarget extends CommonDBTM
{
   /**
    * Check if current user have the right to create and modify requests
    *
    * @return boolean True if he can create and modify requests
    */
   public static function canCreate()
   {
      return true;
   }

   /**
    * Check if current user have the right to read requests
    *
    * @return boolean True if he can read requests
    */
   public static function canView()
   {
      return true;
   }

   public static function getTypeName($nb = 1)
   {
      return _n('Destination', 'Destinations', $nb, 'formcreator');
   }

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
   {
      switch ($item->getType()) {
         case "PluginFormcreatorForm":
            $env       = new self;
            $found_env = $env->find('plugin_formcreator_forms_id = '.$item->getID());
            $nb        = count($found_env);
            return self::createTabEntry(self::getTypeName($nb), $nb);
      }
      return '';
   }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
   {
      global $CFG_GLPI;

      echo '<table class="tab_cadre_fixe">';

      echo '<tr>';
      echo '<th colspan="3">'._n('Destinations', 'Destinations', 2, 'formcreator').'</th>';
      echo '</tr>';

      $target_class    = new PluginFormcreatorTarget();
      $found_targets = $target_class->find('plugin_formcreator_forms_id = '.$item->getID());
      $target_number   = count($found_targets);
      $token           = Session::getNewCSRFToken();
      $i = 0;
      foreach ($found_targets as $target) {
         $i++;
         echo '<tr class="line'.($i % 2).'">';
         $targetItemUrl = Toolbox::getItemTypeFormURL($target['itemtype']) . '?id=' . $target['items_id'];
         echo '<td onclick="document.location=\'' . $targetItemUrl . '\'" style="cursor: pointer">';

         echo $target['name'];
         echo '</td>';

         echo '<td align="center" width="32">';
         echo '<img src="'.$CFG_GLPI['root_doc'].'/plugins/formcreator/pics/edit.png"
                  alt="*" title="'.__('Edit').'" ';
         echo 'onclick="document.location=\'' . $targetItemUrl . '\'" align="absmiddle" style="cursor: pointer" /> ';
         echo '</td>';

         echo '<td align="center" width="32">';
         echo '<img src="'.$CFG_GLPI['root_doc'].'/plugins/formcreator/pics/delete.png"
                  alt="*" title="'.__('Delete', 'formcreator').'"
                  onclick="deleteTarget('.$item->getID().', \''.$token.'\', '.$target['id'].')" align="absmiddle" style="cursor: pointer" /> ';
         echo '</td>';

         echo '</tr>';
      }


      // Display add target link...
      echo '<tr class="line'.(($i + 1) % 2).'" id="add_target_row">';
      echo '<td colspan="3">';
      echo '<a href="javascript:addTarget('.$item->getID().', \''.$token.'\');">
                <img src="'.$CFG_GLPI['root_doc'].'/pics/menu_add.png" alt="+" align="absmiddle" />
                '.__('Add a destination', 'formcreator').'
            </a>';
      echo '</td>';
      echo '</tr>';

      // OR display add target form
      echo '<tr class="line'.(($i + 1) % 2).'" id="add_target_form" style="display: none;">';
      echo '<td colspan="3" id="add_target_form_td"></td>';
      echo '</tr>';

      echo "</table>";
   }

   /**
    * Prepare input datas for adding the question
    * Check fields values and get the order for the new question
    *
    * @param $input datas used to add the item
    *
    * @return the modified $input array
    **/
   public function prepareInputForAdd($input)
   {
      global $DB;

      // Decode (if already encoded) and encode strings to avoid problems with quotes
      foreach ($input as $key => $value) {
         $input[$key] = plugin_formcreator_encode($value);
      }

      // Control fields values :
      // - name is required
      if(isset($input['name'])
            && empty($input['name'])) {
               Session::addMessageAfterRedirect(__('The name cannot be empty!', 'formcreator'), false, ERROR);
               return array();
            }
            // - field type is required
            if(isset($input['itemtype'])) {
               if (empty($input['itemtype'])) {
                  Session::addMessageAfterRedirect(__('The type cannot be empty!', 'formcreator'), false, ERROR);
                  return array();
               }

               switch ($input['itemtype']) {
                  case 'PluginFormcreatorTargetTicket':
                     $targetticket      = new PluginFormcreatorTargetTicket();
                     $id_targetticket   = $targetticket->add(array(
                           'name'    => $input['name'],
                           'comment' => '##FULLFORM##'
                     ));
                     $input['items_id'] = $id_targetticket;

                     if (!isset($input['_skip_create_actors'])
                           || !$input['_skip_create_actors']) {
                              $targetTicket_actor = new PluginFormcreatorTargetTicket_Actor();
                              $targetTicket_actor->add(array(
                                    'plugin_formcreator_targettickets_id'  => $id_targetticket,
                                    'actor_role'                           => 'requester',
                                    'actor_type'                           => 'creator',
                                    'use_notification'                     => '1'
                              ));
                              $targetTicket_actor = new PluginFormcreatorTargetTicket_Actor();
                              $targetTicket_actor->add(array(
                                    'plugin_formcreator_targettickets_id'  => $id_targetticket,
                                    'actor_role'                           => 'observer',
                                    'actor_type'                           => 'validator',
                                    'use_notification'                     => '1'
                              ));
                           }
                           break;
                  case 'PluginFormcreatorTargetChange':
                     $targetchange      = new PluginFormcreatorTargetChange();
                     $id_targetchange   = $targetchange->add(array(
                           'name'    => $input['name'],
                           'comment' => '##FULLFORM##'
                     ));
                     $input['items_id'] = $id_targetchange;

                     if (!isset($input['_skip_create_actors'])
                           || !$input['_skip_create_actors']) {
                              $targetChange_actor = new PluginFormcreatorTargetChange_Actor();
                              $targetChange_actor->add(array(
                                    'plugin_formcreator_targetchanges_id'  => $id_targetchange,
                                    'actor_role'                           => 'requester',
                                    'actor_type'                           => 'creator',
                                    'use_notification'                     => '1',
                              ));
                              $targetChange_actor = new PluginFormcreatorTargetChange_Actor();
                              $targetChange_actor->add(array(
                                    'plugin_formcreator_targetchanges_id'  => $id_targetchange,
                                    'actor_role'                           => 'observer',
                                    'actor_type'                           => 'validator',
                                    'use_notification'                     => '1',
                              ));
                           }
                           break;
               }
            }

            // generate a uniq id
            if (!isset($input['uuid'])
                  || empty($input['uuid'])) {
                     $input['uuid'] = plugin_formcreator_getUuid();
                  }

                  return $input;
   }

   /**
    * Prepare input datas for updating the form
    *
    * @param $input datas used to add the item
    *
    * @return the modified $input array
    **/
   public function prepareInputForUpdate($input)
   {
      // Decode (if already encoded) and encode strings to avoid problems with quotes
      foreach ($input as $key => $value) {
         $input[$key] = plugin_formcreator_encode($value);
      }

      // generate a uniq id
      if (!isset($input['uuid'])
            || empty($input['uuid'])) {
               $input['uuid'] = plugin_formcreator_getUuid();
            }

            return $input;
   }

   public function pre_deleteItem() {
      $itemtype = $this->getField('itemtype');
      $item = new $itemtype();
      return $item->delete(array('id' => $this->getField('items_id')));
   }


   /**
    * Import a form's target into the db
    * @see PluginFormcreatorForm::importJson
    *
    * @param  integer $forms_id  id of the parent form
    * @param  array   $target the target data (match the target table)
    * @return integer the target's id
    */
   public static function import($forms_id = 0, $target = array()) {
      $item = new self;

      $target['plugin_formcreator_forms_id'] = $forms_id;
      $target['_skip_checks']                = true;
      $target['_skip_create_actors']         = true;

      if ($targets_id = plugin_formcreator_getFromDBByField($item, 'uuid', $target['uuid'])) {
         // add id key
         $target['id'] = $targets_id;

         // update target
         $item->update($target);
      } else {
         //create target
         $targets_id = $item->add($target);
         $item->getFromDB('$targets_id');
      }

      // import sub table
      $target['itemtype']::import($item->fields['items_id'], $target['_data']);

      return $targets_id;
   }


   /**
    * Export in an array all the data of the current instanciated target
    * @param boolean $remove_uuid remove the uuid key
    *
    * @return array the array with all data (with sub tables)
    */
   public function export($remove_uuid = false) {
      if (!$this->getID()) {
         return false;
      }

      $form_target_actor = new PluginFormcreatorTargetTicket_Actor;
      $target            = $this->fields;
      $targetId = $this->getID();

      // get data from subclass (ex PluginFormcreatorTargetTicket)
      $target_item = new $target['itemtype'];
      if ($target_item->getFromDB($target['items_id'])) {
         $target['_data'] = $target_item->export();
      }

      // remove key and fk
      unset($target['id'],
            $target['items_id'],
            $target['plugin_formcreator_forms_id'],
            $target['tickettemplates_id']);


      // get target actors
      $target['_data']['_actors'] = [];
      $all_target_actors = $form_target_actor->find("`plugin_formcreator_targettickets_id` = '$targetId'");
      foreach($all_target_actors as $target_actors_id => $target_actor) {
         if ($form_target_actor->getFromDB($target_actors_id)) {
            $target['_data']['_actors'][] = $form_target_actor->export($remove_uuid);
         }
      }

      if ($remove_uuid) {
         $target['uuid'] = '';
      }

      return $target;
   }
}
