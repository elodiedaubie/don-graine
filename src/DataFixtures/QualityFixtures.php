<?php

namespace App\DataFixtures;

use App\Entity\Quality;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class QualityFixtures extends Fixture
{
    //this constant is meant to be uses in fixtures classes
    //to add a new quality, just add it on the const, it will be load in other fixtures

    public const QUALITYFIXTURES = [
        'Bio',
        'Reproductible',
        'Rare',
        'Paysanne'
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::QUALITYFIXTURES as $key => $name) {
            $quality = new Quality();
            $quality->setName($name);
            $manager->persist($quality);
            $this->addReference('quality_' . $key, $quality);
        }
        $manager->flush();
    }
}
