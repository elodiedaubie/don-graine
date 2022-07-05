<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public const FAKEUSERS = 20;

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < self::FAKEUSERS; $i++) {
            $user = new User();
            $user->setEmail('jane' . $i . '@mail.com');
            $user->setPassword('testpaSSword!' . $i);
            $user->setUsername('jane' . $i);
            $user->setCreatedAt(new DateTimeImmutable());
            $user->isVerified(true);
            $manager->persist($user);
        }
        //insert all users in DB at the same time
        $manager->flush();
    }
}
