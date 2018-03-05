<?php
/**
 * @file
 * Contains \Drupal\openlms_users\Form\JoinForm.
 */
namespace Drupal\openlms_users\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Database\Database;
use Drupal\Core\Entity;
use Drupal\Core\Access\AccessibleInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
class JoinForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openlms_join_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) 

  {
    
    $form['invite_code'] = array(
      '#type' => 'textfield',
      '#size' => 16,
      '#title' => 'Invite Code',
      );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
      );
    return $form;
  }
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $invite_code = $form_state->getValue('invite_code');
    if(!strlen($invite_code) == 16){
      $form_state->setErrorByName('Code length', $this->t('Please enter valid invitation code'));
    }

  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $invite_code = $form_state->getValue('invite_code');
    print_r($invite_code);
    die();

    
}
}