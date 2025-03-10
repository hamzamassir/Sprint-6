<?php

namespace Drupal\anytown\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * @Block(
 *   id = "anytown_selected_node_block",
 *   admin_label = @Translation("Selected Node Display"),
 *   category = @Translation("Anytown")
 * )
 */
class SelectedNodeBlock extends BlockBase
{
    public function build()
    {
        $nid = \Drupal::state()->get('anytown.selected_node');

        if (!$nid) {
            return [
            '#markup' => $this->t('No node selected yet.'),
            ];
        }

        $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
        if (!$node) {
            return [
            '#markup' => $this->t('Selected node not found.'),
            ];
        }

      // Get other nodes of same type with explicit access check
        $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery()
        ->condition('type', $node->bundle())
        ->condition('nid', $nid, '<>')
        ->condition('status', 1)
        ->sort('title')
        ->accessCheck(true); // Add explicit access checking

        $nids = $query->execute();
        $related_nodes = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->loadMultiple($nids);

        $related_titles = [];
        foreach ($related_nodes as $related_node) {
            $related_titles[] = $related_node->getTitle();
        }

        return [
        '#theme' => 'anytown_selected_node',
        '#selected_title' => $node->getTitle(),
        '#related_titles' => $related_titles,
        ];
    }
}
