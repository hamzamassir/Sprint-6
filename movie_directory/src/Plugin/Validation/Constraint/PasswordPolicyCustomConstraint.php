<?php

namespace Drupal\movie_directory\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Custom password policy constraint.
 *
 * @Constraint(
 *   id = "PasswordPolicyCustomConstraint",
 *   label = @Translation("Custom Password Policy Constraint")
 * )
 */
class PasswordPolicyCustomConstraint extends Constraint
{
    public $message = 'The password does not satisfy the password policies.';
}
