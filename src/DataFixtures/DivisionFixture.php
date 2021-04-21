<?php

namespace App\DataFixtures;


use App\Entity\Division;
use App\Repository\DivisionRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DivisionFixture extends Fixture
{
    private array $divisions = ['A', 'B'];

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->divisions as $divisionName) {
            $divisionRecord = new Division($divisionName);
            $manager->persist($divisionRecord);
        }
        $manager->flush();
    }
}