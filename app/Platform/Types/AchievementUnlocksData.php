<?php
namespace App\Platform\Types;

class AchievementUnlocksData
{
    public int $id;
    public string $badgeName;
    public int $numAwarded;
    public int $numAwardedHardcore;

    public function __construct(int $id, string $badgeName, int $numAwarded, int $numAwardedHardcore)
    {
        $this->id = $id;
        $this->badgeName = $badgeName;
        $this->numAwarded = $numAwarded;
        $this->numAwardedHardcore = $numAwardedHardcore;
    }

    public function getArray()
    {
        return [
            "ID" => $this->id,
            "NumAwarded" => $this->numAwarded,
            "NumAwardedHardcore" => $this->numAwardedHardcore
        ];
    }
}