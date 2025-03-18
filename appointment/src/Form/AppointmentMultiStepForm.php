<?php

namespace Drupal\appointment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\menu_test\Access\AccessCheck;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a multi-step form for booking appointments.
 */
class AppointmentMultiStepForm extends FormBase
{
    protected $entityTypeManager;

    public function __construct(EntityTypeManagerInterface $entity_type_manager)
    {
        $this->entityTypeManager = $entity_type_manager;
    }

    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('entity_type.manager')
        );
    }

    public function getFormId()
    {
        return 'appointment_multistep_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $step = $form_state->get('step') ?: 1;
        $form_state->set('step', $step);

        switch ($step) {
            case 1:
                return $this->buildStepOne($form, $form_state);
            case 2:
                return $this->buildStepTwo($form, $form_state);
            case 3:
                return $this->buildStepThree($form, $form_state);
            case 4:
                return $this->buildStepFour($form, $form_state);
        }
    }

    protected function buildStepOne(array $form, FormStateInterface $form_state)
    {
        $form['step_title'] = [
        '#type' => 'markup',
        '#markup' => '<h2>' . $this->t('Step 1: Select Agency') . '</h2>',
        ];

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

        $form['actions'] = [
        '#type' => 'actions',
        ];

        $form['actions']['next'] = [
        '#type' => 'submit',
        '#value' => $this->t('Next'),
        '#submit' => ['::submitStepOne'],
        ];

        return $form;
    }

    protected function buildStepTwo(array $form, FormStateInterface $form_state)
    {
        $form['step_title'] = [
        '#type' => 'markup',
        '#markup' => '<h2>' . $this->t('Step 2: Select Specialization') . '</h2>',
        ];

      // Load specializations
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

        $form['actions'] = [
        '#type' => 'actions',
        ];

        $form['actions']['back'] = [
        '#type' => 'submit',
        '#value' => $this->t('Back'),
        '#submit' => ['::backToPrevious'],
        '#limit_validation_errors' => [],
        ];

        $form['actions']['next'] = [
        '#type' => 'submit',
        '#value' => $this->t('Next'),
        '#submit' => ['::submitStepTwo'],
        ];

        return $form;
    }

    protected function buildStepThree(array $form, FormStateInterface $form_state)
    {
        $form['step_title'] = [
        '#type' => 'markup',
        '#markup' => '<h2>' . $this->t('Step 3: Select Adviser') . '</h2>',
        ];

      // Load advisers based on agency and specialization
        $agency_id = $form_state->get('agency');
        $specialization_id = $form_state->get('specialization');

        $query = $this->entityTypeManager->getStorage('user')->getQuery()
        ->condition('status', 1)
        ->condition('roles', 'adviser')
        ->condition('field_agency', $agency_id)
        ->condition('field_specializations', $specialization_id)
        ->AccessCheck(true);

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

        $form['actions'] = [
        '#type' => 'actions',
        ];

        $form['actions']['back'] = [
        '#type' => 'submit',
        '#value' => $this->t('Back'),
        '#submit' => ['::backToPrevious'],
        '#limit_validation_errors' => [],
        ];

        $form['actions']['next'] = [
        '#type' => 'submit',
        '#value' => $this->t('Next'),
        '#submit' => ['::submitStepThree'],
        ];

        return $form;
    }

    protected function buildStepFour(array $form, FormStateInterface $form_state)
    {
        $form['step_title'] = [
        '#type' => 'markup',
        '#markup' => '<h2>' . $this->t('Step 4: Personal Information') . '</h2>',
        ];

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

        $form['actions'] = [
        '#type' => 'actions',
        ];

        $form['actions']['back'] = [
        '#type' => 'submit',
        '#value' => $this->t('Back'),
        '#submit' => ['::backToPrevious'],
        '#limit_validation_errors' => [],
        ];

        $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Book Appointment'),
        ];

        return $form;
    }

    public function submitStepOne(array &$form, FormStateInterface $form_state)
    {
        $form_state->set('agency', $form_state->getValue('agency'));
        $form_state->set('step', 2);
        $form_state->setRebuild(true);
    }

    public function submitStepTwo(array &$form, FormStateInterface $form_state)
    {
        $form_state->set('specialization', $form_state->getValue('specialization'));
        $form_state->set('step', 3);
        $form_state->setRebuild(true);
    }

    public function submitStepThree(array &$form, FormStateInterface $form_state)
    {
        $form_state->set('adviser', $form_state->getValue('adviser'));
        $form_state->set('date', $form_state->getValue('date'));
        $form_state->set('time', $form_state->getValue('time'));
        $form_state->set('step', 4);
        $form_state->setRebuild(true);
    }

    public function backToPrevious(array &$form, FormStateInterface $form_state)
    {
        $current_step = $form_state->get('step');
        $form_state->set('step', $current_step - 1);
        $form_state->setRebuild(true);
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        try {
            $appointment = $this->entityTypeManager->getStorage('appointment')->create([
            'agency' => $form_state->get('agency'),
            'adviser' => $form_state->get('adviser'),
            'title' => 'Appointment for ' . $form_state->getValue('name'),
            'customer_name' => $form_state->getValue('name'),
            'customer_email' => $form_state->getValue('email'),
            'customer_phone' => $form_state->getValue('phone'),
            'appointment_date' => $form_state->get('date') . ' ' . $form_state->get('time'),
            'notes' => $form_state->getValue('notes'),
            'status' => 'pending',
            ]);

            $appointment->save();

            $this->messenger()->addMessage($this->t('Your appointment has been booked successfully.'));
            $form_state->setRedirect('entity.appointment.canonical', ['appointment' => $appointment->id()]);
        } catch (\Exception $e) {
            $this->messenger()->addError($this->t('There was a problem booking your appointment. Please try again.'));
            \Drupal::logger('appointment')->error($e->getMessage());
        }
    }
}
