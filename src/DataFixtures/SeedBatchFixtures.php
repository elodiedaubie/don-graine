<?php

namespace App\DataFixtures;

use App\Entity\SeedBatch;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SeedBatchFixtures extends Fixture implements DependentFixtureInterface
{
    //thoses constants are meant to be use in fixtures classes only
    //if AVAILABLEBATCHES can be freely change. Repartition on OWNERS is random.
    public const AVAILABLEBATCHES = 30;
    //UNAVAILABLEBATCHES must not be change.
    public const UNAVAILABLEBATCHES = DonationFixtures::DONATIONSNUMBER;

    public function load(ObjectManager $manager): void
    {
        //load 10 availables batches with random seed and random owner
        for ($i = 0; $i < self::AVAILABLEBATCHES; $i++) {
            $batch = new SeedBatch();
            $batch->setSeedQuantity((rand(SeedBatch::MINSEEDS, SeedBatch::MAXSEEDS)));
            $batch->setPlant(
                $this->getReference('plant_' . (rand(0, count(PlantFixtures::PLANTS) - 1)))
            );
            $batch->setOwner(
                $this->getReference('newcomer_' . (rand(0, UserFixtures::NEWCOMERS - 1)))
            );
            $batch->setQuality(
                $this->getReference('quality_' . (rand(0, count(QualityFixtures::QUALITIES) - 1)))
            );
            //each batch is added in 3 differents owners favorite List
            $batch->addFavoriteOwner(
                $this->getReference('owner_' . (rand(0, DonationFixtures::DONATIONSNUMBER - 1)))
            );
            $batch->addFavoriteOwner(
                $this->getReference('beneficiary_' . (rand(0, DonationFixtures::DONATIONSNUMBER - 1)))
            );
            $batch->addFavoriteOwner(
                $this->getReference('newcomer_' . (rand(0, UserFixtures::NEWCOMERS - 1)))
            );
            $manager->persist(($batch));
        }
        //load 10 unavailable batches with random seed and random owner
        for ($i = 0; $i < self::UNAVAILABLEBATCHES; $i++) {
            $batch = new SeedBatch();
            $batch->setSeedQuantity((rand(SeedBatch::MINSEEDS, SeedBatch::MAXSEEDS)));
            $batch->setPlant(
                $this->getReference('plant_' . (rand(0, count(PlantFixtures::PLANTS) - 1)))
            );
            $batch->setOwner(
                $this->getReference('owner_' . (rand(0, DonationFixtures::DONATIONSNUMBER - 1)))
            );
            $batch->setQuality(
                $this->getReference('quality_' . (rand(0, count(QualityFixtures::QUALITIES) - 1)))
            );
            $batch->setIsAvailable(false);
            $manager->persist(($batch));
            $this->addReference('unavailable_batch_' . $i, $batch);
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
