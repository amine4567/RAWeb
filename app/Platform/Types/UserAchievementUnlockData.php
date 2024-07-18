<?php
namespace App\Platform\Types;

class UserAchievementUnlockData
{
    public string $userName;
    public string $dateEarned;
    public bool $isHardcore;

    public function __construct(string $userName, string $dateEarned, bool $isHardcore)
    {
        $this->userName = $userName;
        $this->dateEarned = $dateEarned;
        $this->isHardcore = $isHardcore;
    }
}