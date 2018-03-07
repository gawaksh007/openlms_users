<?php
/**
 * @file
 * Contains \Drupal\openlms_users\Form\UsersForm.
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
use Drupal\group\Access\GroupAccessResult;
use Drupal\group\Plugin\GroupContentEnablerBase;
use Drupal\group\Entity\GroupInterface;
use Drupal\group\Entity\GroupContentInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\group\Entity\Group;
class UsersForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openlms_user_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state){
    // Get all group ids
    $query = \Drupal::service('entity.query')
      ->get('group');
    $entity_ids = $query->execute();
    // get group name from group ids
    foreach ($entity_ids as $key => $value) {
      $group=Group::load($key);
      $group_name[$key]=$group->get('label')->getValue()[0]['value'];
    }
    $form['group_name'] = array(
      '#type' => 'select',
      '#options' => $group_name,
      '#title' => 'A dropdown menu for selecting groups.',
      );
    $form['emails'] = array(
      '#type' => 'textarea',
      '#title' => 'Please enter line separated email ids',
      '#required' => TRUE,  

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
    // validation form input - checking for email values
    $emails = $form_state->getValue('emails');
    $emailids = [];
    $emailids = explode("\n",$emails);
    foreach ($emailids as $email) {    
      $email = trim($email);
      if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_state->setErrorByName('Email', $this->t('Please enter valid list of Email IDs.  "'.$email.'" is not in right format')); 
      }
    }
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state){
    //getting values from the form
    $emails = $form_state->getValue('emails');
    $group_id = $form_state->getValue('group_name');
    
    //get invite code from group id
    $group=Group::load($group_id);
    $invite_code=$group->get('field_invite_code')->getValue()[0]['value'];
    
    $emailids = [];
    $emailids = explode("\n",$emails);
    // defining the mail content
    foreach ($emailids as $email) {
      $mailManager = \Drupal::service('plugin.manager.mail');
      $module = 'openlms_users';
      $key = 'Invitation';
      $to = $email;
      $params['message'] = "Dear Student, You have received an invitation to join the group. Please use the following invite code to join the group.\n The invite code is : ".$invite_code;
      $params['node_title'] = "Invitation to join the group";
      $langcode = \Drupal::currentUser()->getPreferredLangcode();
      $send = true;
      // mail function using smtp auth module
      $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
      if ($result['result'] !== true) {
       drupal_set_message(t('There was a problem sending your message and it was not sent.'), 'error');
      }
      else {
        drupal_set_message(t('Invitation has been sent.'));
      } 
    }
  }
}