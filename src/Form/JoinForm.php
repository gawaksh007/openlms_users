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
    
    // load the node with the same invite code as inserted in the form
    $nodes = \Drupal::entityTypeManager()
  ->getStorage('node')
  ->loadByProperties(['field_invite_code' => $invite_code]);
    
    // get currently logged in user id 
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    $uid= $user->get('uid')->value;

    // getting members list of the group
    foreach ($nodes as $key => $value) {
      $nid=$key;
      foreach ($value as $node => $values) {
        if($node=="field_members")
          $target_ids=$values->getValue();
          foreach ($target_ids as $target_id) {
            $member_ids[]=$target_id['target_id'];
          }
      }
    }
    // check if user is already enrolled in a group
    if(in_array($uid, $member_ids))
    {
      drupal_set_message(t("You are already a member of the group."), 'error');
    }
    else {
      $member_ids[]=$uid;
      $node = Node::load($nid);
      $node->field_members[] = ['target_id' => $uid];
      $node->save();
      drupal_set_message(t("Thank you for joining the group"));
    }
  }
}