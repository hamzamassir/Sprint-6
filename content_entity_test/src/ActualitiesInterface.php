<?php

declare(strict_types=1);

namespace Drupal\content_entity_test;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining an actualities entity type.
 */
interface ActualitiesInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
