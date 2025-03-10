<?php

declare(strict_types=1);

namespace Drupal\anytown\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Anytown settings for this site.
 */
final class SettingsForm extends ConfigFormBase
{
    /**
     * Name for module's configuration object.
     */
    public const SETTINGS = 'anytown.settings';

    /**
     * {@inheritdoc}
     */
    public function getFormId(): string
    {
        return self::SETTINGS;
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames(): array
    {
        return [self::SETTINGS];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state): array
    {
        $config = $this->config(self::SETTINGS);
        $form['forecast_settings'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Forecast Settings'),
          '#collapsible' => true,
          '#collapsed' => false,
        ];
        $form['forecast_settings']['display_forecast'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Display weather forecast'),
          '#default_value' => $config->get('display_forecast'),
        ];
        $form['forecast_settings']['location'] = [
          '#type' => 'textfield',
          '#title' => $this->t('ZIP code of market'),
          '#description' => $this->t('Used to determine weekend weather forecast.'),
          '#default_value' => $config->get('location'),
          '#placeholder' => '90210',
        ];
        $form['forecast_settings']['weather_closures'] = [
          '#type' => 'textarea',
          '#title' => $this->t('Weather-related closures'),
          '#description' => $this->t('List one closure per line.'),
          '#default_value' => $config->get('weather_closures'),
        ];
        return parent::buildForm($form, $form_state);
    }
    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state): void
    {
        parent::validateForm($form, $form_state);
        $location = $form_state->getValue('location');
        $value = filter_var($location, FILTER_VALIDATE_INT);
        if (!$value || strlen($location) !== 5) {
            $form_state->setErrorByName('location', $this->t('ZIP code is unvalid.'));
        }
    }
    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state): void
    {
        $this->config(self::SETTINGS)
        ->set('display_forecast', $form_state->getValue('display_forecast'))
        ->set('location', $form_state->getValue('location'))
        ->set('weather_closures', $form_state->getValue('weather_closures'))
        ->save();

        $this->messenger()->addMessage($this->t('Anytown settings have been saved.'));
    }
}
