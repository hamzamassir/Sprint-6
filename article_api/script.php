<?php

use Drupal\node\Entity\Node;
use Drupal\Core\Database\Database;

// Bootstrap Drupal (if needed)
$autoloader = require_once 'autoload.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Specify the node ID to delete and recreate
$nid = 10;

try {
    // Get the entity type manager
    $entity_type_manager = \Drupal::entityTypeManager();
    $node_storage = $entity_type_manager->getStorage('node');

    // Check if node exists
    $existing_node = $node_storage->load($nid);

    // Delete the node if it exists
    if ($existing_node) {
        try {
            $existing_node->delete();
            echo "Deleted node with ID $nid\n";
        } catch (\Exception $e) {
            echo "Error deleting node: " . $e->getMessage() . "\n";
        }
    } else {
        echo "No existing node found with ID $nid\n";
    }

    // Create a new node
    try {
        $node = Node::create([
            'type' => 'article',
            'title' => 'Article with ID ' . $nid,
            'nid' => $nid, // Set the specific node ID
            'uid' => 1, // Author ID (usually admin is 1)
            'status' => 1, // Published
            'body' => [
                'value' => 'This is the body of the article with ID ' . $nid,
                'format' => 'basic_html',
            ],
        ]);

        // Save the node
        $node->save();
        echo "Recreated article with ID $nid\n";
    } catch (\Exception $e) {
        echo "Error creating node: " . $e->getMessage() . "\n";
    }

    // Additional debugging: check if node was actually created
    $check_node = $node_storage->load($nid);
    if ($check_node) {
        echo "Verification: Node $nid exists after recreation\n";
    } else {
        echo "Verification: Node $nid was NOT created\n";
    }

} catch (\Exception $e) {
    echo "Unexpected error: " . $e->getMessage() . "\n";
}

// Debugging: Database connection and node type
try {
    $connection = Database::getConnection();
    $node_type_storage = \Drupal::entityTypeManager()->getStorage('node_type');
    $article_type = $node_type_storage->load('article');

    if ($article_type) {
        echo "Article content type exists\n";
    } else {
        echo "Article content type does not exist\n";
    }
} catch (\Exception $e) {
    echo "Error checking content type: " . $e->getMessage() . "\n";
}