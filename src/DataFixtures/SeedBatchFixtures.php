<?php

namespace App\DataFixtures;

use App\Entity\SeedBatch;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SeedBatchFixtures extends Fixture implements DependentFixtureInterface
{
    //this constant is meant to be use in fixtures classes only
    //to update number of batch, just change this constant used in other fixtures
    public const BATCHSNUMBERFIXTURES = 10;

    public function load(ObjectManager $manager): void
    {
        //load 10 batches with random seed and random owner
        for ($i = 0; $i < self::BATCHSNUMBERFIXTURES; $i++) {
            $batch = new SeedBatch();
            $batch->setSeedQuantity((rand(SeedBatch::MINSEEDSQUANTITY, SeedBatch::MAXSEEDSQUANTITY)));
            $batch->setPlant(
                $this->getReference('plant_' . (rand(0, count(PlantFixtures::PLANTSFIXTURES) - 1)))
            );
            $batch->setOwner(
                $this->getReference('user_' . (rand(0, UserFixtures::USERSNUMBERFIXTURES) - 1))
            );
            $batch->setQuality(
                $this->getReference('quality_' . (rand(0, count(QualityFixtures::QUALITYFIXTURES) - 1)))
            );
            $manager->persist(($batch));
            $this->addReference('batch_' . $i, $batch);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            PlantFixtures::class,
            UserFixtures::class,
            QualityFixtures::class
        ];
    }
}
