<?php
/**
 * @file
 * Contains \Drupal\openlms_users\Form\JoinForm.
 */
namespace Drupal\openlms_users\Form;
use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Entity;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Access\AccessibleInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;

use Drupal\group\Access\GroupAccessResult;
use Drupal\group\Plugin\GroupContentEnablerBase;
use Drupal\group\Entity\GroupInterface;
use Drupal\group\Entity\GroupContentInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\group\Entity\Group;
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
    
    // load the group with the same invite code as inserted in the form
    $groups = \Drupal::entityTypeManager()
    ->getStorage('group')
    ->loadByProperties(['field_invite_code' => $invite_code]);
    
    // get currently logged in user id 
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    $uid= $user->get('name')->value;

    // getting members list of the group
    $names=array();
    if(sizeof($groups) !== 0 ){
      foreach ($groups as $key => $value) {
        $group = Group::load($key);
        $members=$group->getContentEntities('group_membership');
        foreach ($members as $node => $values) {
          $names[]=$values->get('name')->getValue()[0]['value'];
        }
      }
    }
    else {
      drupal_set_message(t("Please enter the correct Invite Code ."), 'error');
      print_r("wront invite code");
      die();    
    }
        // check if user is already enrolled in a group
    if(in_array($uid, $names))
    {
      drupal_set_message(t("You are already a member of the group."), 'error');
    }
    else {
      // add the user to the group
      $group->addMember($user);
      drupal_set_message(t("Thank you for joining the group"));
    }
  }
}