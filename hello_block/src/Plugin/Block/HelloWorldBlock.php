<?php

declare(strict_types=1);

namespace Drupal\hello_block\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
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
     * Summary of __construct
     * @param array $configuration
     * @param mixed $plugin_id
     * @param mixed $plugin_definition
     * @param \Drupal\Core\Session\AccountProxy $currentUser
     */
    public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountProxy $currentUser)
    {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
        $this->currentUser = $currentUser;
    }
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
    {
        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->get('current_user')
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
            'max-age' => 0,
          ];
        return $build;
    }
}
