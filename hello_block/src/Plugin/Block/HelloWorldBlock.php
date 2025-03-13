<?php

declare(strict_types=1);

namespace Drupal\hello_block\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a hello world block.
 */
#[Block(
    id: 'anytown_hello_world',
    admin_label: new TranslatableMarkup('Hello World'),
    category: new TranslatableMarkup('Custom')
)]
class HelloWorldBlock extends BlockBase implements ContainerFactoryPluginInterface
{
    /**
     * @var \Drupal\Core\Session\AccountProxy
     */
    private $currentUser;
    /**
     * Summary of entityTypeManager
     * @var /Drupal\Core\Entity\EntityTypeManagerInterface
     */
    private $entityTypeManager;
    /**
     * Summary of __construct
     * @param array $configuration
     * @param mixed $plugin_id
     * @param mixed $plugin_definition
     * @param \Drupal\Core\Session\AccountProxy $currentUser
     * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
     */
    public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountProxy $currentUser, EntityTypeManagerInterface $entityTypeManager)
    {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
        $this->currentUser = $currentUser;
        $this->entityTypeManager = $entityTypeManager;
    }
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
    {
        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->get('current_user'),
            $container->get('entity_type.manager'),
        );
    }
    /**
    * {@inheritdoc}
    */
    public function build(): array
    {
        $build['content'] = [
            '#markup' => $this->currentUser->isAuthenticated()
            ? $this->t('Hello, @name! Welcome back.', ['@name' => $this->currentUser->getDisplayName()])
            : $this->t('Hello world! please login to see your name.'),
        ];
        $build['content']['#cache'] = [
            // We're creating markup that depends on the current user. So we need
            // to tell Drupal to use the 'user' cache context. This will ensure that
            // the block content will vary per-user. Additionally, since we're adding
            // the user's name to the markup we add a cache tag for the current user.
            // This will ensure that if the user edits their account and changes their
            // name that the block will be updated.
            'contexts' => ['user'],
            'tags' => $this->entityTypeManager->getStorage('user')->load($this->currentUser->id())->getCacheTags(),
          ];
        return $build;
    }
}
