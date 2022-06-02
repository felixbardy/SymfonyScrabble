<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;


/**
 * @Annotation
 */
class CanBeScrabbled extends Constraint
{
    public $message = 'Le mot "{{ string }}" ne peut pas être formé par les lettres disponibles!';
    public $input;

    public function getRequiredOptions()
    {
        return ['input'];
    }
}