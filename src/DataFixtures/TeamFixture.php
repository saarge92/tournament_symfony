<?php

namespace App\DataFixtures;

use App\Entity\Team;
use App\Repository\DivisionRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TeamFixture extends Fixture implements OrderedFixtureInterface
{
    private DivisionRepository $divisionRepository;

    protected array $teamsDivisionA = [
        'Россия',
        'Латвия',
        'Эстония',
        'Литва',
        'Польша',
        'Словакия',
    ];

    protected array $teamDivisionB = [
        'Швеция',
        'Финляндия',
        'Норвегия',
        'Дания',
        'Швейцария',
        'Германия'
    ];

    public function __construct(DivisionRepository $divisionRepository)
    {
        $this->divisionRepository = $divisionRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $divisionA = $this->divisionRepository->findOneBy(['name' => 'A']);
        foreach ($this->teamsDivisionA as $teamName) {
            $newTeam = new Team($teamName, $divisionA);
            $manager->persist($newTeam);
        }

        $divisionB = $this->divisionRepository->findOneBy(['name' => 'B']);
        foreach ($this->teamDivisionB as $teamName) {
            $newTeam = new Team($teamName, $divisionB);
            $manager->persist($newTeam);
        }

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 2;
    }
}