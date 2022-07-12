<?php

namespace App\DataFixtures;

use App\Entity\Plant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class PlantFixtures extends Fixture implements DependentFixtureInterface
{
    //this constant is meant to be uses in fixtures classes
    //to add a new plant, just add a value in the array, it will be load in other fixtures
    public const PLANTS = [
        'Brocoli',
        'Carotte',
        'Chou',
        'Radis',
        'Roquette',
        'Tomate',
        'Poivron',
        'Courgette',
        'Fraises',
        'Framboises',
        'Phacélie à feuilles de tanaisie',
        'Bourrache officinale',
        'Sainfoin cultivé',
        'Luzerne',
        'L\'ail des ours',
        'Tournesol',
        'Moutarde',
        'Vipérine commune',
        'Épilobe en épi',
        'Mélilot blanc',
        'Rosier',
        'Jasmin',
        'Lotus bleu',
        'Lierre de Boston',
        'Cycas du Japon',
        'Eurphobia Marginata',
        'Ipomée bleue',
        'Câprier Spinosa',
        'Passiflore Atala',
        'Abrus precatorius'
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::PLANTS as $key => $name) {
            $plant = new Plant();
            $plant->setName($name);
            $plant->setPurpose($this->getReference('purpose_' . rand(0, count(PurposeFixtures::PURPOSES) - 1)));
            $manager->persist(($plant));
            $this->addReference('plant_' . $key, $plant);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            PurposeFixtures::class,
        ];
    }
}
