<?php

namespace App\Form;

use App\Repository\InputListRepository;
use App\Validator\CanBeScrabbled;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WordSubmissionFormType extends AbstractType
{
    public function __construct(
        private InputListRepository $inputListRepository
    )
    {}
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $scrabbleInput = $this->inputListRepository->findOneBy([], ['id' => 'DESC'])->getInput();

        $builder
            ->add('word', null, [
                'label' => 'Tapes ton mot ici',
                'required' => true,
                'constraints' => [
                    new CanBeScrabbled(['input' => $scrabbleInput])
                ]
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
