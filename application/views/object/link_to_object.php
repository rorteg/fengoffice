<?php
  require_javascript('og/modules/linkToObjectForm.js');
  set_page_title(lang('link objects'));
  project_tabbed_navigation(PROJECT_TAB_FILES);
  project_crumbs(array(
    array(lang('object'), get_url('object')),
    array(lang('link objects'))
  ));
  //add_stylesheet_to_page('project/link_objects.css');

?>
<form class="internalForm" action="<?php echo $link_to_object->getLinkObjectUrl() ?>" method="post" enctype="multipart/form-data">
<?php tpl_display(get_template_path('form_errors')) ?>
  <div class="hint"><?php echo lang('link objects to object desc', $link_to_object->getObjectUrl(), clean($link_to_object->getObjectName())) ?></div>
  <div>
    <?php echo radio_field('link[what]', array_var($link_data, 'what') == 'existing_object', array('value' => 'existing_object', 'id' => 'linkFormExistingObject', 'onclick' => 'App.modules.linkToObjectForm.toggleLinkForms()')) ?> <label for="linkFormExistingObject" class="checkbox"><?php echo lang('link existing object') ?></label>
  </div>
  
  <div id="linkFormExistingObjectControls">
    <fieldset>
      <legend><?php echo lang('select object') ?></legend>
      <?php echo select_project_object('link[object_id]', active_project(), array_var($object_data, 'object_id'), $already_linked_objects_ids, array('id' => 'linkFormSelectObject', 'style' => 'width: 300px')) ?>
    </fieldset>
  </div>
  
  <div>
    <?php echo radio_field('link[what]', array_var($link_data, 'what') <> 'existing_object', array('value' => 'new_object', 'id' => 'linkFormNewObject', 'onclick' => 'App.modules.linkToObjectForm.toggleLinkForms()')) ?> <label for="linkFormNewObject" class="checkbox"><?php echo lang('upload and link') ?></label>
  </div>
  
  <div id="linkFormNewObjectControls">
    <?php echo render_linked_objects() ?>
  </div>
  
  <script>
    App.modules.linkToObjectForm.toggleLinkForms();
  </script>
  
  <?php echo submit_button(lang('link objects')) ?>
  
</form>