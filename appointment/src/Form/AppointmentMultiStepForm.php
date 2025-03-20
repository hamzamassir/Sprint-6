<?php

namespace Drupal\appointment\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a multi-step form for booking appointments.
 */
class AppointmentMultiStepForm extends FormBase
{
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
    protected $entityTypeManager;

  /**
   * Constructs a new AppointmentMultiStepForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
    public function __construct(EntityTypeManagerInterface $entity_type_manager)
    {
        $this->entityTypeManager = $entity_type_manager;
    }

  /**
   * {@inheritdoc}
   */
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('entity_type.manager')
        );
    }

  /**
   * {@inheritdoc}
   */
    public function getFormId()
    {
        return 'appointment_multistep_form';
    }

  /**
   * Main form builder callback.
   */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
      // Set or get current step.
        $step = $form_state->get('step') ?: 1;
        $form_state->set('step', $step);

      // Delegate to individual builder methods.
        switch ($step) {
            case 1:
                return $this->buildStepOne($form, $form_state);

            case 2:
                return $this->buildStepTwo($form, $form_state);

            case 3:
                return $this->buildStepThree($form, $form_state);

            case 4:
                return $this->buildStepFour($form, $form_state);

            case 5:
                return $this->buildStepFive($form, $form_state);
        }
    }

  /**
   * Helper: Render a step title.
   *
   * @param string $title
   *   The title text.
   *
   * @return array
   *   Render array for the title.
   */
    protected function buildStepTitle($title)
    {
        return [
        '#type' => 'markup',
        '#markup' => '<h2>' . $this->t($title) . '</h2>',
        ];
    }

  /**
   * Helper: Build actions buttons.
   *
   * @param array $buttons
   *   An associative array of buttons (keys like 'back', 'next', 'submit').
   *
   * @return array
   *   The render array for the actions.
   */
    protected function buildActions(array $buttons = [])
    {
        $actions = ['#type' => 'actions'];
        foreach ($buttons as $key => $button) {
            $actions[$key] = $button;
        }
        return $actions;
    }

  /**
   * Builds Step One: Agency selection.
   */
    protected function buildStepOne(array $form, FormStateInterface $form_state)
    {
        $form['step_title'] = $this->buildStepTitle('Step 1: Select Agency');

        $agencies = $this->entityTypeManager->getStorage('agency')->loadMultiple();
        $options = [];
        foreach ($agencies as $agency) {
            $options[$agency->id()] = $agency->label();
        }

        $form['agency'] = [
        '#type' => 'select',
        '#title' => $this->t('Select Agency'),
        '#options' => $options,
        '#required' => true,
        '#empty_option' => $this->t('- Select an agency -'),
        ];

        $form['actions'] = $this->buildActions([
        'next' => [
        '#type' => 'submit',
        '#value' => $this->t('Next'),
        '#submit' => ['::submitStepOne'],
        ],
        ]);

        return $form;
    }

  /**
   * Builds Step Two: Specialization selection.
   */
    protected function buildStepTwo(array $form, FormStateInterface $form_state)
    {
        $form['step_title'] = $this->buildStepTitle('Step 2: Select Specialization');

        $specializations = $this->entityTypeManager->getStorage('taxonomy_term')
        ->loadByProperties(['vid' => 'specializations']);
        $options = [];
        foreach ($specializations as $term) {
            $options[$term->id()] = $term->label();
        }
        $form['specialization'] = [
        '#type' => 'select',
        '#title' => $this->t('Select Specialization'),
        '#options' => $options,
        '#required' => true,
        '#empty_option' => $this->t('- Select specialization -'),
        ];

        $form['actions'] = $this->buildActions([
        'back' => [
        '#type' => 'submit',
        '#value' => $this->t('Back'),
        '#submit' => ['::backToPrevious'],
        '#limit_validation_errors' => [],
        ],
        'next' => [
        '#type' => 'submit',
        '#value' => $this->t('Next'),
        '#submit' => ['::submitStepTwo'],
        ],
        ]);

        return $form;
    }

  /**
   * Builds Step Three: Adviser selection and appointment datetime input.
   */
    protected function buildStepThree(array $form, FormStateInterface $form_state)
    {
        $form['step_title'] = $this->buildStepTitle('Step 3: Select Adviser');

        $agency_id = $form_state->get('agency');
        $specialization_id = $form_state->get('specialization');

      // Load advisers matching agency and specialization.
        $query = $this->entityTypeManager->getStorage('user')->getQuery()
        ->condition('status', 1)
        ->condition('roles', 'adviser')
        ->condition('field_agency', $agency_id)
        ->condition('field_specializations', $specialization_id)
        ->accessCheck(true);
        $adviser_ids = $query->execute();

        $advisers = $this->entityTypeManager->getStorage('user')->loadMultiple($adviser_ids);
        $options = [];
        foreach ($advisers as $adviser) {
            $options[$adviser->id()] = $adviser->getDisplayName();
        }

        $form['adviser'] = [
        '#type' => 'select',
        '#title' => $this->t('Select Adviser'),
        '#options' => $options,
        '#required' => true,
        '#empty_option' => $this->t('- Select adviser -'),
        ];

      // Date and time fields (simplified for this step; details refined in step 4).
        $form['date'] = [
        '#type' => 'date',
        '#title' => $this->t('Appointment Date'),
        '#required' => true,
        ];
        $form['time'] = [
        '#type' => 'time',
        '#title' => $this->t('Appointment Time'),
        '#required' => true,
        ];

        $form['actions'] = $this->buildActions([
        'back' => [
        '#type' => 'submit',
        '#value' => $this->t('Back'),
        '#submit' => ['::backToPrevious'],
        '#limit_validation_errors' => [],
        ],
        'next' => [
        '#type' => 'submit',
        '#value' => $this->t('Next'),
        '#submit' => ['::submitStepThree'],
        ],
        ]);

        return $form;
    }

  /**
   * Builds Step Four: Available time slot selection.
   */
    protected function buildStepFour(array $form, FormStateInterface $form_state)
    {
        $form['step_title'] = $this->buildStepTitle('Step 4: Select Date and Time');

        $adviser_id = $form_state->get('adviser');
        $adviser = $this->entityTypeManager->getStorage('user')->load($adviser_id);
        $form['date'] = [
        '#type' => 'date',
        '#title' => $this->t('Select Date'),
        '#required' => true,
        '#min' => date('Y-m-d'),
        '#max' => date('Y-m-d', strtotime('+30 days')),
        ];

      // Only process time slots if a date has been submitted.
        $selected_date = $form_state->getValue('date');
        if ($selected_date) {
          // The stored value is like "09:00", etc.
            $working_hours = [];
            foreach ($adviser->get('field_working_hours')->getValue() as $hour) {
              // Use "value" key (e.g. "09:00").
                $stored_time = $hour['value'];
              // Normalize: Remove the colon to form the key (e.g., "09:00" becomes "0900").
                $time_slot_key = str_replace(':', '', $stored_time);
                $working_hours[$time_slot_key] = $stored_time;
            }
            $query = $this->entityTypeManager->getStorage('appointment')->getQuery()
            ->condition('adviser', $adviser_id)
            ->accessCheck(false);
            $appointment_ids = $query->execute();

            $booked_slots = [];
            if (!empty($appointment_ids)) {
                $appointments = $this->entityTypeManager->getStorage('appointment')->loadMultiple($appointment_ids);
                foreach ($appointments as $appointment) {
                  // Extract the date part (first 10 characters) and compare with the selected date.
                    $appt_date = substr($appointment->get('appointment_date')->value, 0, 10);
                    if ($appt_date === $selected_date) {
                        // Pull the booked time slot (e.g., "0900").
                        $booked_slot = $appointment->get('time_slot')->value;
                        $booked_slots[] = $booked_slot;
                    }
                }
            }

          // Only show available time slots (exclude booked ones).
            $available_options = [];
            foreach ($working_hours as $key => $stored_time) {
                if (!in_array($key, $booked_slots)) {
                    $available_options[$key] = $stored_time;
                }
            }

          // Render the radio options only for available time slots.
            $form['time'] = [
            '#type' => 'radios',
            '#title' => $this->t('Available Time Slots'),
            '#options' => $available_options,
            '#required' => true,
            '#attributes' => ['class' => ['time-slots-grid']],
            ];
        }

      // Build Back and Next buttons.
        $form['actions'] = $this->buildActions([
        'back' => [
        '#type' => 'submit',
        '#value' => $this->t('Back'),
        '#submit' => ['::backToPrevious'],
        '#limit_validation_errors' => [],
        ],
        'next' => [
        '#type' => 'submit',
        '#value' => $this->t('Next'),
        '#submit' => ['::submitStepFour'],
        ],
        ]);

        return $form;
    }

  /**
   * Builds Step Five: Personal Information.
   */
    protected function buildStepFive(array $form, FormStateInterface $form_state)
    {
        $form['step_title'] = $this->buildStepTitle('Step 5: Personal Information');

        $form['name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Your Name'),
        '#required' => true,
        ];
        $form['email'] = [
        '#type' => 'email',
        '#title' => $this->t('Email'),
        '#required' => true,
        ];
        $form['phone'] = [
        '#type' => 'tel',
        '#title' => $this->t('Phone'),
        '#required' => true,
        ];
        $form['notes'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Notes'),
        ];

        $form['actions'] = $this->buildActions([
        'back' => [
        '#type' => 'submit',
        '#value' => $this->t('Back'),
        '#submit' => ['::backToPrevious'],
        '#limit_validation_errors' => [],
        ],
        'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Book Appointment'),
        ],
        ]);

        return $form;
    }
    protected function buildStepSix(array $form, FormStateInterface $form_state)
    {
        $form['step_title'] = $this->buildStepTitle('Step 6: Confirmation');
        //Retrieve the values from the form_state
        $agency_id = $form_state->get('agency');
        $specialization_id = $form_state->get('specialization');
        $advisor_id = $form_state->get('advisor');
        $date = $form_state->get('date');
        $time = $form_state->get('time');
        $name = $form_state->get('name');
        $email = $form_state->get('email');
        $phone = $form_state->get('phone');
        $notes = $form_state->get('notes');
        //Load the agency, specialization, and advisor entities
        $agency = $this->entityTypeManager->getStorage('agency')->load($agency_id);
        $adviser = $this->entityTypeManager->getStorage('user')->load($advisor_id);
        $specialization = $this->entityTypeManager->getStorage('taxonomy_term')->load($specialization_id);
        // Format date and time
        $date_time = \DateTime::createFromFormat('Y-m-d H:i', "$date $time");
        $formatter = \Drupal::service('date.formatter');
        $formatted_date = $formatter->format($date_time, 'custom', 'Y-m-d H:i');

        // Build markup for each detail
        $form['details'] = [
            '#type' => 'item',
            '#markup' => '<div class="confirmation-details">' .
                '<h3>' . $this->t('Your Appointment Details') . '</h3>' .
                '<p><strong>Agency:</strong> ' . $agency->label() . '</p>' .
                '<p><strong>Adviser:</strong> ' . $adviser->getDisplayName() . '</p>' .
                '<p><strong>Date and Time:</strong> ' . $formatted_date . '</p>' .
                '<p><strong>Specialization:</strong> ' . $specialization->label() . '</p>' .
                '<p><strong>Name:</strong> ' . $name . '</p>' .
                '<p><strong>Email:</strong> ' . $email . '</p>' .
                '<p><strong>Phone:</strong> ' . $phone . '</p>' .
                '<p><strong>Notes:</strong> ' . $notes . '</p>' .
            '</div>',
        ];

        // Add confirmation buttons
        $form['actions'] = $this->buildActions([
            'back' => [
                '#type' => 'submit',
                '#value' => $this->t('Back'),
                '#submit' => ['::backToPrevious'],
                '#limit_validation_errors' => [],
            ],
            'confirm' => [
                '#type' => 'submit',
                '#value' => $this->t('Confirm Appointment'),
                '#submit' => ['::submitStepSix'],
            ],
        ]);

        return $form;
    }
  /**
   * Submission handler for Step One.
   */
    public function submitStepOne(array &$form, FormStateInterface $form_state)
    {
        $form_state->set('agency', $form_state->getValue('agency'));
        $form_state->set('step', 2);
        $form_state->setRebuild(true);
    }

  /**
   * Submission handler for Step Two.
   */
    public function submitStepTwo(array &$form, FormStateInterface $form_state)
    {
        $form_state->set('specialization', $form_state->getValue('specialization'));
        $form_state->set('step', 3);
        $form_state->setRebuild(true);
    }

  /**
   * Submission handler for Step Three.
   */
    public function submitStepThree(array &$form, FormStateInterface $form_state)
    {
        $form_state->set('adviser', $form_state->getValue('adviser'));
      // Also store date/time chosen in this step.
        $form_state->set('date', $form_state->getValue('date'));
        $form_state->set('time', $form_state->getValue('time'));
        $form_state->set('step', 4);
        $form_state->setRebuild(true);
    }

  /**
   * Submission handler for Step Four.
   */
    public function submitStepFour(array &$form, FormStateInterface $form_state)
    {
      // Overwrite with new values in step four if different.
        $form_state->set('date', $form_state->getValue('date'));
        $form_state->set('time', $form_state->getValue('time'));
        $form_state->set('step', 5);
        $form_state->setRebuild(true);
    }

  /**
   * Handler for the "Back" button.
   */
    public function backToPrevious(array &$form, FormStateInterface $form_state)
    {
        $current_step = $form_state->get('step');
        $form_state->set('step', $current_step - 1);
        $form_state->setRebuild(true);
    }

  /**
   * Final form submit handler.
   */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        try {
          // Retrieve date value.
            $date = $form_state->getValue('date');
            if (empty($date)) {
                $date = $form_state->get('date');
            }
            if (empty($date)) {
                throw new \Exception('Date value is missing.');
            }

          // Retrieve time value.
            $time = $form_state->getValue('time');
            if (empty($time)) {
                $time = $form_state->get('time');
            }
            if (empty($time)) {
                throw new \Exception('Time value is missing.');
            }

          // Normalize time: If it's "0900", convert to "09:00".
            $time = (string) $time;
            if (strlen($time) == 4 && strpos($time, ':') === false) {
                $time = substr($time, 0, 2) . ':' . substr($time, 2, 2);
            }

          // Create a DateTime object.
            $date_time = \DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
            if (!$date_time) {
                throw new \Exception('Could not parse date/time combination.');
            }
          // Format datetime in ISO8601 format: e.g., "2025-03-20T09:00:00"
            $formatted_datetime = $date_time->format('Y-m-d\TH:i:s');

          // Create the appointment entity.
            $appointment = $this->entityTypeManager->getStorage('appointment')->create([
            'agency'            => $form_state->get('agency'),
            'adviser'           => $form_state->get('adviser'),
            'title'             => 'Appointment for ' . $form_state->getValue('name'),
            'customer_name'     => $form_state->getValue('name'),
            'customer_email'    => $form_state->getValue('email'),
            'customer_phone'    => $form_state->getValue('phone'),
            'appointment_date'  => $formatted_datetime,
            'time_slot'         => str_replace(':', '', $time),
            'notes'             => $form_state->getValue('notes'),
            'status'            => 'pending',
            ]);
            $appointment->save();
            $appointment_id = $appointment->id();
            if (!$appointment_id) {
                  throw new \Exception('Appointment entity saved without an ID.');
            }

          // Prepare mail parameters.
            $params = [
            'appointment_id'   => $appointment_id,
            'agency'           => $form_state->get('agency'),
            'adviser'          => $form_state->get('adviser'),
            'appointment_date' => $formatted_datetime,
            'time_slot'        => str_replace(':', '', $time),
            'customer_name'    => $form_state->getValue('name'),
            'customer_email'   => $form_state->getValue('email'),
            'customer_phone'   => $form_state->getValue('phone'),
            'notes'            => $form_state->getValue('notes'),
            ];
            $mail_manager = \Drupal::service('plugin.manager.mail');
            $module = 'appointment';
            $langcode = \Drupal::currentUser()->getPreferredLangcode();

          // Send email to the advisor.
            $advisor = $this->entityTypeManager->getStorage('user')->load($form_state->get('adviser'));
            $advisor_email = $advisor ? $advisor->getEmail() : '';
            if (!empty($advisor_email)) {
                $params['subject'] = 'New Appointment Booked';
                $result = $mail_manager->mail($module, 'new_appointment_advisor', $advisor_email, $langcode, $params, null, true);
                if ($result['result'] !== true) {
                    \Drupal::logger('appointment')->error('Failed to send email to advisor.');
                }
            }

          // Send email to the customer.
            $user_email = $form_state->getValue('email');
            if (!empty($user_email)) {
                $params['subject'] = 'Your Appointment is Confirmed';
                $result = $mail_manager->mail($module, 'new_appointment_user', $user_email, $langcode, $params, null, true);
                if ($result['result'] !== true) {
                    \Drupal::logger('appointment')->error('Failed to send email to customer.');
                }
            }

            $this->messenger()->addMessage($this->t('Your appointment has been booked successfully.'));
            $form_state->setRedirect('entity.appointment.canonical', ['appointment' => $appointment_id]);
        } catch (\Exception $e) {
            $this->messenger()->addError($this->t('There was a problem booking your appointment. Please try again.'));
            \Drupal::logger('appointment')->error($e->getMessage());
        }
    }
}
