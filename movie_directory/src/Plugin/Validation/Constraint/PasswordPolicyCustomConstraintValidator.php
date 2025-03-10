<?php

namespace Drupal\movie_directory\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\password_policy\PasswordPolicyValidator;

class PasswordPolicyCustomConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface
{
  /**
   * The password policy validator.
   *
   * @var \Drupal\password_policy\PasswordPolicyValidator
   */
    protected $passwordPolicyValidator;

  /**
   * Constructs a new PasswordPolicyCustomConstraintValidator.
   *
   * @param \Drupal\password_policy\PasswordPolicyValidator $password_policy_validator
   *   The password policy validator.
   */
    public function __construct(PasswordPolicyValidator $password_policy_validator)
    {
        $this->passwordPolicyValidator = $password_policy_validator;
    }

  /**
   * {@inheritdoc}
   */
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('password_policy.validator')
        );
    }

  /**
   * {@inheritdoc}
   */
    public function validate($value, Constraint $constraint)
    {
      // If no value is provided, skip validation
        if (empty($value)) {
            return;
        }
        $user = $this->context->getRoot()->getEntity();
      // Validate using the password policy validator
        $validation_result = $this->passwordPolicyValidator->validatePassword($value->getString(), $user);

      // If validation fails, add a violation
        if (!$validation_result) {
            $this->context->addViolation($constraint->message);
        }
    }
}
