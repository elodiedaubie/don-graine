<?php

namespace App\DataFixtures;

use App\Entity\Donation;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class DonationFixtures extends Fixture implements DependentFixtureInterface
{
    //this constant is meant to be uses in fixtures classes
    //This constant handle numbers of owners, beneficiaries and not available seed Batches
    public const DONATIONSNUMBER = 20;

    public function load(ObjectManager $manager): void
    {
        //set only 'En cours' ou 'Finalisé' Batches
        for ($i = 0; $i < self::DONATIONSNUMBER; $i++) {
            $donation = new Donation();
            $donation->setCreatedAt(new DateTimeImmutable());
            $donation->setStatus(Donation::STATUS[rand(0, count(Donation::STATUS) - 2)]);
            $donation->setSeedBatch($this->getReference('unavailable_batch_' . $i));
            $donation->setBeneficiary($this->getReference('beneficiary_' . rand(0, self::DONATIONSNUMBER - 1)));
            $manager->persist($donation);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            SeedBatchFixtures::class,
            UserFixtures::class
        ];
    }
}
