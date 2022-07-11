<?php

namespace App\DataFixtures;

use App\Entity\Purpose;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PurposeFixtures extends Fixture
{
    //this constant is meant to be uses in fixtures classes
    //to add a new purpose, just add it on the const, it will be load in other fixtures
    public const PURPOSESFIXTURES = [
       ['Potagère', 'picto-vegetable.png'],
       ['Mellifère', 'picto-melliferous.png'],
       ['Décorative', 'picto-decorative.png']
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::PURPOSESFIXTURES as $key => $infos) {
            $purpose = new Purpose();
            $purpose->setName($infos[0]);
            $purpose->setPicto($infos[1]);
            $manager->persist(($purpose));
            $this->addReference('purpose_' . $key, $purpose);
        }
        $manager->flush();
    }
}
