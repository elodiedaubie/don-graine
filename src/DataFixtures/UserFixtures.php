<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const USERSNUMBERFIXTURES = 20;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < self::USERSNUMBERFIXTURES; $i++) {
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
        }
        //insert all users in DB at the same time
        $manager->flush();
    }
}
