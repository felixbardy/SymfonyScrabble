<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;


/**
 * @Annotation
 */
class CanBeScrabbled extends Constraint
{
    public $message = 'The string {{ string }} cannot be formed out of the current Scrabble input.';
    public $input;

    public function getRequiredOptions()
    {
        return ['input'];
    }
}