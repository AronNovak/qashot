<?php

namespace Drupal\qa_shot\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the QAShot Test entity.
 *
 * @ingroup qa_shot
 *
 * @ContentEntityType(
 *   id = "qa_shot_test",
 *   label = @Translation("QAShot Test"),
 *   label_singular = @Translation("QAShot Test"),
 *   label_plural = @Translation("QAShot Tests"),
 *   label_count = @PluralTranslation(
 *     singular = "@count QAShot Test",
 *     plural = "@count QAShot Tests"
 *   ),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\qa_shot\QAShotTestListBuilder",
 *     "views_data" = "Drupal\qa_shot\Entity\QAShotTestViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\qa_shot\Form\QAShotTestForm",
 *       "add" = "Drupal\qa_shot\Form\QAShotTestForm",
 *       "edit" = "Drupal\qa_shot\Form\QAShotTestForm",
 *       "delete" = "Drupal\qa_shot\Form\QAShotTestDeleteForm",
 *     },
 *     "access" = "Drupal\qa_shot\QAShotTestAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\qa_shot\QAShotTestHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "qa_shot_test",
 *   data_table = "qa_shot_test_field_data",
 *   revision_table = "qa_shot_test_revision",
 *   revision_data_table = "qa_shot_test_field_revision",
 *   admin_permission = "administer qashot test entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/qa_shot_test/{qa_shot_test}",
 *     "add-form" = "/admin/structure/qa_shot_test/add",
 *     "edit-form" = "/admin/structure/qa_shot_test/{qa_shot_test}/edit",
 *     "delete-form" = "/admin/structure/qa_shot_test/{qa_shot_test}/delete",
 *     "collection" = "/admin/structure/qa_shot_test",
 *     "version-history" = "/admin/structure/qa_shot_test/{qa_shot_test}/revisions",
 *     "revision" = "/admin/structure/qa_shot_test/{qa_shot_test}/revisions/{qa_shot_test_revision}/view",
 *   },
 *   field_ui_base_route = "qa_shot_test.settings"
 * )
 */
class QAShotTest extends ContentEntityBase implements QAShotTestInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    // If no revision author has been set explicitly, make the node owner the
    // revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preSaveRevision(EntityStorageInterface $storage, \stdClass $record) {
    parent::preSaveRevision($storage, $record);

    if (!$this->isNewRevision() && isset($this->original) && (!isset($record->revision_log) || $record->revision_log === '')) {
      // If we are updating an existing node without adding a new revision, we
      // need to make sure $entity->revision_log is reset whenever it is empty.
      // Therefore, this code allows us to avoid clobbering an existing log
      // entry with an empty one.
      $record->revision_log = $this->original->revision_log->value;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? NODE_PUBLISHED : NODE_NOT_PUBLISHED);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionCreationTime() {
    return $this->get('revision_timestamp')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionCreationTime($timestamp) {
    $this->set('revision_timestamp', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionAuthor() {
    return $this->getRevisionUser();
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionUser() {
    return $this->get('revision_uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionAuthorId($uid) {
    $this->setRevisionUserId($uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionUser(UserInterface $user) {
    $this->set('revision_uid', $user);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionUserId() {
    return $this->get('revision_uid')->entity->id();
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionUserId($user_id) {
    $this->set('revision_uid', $user_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionLogMessage() {
    return $this->get('revision_log')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionLogMessage($revision_log_message) {
    $this->set('revision_log', $revision_log_message);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the QAShot Test entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the QAShot Test entity.'))
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setRequired(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRevisionable(TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the QAShot Test is published.'))
      ->setDefaultValue(TRUE)
      ->setRevisionable(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'))
      ->setRevisionable(TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'))
      ->setRevisionable(TRUE);

    $fields['revision_timestamp'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Revision timestamp'))
      ->setDescription(t('The time that the current revision was created.'))
      ->setQueryable(FALSE)
      ->setRevisionable(TRUE);

    $fields['revision_uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Revision user ID'))
      ->setDescription(t('The user ID of the author of the current revision.'))
      ->setSetting('target_type', 'user')
      ->setQueryable(FALSE)
      ->setRevisionable(TRUE);

    $fields['revision_log'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Revision log message'))
      ->setDescription(t('Briefly describe the changes you have made.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue('')
      ->setDisplayOptions('form', array(
        'type' => 'string_textarea',
        'weight' => 25,
        'settings' => array(
          'rows' => 4,
        ),
      ));

    return $fields;
  }

  /**
   * Default value callback for 'uid' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return array
   *   An array of default values.
   */
  public static function getCurrentUserId() {
    return array(\Drupal::currentUser()->id());
  }
}
