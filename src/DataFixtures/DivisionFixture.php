<?php

namespace App\DataFixtures;

use App\Entity\Division;
use App\Repository\DivisionRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class DivisionFixture extends Fixture implements OrderedFixtureInterface
{
    private array $divisions = ['A', 'B'];
    private DivisionRepository $divisionRepository;

    public function __construct(DivisionRepository $divisionRepository)
    {
        $this->divisionRepository = $divisionRepository;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->divisions as $divisionName) {
            $divisionRecordExist = $this->divisionRepository->findOneBy(['name' => $divisionName]);
            if (!$divisionRecordExist) {
                $divisionRecord = new Division($divisionName);
                $manager->persist($divisionRecord);
            }
        }
        $manager->flush();
    }

    public function getOrder(): int
    {
        return 1;
    }
}