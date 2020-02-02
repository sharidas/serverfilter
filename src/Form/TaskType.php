<?php
/**
 * @author Sujith Haridasan <sujith.h@gmail.com>
 *
 * @copyright Copyright (c) 2020
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 */
namespace App\Form;

use App\Entity\FormData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    /**
     * Build the form with the following
     * - A drop down of storage
     * - A checkbox for RAM
     * - A drop down for hard disk
     * - A drop down for location
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $ramStorage = [
            '2GB' => '2GB',
            '4GB' => '4GB',
            '8GB' => '8GB',
            '12GB' => '12GB',
            '16GB' => '16GB',
            '24GB' => '24GB',
            '32GB' => '32GB',
            '48GB' => '48GB', '64GB' => '64GB', '96GB' => '96GB'];
        $builder
            ->add('storage', ChoiceType::class, [
                'choices' => [
                    '0' => '0',
                    '250GB' => '250GB',
                    '500GB' => '500GB',
                    '1TB' => '1TB',
                    '2TB' => '2TB',
                    '3TB' => '3TB',
                    '4TB' => '4TB',
                    '8TB' => '8TB',
                    '12TB' => '12TB',
                    '24TB' => '24TB',
                    '48TB' => '48TB',
                    '72TB' => '72TB',
                ]
            ])
            ->add('ram', ChoiceType::class, [
                'choices' => $ramStorage,
                'multiple' => true,
                'expanded' => true
            ])
            ->add('hdisk', ChoiceType::class, [
                'choices' => [
                    'SAS' => 'SAS',
                    'SATA' => 'SATA',
                    'SATA2' => 'SATA2',
                    'SSD' => 'SSD',
                ]
            ])
            ->add('location', ChoiceType::class, [
                'choices' => [
                    'AmsterdamAMS-01' => 'AmsterdamAMS-01',
                    'Washington D.C.WDC-01' => 'Washington D.C.WDC-01',
                    'San FranciscoSFO-12' => 'San FranciscoSFO-12',
                    'SingaporeSIN-11' => 'SingaporeSIN-11',
                    'DallasDAL-10' => 'DallasDAL-10'
                ]
            ])
            ->add('uploadFile', FileType::class, [
                'label' => 'Upload file'
            ])
            ->add('data', CollectionType::class);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FormData::class
        ]);
    }
}
