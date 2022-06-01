<?php

namespace App\Validator;

use App\GameGenerator;
use App\Repository\InputListRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class CanBeScrabbledValidator extends ConstraintValidator
{

    public function __construct(
        private GameGenerator $gameGenerator
    )
    {}

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CanBeScrabbled) {
            throw new UnexpectedTypeException($constraint, CanBeScrabbled::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) to take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }
        
        if (!$this->gameGenerator->isValid($value, $constraint->input)) {
            $this->context->buildViolation($constraint->message)
                 ->setParameter('{{ string }}', $value)
                 ->addViolation()
            ;
        }
    }
}