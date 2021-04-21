<?php

namespace App\DataFixtures;

use App\Entity\Tournament;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TournamentFixture extends Fixture implements OrderedFixtureInterface
{
    protected array $tournaments = ['Кубок Прибалтики', 'Чемпионат Европы', 'Кубок СНГ'];

    public function load(ObjectManager $manager)
    {
        foreach ($this->tournaments as $tournamentName) {
            $tournamentRecord = new Tournament($tournamentName);
            $manager->persist($tournamentRecord);
        }
        $manager->flush();
    }

    public function getOrder(): int
    {
        return 4;
    }
}