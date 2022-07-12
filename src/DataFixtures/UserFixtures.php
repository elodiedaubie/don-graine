<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    //this constant is meant to be use in fixtures classes only
    //changing NEWCOMERS number will only increase numbers of users without any added batches
    public const NEWCOMERS = 20;

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        //users who own batches but didn't received any
        for ($i = 0; $i < DonationFixtures::DONATIONSNUMBER; $i++) {
            $user = new User();
            $user->setEmail('jane' . $i . '@mail.com');
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                'testpaSSword!' . $i
            );
            $user->setPassword($hashedPassword);
            $user->setUsername('jane' . $i);
            $user->setCreatedAt(new DateTimeImmutable());
            $user->isVerified(true);
            $manager->persist($user);
            $this->addReference('owner_' . $i, $user);
        }

        //users who received donation but didn't give any
        for ($i = 0; $i < DonationFixtures::DONATIONSNUMBER; $i++) {
            $user = new User();
            $user->setEmail('john' . $i . '@mail.com');
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                'testpaSSword!' . $i
            );
            $user->setPassword($hashedPassword);
            $user->setUsername('john' . $i);
            $user->setCreatedAt(new DateTimeImmutable());
            $user->isVerified(true);
            $manager->persist($user);
            $this->addReference('beneficiary_' . $i, $user);
        }

        //users who did not receive or give a donation
        for ($i = 0; $i < self::NEWCOMERS; $i++) {
            $user = new User();
            $user->setEmail('sam' . $i . '@mail.com');
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                'testpaSSword!' . $i
            );
            $user->setPassword($hashedPassword);
            $user->setUsername('sam' . $i);
            $user->setCreatedAt(new DateTimeImmutable());
            $user->isVerified(true);
            $manager->persist($user);
            $this->addReference('newcomer_' . $i, $user);
        }
        //insert all users in DB at the same time

        $manager->flush();
    }
}
