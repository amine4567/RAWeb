<?php

declare(strict_types=1);

namespace App\Platform\Components;

use App\Models\Achievement;
use App\Platform\Types\AchievementUnlocksData;
use App\Platform\Types\UserAchievementUnlockData;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Support\Carbon;

class AchievementCard extends Component
{

    private Achievement $achievementData;
    private AchievementUnlocksData $achievementUnlocksData;
    private ?UserAchievementUnlockData $userAchievementUnlockData;
    private string $beatenGameCreditDialogContext = 's:|h:';
    private bool $isCreditDialogEnabled = true;
    private bool $showAuthorName = false;
    private int $totalPlayerCount = 0;
    private bool $useMinimalLayout = false;

    public function __construct(Achievement $achievementData, 
    AchievementUnlocksData $achievementUnlocksData,
    ?UserAchievementUnlockData $userAchievementUnlockData, 
    string $beatenGameCreditDialogContext = 's:|h:', 
    bool $isCreditDialogEnabled = true,
    bool $showAuthorName = false,
    int $totalPlayerCount = 0,
    bool $useMinimalLayout = false)
    {
        $this->achievementData = $achievementData;
        $this->achievementUnlocksData = $achievementUnlocksData;
        $this->userAchievementUnlockData = $userAchievementUnlockData;
        $this->beatenGameCreditDialogContext = $beatenGameCreditDialogContext;
        $this->isCreditDialogEnabled = $isCreditDialogEnabled;
        $this->showAuthorName = $showAuthorName;
        $this->totalPlayerCount = $totalPlayerCount;
        $this->useMinimalLayout = $useMinimalLayout;
    }

    public function render(): ?View
    {
        $cardViewValues = $this->buildAllCardViewValues();

        return view('components.cards.achievement', $cardViewValues);
    }

    private function buildAllCardViewValues(): array
    {
        $achBadgeName = $this->achievementData->BadgeName;
        if (!isset($this->userAchievementUnlockData)) {
            $achBadgeName .= "_lock";
        }

        $imgClass = $this->userAchievementUnlockData && $this->userAchievementUnlockData->isHardcore ? 'goldimagebig' : 'badgeimg';
        $imgClass .= ' w-[54px] h-[54px] sm:w-16 sm:h-16';

        $achievementData = $this->achievementData;
        $achievementUnlocksData = $this->achievementUnlocksData;
        $userAchievementUnlockData = $this->userAchievementUnlockData;
        $beatenGameCreditDialogContext = $this->beatenGameCreditDialogContext;
        $useMinimalLayout = $this->useMinimalLayout;
        $isCreditDialogEnabled = $this->isCreditDialogEnabled;
        $showAuthorName = $this->showAuthorName;
        $totalPlayerCount = $this->totalPlayerCount;
        $renderedAchievementAvatar = achievementAvatar(
            $this->achievementData,
            label: false,
            icon: $achBadgeName,
            iconSize: 64,
            iconClass: $imgClass,
            tooltip: false
        );
        $unlockDate = '';
        $unlockTimestamp = 0;
        if(isset($this->userAchievementUnlockData)) {
            $parsedDateEarned = Carbon::parse($this->userAchievementUnlockData->dateEarned);
            $unlockDate = $parsedDateEarned->format('F j Y, g:ia');
            $unlockTimestamp = $parsedDateEarned->timestamp;
        }

        return compact(
            'achievementData',
            'achievementUnlocksData',
            'userAchievementUnlockData',
            'renderedAchievementAvatar',
            'beatenGameCreditDialogContext',
            'isCreditDialogEnabled',
            'showAuthorName',
            'totalPlayerCount',
            'unlockDate',
            'unlockTimestamp',
            'useMinimalLayout'
        );
    }
}
