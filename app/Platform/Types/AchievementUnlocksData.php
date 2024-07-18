<?php
namespace App\Platform\Types;

class AchievementData
{
    public string $badgeName;
    public int $numAwarded;
    public int $numAwardedHardcore;

    public function __construct(string $badgeName, int $numAwarded, int $numAwardedHardcore)
    {
        $this->badgeName = $badgeName;
        $this->numAwarded = $numAwarded;
        $this->numAwardedHardcore = $numAwardedHardcore;
    }
}