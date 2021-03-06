<?php
/**
 * Created by PhpStorm.
 * User: qunabu
 * Date: 16.09.17
 * Time: 12:45
 */

class GridFieldDuplicateBlocksButton implements GridField_HTMLProvider, GridField_ActionProvider {
  protected $targetFragment;

  public function __construct($targetFragment = "before") {
    $this->targetFragment = $targetFragment;
  }

  public function getHTMLFragments($gridField) {
    $button = new GridField_FormAction(
      $gridField,
      'duplicateall',
      _t('Blocks.DUPLICATE', 'Duplicate All'),
      'duplicateall',
      null
    );
    $button->setAttribute('data-icon', 'chain--arrow');
    $button->addExtraClass('deleteWithConfirm gridfield-better-buttons-delete');
    return array(
      $this->targetFragment => '<p class="grid-del-button">' . $button->Field() . '</p>',
    );
  }

  public function getActions($gridField) {
    return array('duplicateall');
  }

  public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
    if($actionName == 'duplicateall') {
      return $this->handleduplicateall($gridField);
    }
  }

  public function handleduplicateall($gridField, $request = null) {

    /** @var ManyManyList $items */
    $items=$gridField->getList();
    $pageId = $items->getForeignID();
    $page = DataObject::get_by_id('Page', $pageId);

    //$last_id = DB::query('SELECT ID FROM "Block" ORDER BY ID DESC')->value();

    foreach ($items as $block) {

      $classname = ClassInfo::class_name($block);

      $data = $block->toMap();
      unset($data['ID']);

      $new_block = new $classname();
      $new_block->update($data);
      $new_block->write();

      $page->Blocks()->remove($block);
      $page->Blocks()->add($new_block);

    }
    Controller::curr()->redirectBack();
  }
}