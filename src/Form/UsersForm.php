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
  public function buildForm(array $form, FormStateInterface $form_state) 

  {
    $nids = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'group']);
    $titles=array();
    foreach ($nids as $key=>$value) {
      $nodes=\Drupal\node\Entity\Node::load($key);
      foreach ($nodes as $keys=>$values) {
        $title=$nodes->get('title')->getValue()[0]['value'];
        $invite_code=$nodes->get('field_invite_code')->getValue()[0]['value'];
      } 
      $titles[$invite_code]=$title;

    }
    $form['group_name'] = array(
      '#type' => 'select',
      '#options' => $titles,
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
    $emails = $form_state->getValue('emails');
    $array = [];
    $array = explode("\n",$emails);
    foreach ($array as $email) {    
      $email = trim($email);
      if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_state->setErrorByName('Email', $this->t('Please enter valid list of Email IDs.  "'.$email.'" is not in right format')); 
      }
    }
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $emails = $form_state->getValue('emails');
    $group_name = $form_state->getValue('group_name');
    
    $array = [];
    $array = explode("\n",$emails);
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'openlms_users';
    $key = 'Invitation';
    $to = \Drupal::currentUser()->getEmail();
    $params['message'] = "This is the Body of the mail".$group_name;
    $params['node_title'] = "Invitation to join the group";
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = true;
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    if ($result['result'] !== true) {
     drupal_set_message(t('There was a problem sending your message and it was not sent.'), 'error');
   }
   else {
     drupal_set_message(t('Your message has been sent.'));
   } 
 }
}