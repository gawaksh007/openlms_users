  <?php
  /**
   * @file
   * Contains \Drupal\openlms_users\Form\UsersForm.
   */
  namespace Drupal\openlms_users\Form;
  use Drupal\Core\Form\FormBase;
  use Drupal\Core\Form\FormStateInterface;
  class UsersForm extends FormBase {
    /**
     * {@inheritdoc}
     */
    public function getFormId() {
      return 'uid';
    }

      /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $uid= NULL) 

    {
      
      

      foreach ( $data as $key=>$value )
      { 
        $form[$value[0]] = array (
        '#type' => 'radios',
        '#title' => $key,
        '#options' => $value[1],
      );


      }
      $form['qid'] = array (
        '#type' => 'hidden',
        '#value' => $qid,
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
    public function submitForm(array &$form, FormStateInterface $form_state) {
     
  $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
      foreach ($form_state->getValues() as $key => $value) {
        if(is_numeric($key))
        db_insert('openlms_quiz')
      ->fields(array(
        'uid' => $user->id(),
        'qid' => $form_state->getValue('qid'),   
        'question' => $key, 
        'Response' => $value+1, 
        'Submitted' => time(), 
      ))->execute();
      drupal_set_message("successfully Submitted the responses");
      }
     
      
  }

     }
