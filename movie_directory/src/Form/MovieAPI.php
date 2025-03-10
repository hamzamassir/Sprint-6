<?php

namespace Drupal\movie_directory\Form;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class MovieAPI extends FormBase
{
    public const MOVIE_API_CONFIG_PAGE = 'movie_api_config_page:values';
    /**
    * {@inheritdoc}
    */
    public function getFormId()
    {
        return 'movie_api_config_page';
    }

    /**
    * {@inheritdoc}
    */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $values = Drupal::state()->get(self::MOVIE_API_CONFIG_PAGE);
        $form = [];
        $form['api_base_url'] = [
            '#type' => 'textfield',
            '#title' => $this->t('API base URL'),
            '#description' => $this->t('Enter the base URL for the movie API'),
            '#required' => true,
            '#default_value' => $values['api_base_url'] ?? '',
        ];
        $form['api_key'] = [
            '#type' => 'textfield',
            '#title' => $this->t('API key (v3 auth)'),
            '#description' => $this->t('Enter the API key for the movie API'),
            '#required' => true,
            '#default_value' => $values['api_key'] ?? '',
        ];
        $form['actions']['#type'] = 'actions';
        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Save configuration'),
            '#button_type' => 'primary',
        ];
        return $form;
    }

    /**
    * {@inheritdoc}
    */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $submit_values = $form_state->cleanValues()->getValues();

        Drupal::state()->set(self::MOVIE_API_CONFIG_PAGE, $submit_values);

        $messenger = Drupal::service('messenger');
        $messenger->addMessage($this->t('Movie API configuration saved successfully.'));
    }
}
