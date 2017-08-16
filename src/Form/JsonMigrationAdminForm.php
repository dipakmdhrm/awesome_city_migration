<?php

namespace Drupal\awesome_city_migration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class JsonMigrationAdminForm.
 */
class JsonMigrationAdminForm extends FormBase {

  /**
   * Drupal\Core\Config\ConfigManager definition.
   *
   * @var \Drupal\Core\Config\ConfigManager
   */
  protected $configManager;
  /**
   * Constructs a new JsonMigrationAdminForm object.
   */

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'json_migration_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = \Drupal::config('migrate_plus.migration.awesome_city_json');

    // Get list of source fields.
    $source_fields = [];
    foreach($config->get('source.fields') as $value) {
      $source_fields[] = $value['name'];
    }

    // Get Migration mappings.
    $migration_mappings = array_flip($config->get('process'));

    // Get list of all fields on awesome_city entity to
    // build the destination option list.
    $options = [];
    $awesome_city_fields = \Drupal::service('entity_field.manager')->getFieldDefinitions('awesome_city');
    foreach ($awesome_city_fields as $key => $value) {
      $options[$key] = $value->getLabel()->render();
    }

    // Create fields.
    foreach ($source_fields as $source_field) {
      $form['fieldset_' . $source_field] = [
        '#type' => 'fieldset',
        '#title' => 'Srouce Field: ' . $source_field,
      ];

      $form['fieldset_' . $source_field][$source_field] = [
        '#type' => 'select',
        '#title' => $this->t('Destination'),
        '#options' => $options,
        '#default_value' => $migration_mappings[$source_field],
      ];
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')->getEditable('migrate_plus.migration.awesome_city_json');

    // Get list of source fields.
    $source_fields = [];
    foreach($config->get('source.fields') as $value) {
      $source_fields[] = $value['name'];
    }

    // Get old destination.
    $old_destinations = $config->get('process');

    // Display result.
    foreach ($form_state->getValues() as $source => $destination) {
      if (in_array($source, $source_fields)) {
        $config->set('process.' . $destination, $source)->save();
        unset($old_destinations[$destination]);
      }
    }

    foreach ($old_destinations as $key => $value) {
      \Drupal::logger('JsonMigrationAdminForm')->notice($key);
      \Drupal::service('config.factory')->getEditable('migrate_plus.migration.awesome_city_json')->clear('process.' . $key)->save();
    }
  }
}
