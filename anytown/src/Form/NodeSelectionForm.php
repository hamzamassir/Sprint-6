<?php

namespace Drupal\anytown\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class NodeSelectionForm extends FormBase
{
    public function getFormId()
    {
        return 'anytown_node_selection_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        // Get all published nodes of type 'article'
        $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery()
        ->condition('status', 1)
        //->condition('type', 'news')
        ->sort('title')
        ->accessCheck(true); // Add explicit access checking
        $nids = $query->execute();
        $options = [];
        if (!empty($nids)) {
            $nodes = \Drupal::entityTypeManager()
                ->getStorage('node')
                ->loadMultiple($nids);
            foreach ($nodes as $node) {
                $options[$node->id()] = $node->getTitle();
            }
        }

        $form['node_reference'] = [
        '#type' => 'select',
        '#title' => $this->t('Select a Node'),
        '#options' => $options,
        '#empty_option' => $this->t('- Select -'),
        '#required' => true,
        ];

        $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
        ];

        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        \Drupal::state()->set('anytown.selected_node', $form_state->getValue('node_reference'));
        $this->messenger()->addMessage($this->t('Node selection saved.'));
    }
}
