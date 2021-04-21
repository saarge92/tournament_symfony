<?php

namespace App\DataFixtures;

use App\Entity\Stage;
use App\Repository\StageRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class StageFixture extends Fixture implements OrderedFixtureInterface
{
    protected array $stages = [
        1 => 'Qualification',
        2 => 'Quarterfinal',
        3 => 'Semifinal',
        4 => 'Third place',
        5 => 'Final'
    ];
    private StageRepository $stageRepository;

    public function __construct(StageRepository $stageRepository)
    {
        $this->stageRepository = $stageRepository;
    }

    public function load(ObjectManager $manager)
    {
        foreach ($this->stages as $index => $stage) {
            $stageExist = $this->stageRepository->find($index);
            if (!$stageExist) {
                $newStage = new Stage($index, $stage);
                $manager->persist($newStage);
            }
        }
        $manager->flush();
    }

    public function getOrder(): int
    {
        return 3;
    }
}