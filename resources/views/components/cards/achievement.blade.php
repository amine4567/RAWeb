<li class="flex gap-x-3 odd:bg-[rgba(50,50,50,0.4)] light:odd:bg-neutral-200  px-2 py-3 md:py-1 w-full {{ $userAchievementUnlockData ? 'unlocked-row' : '' }} {{ $achievementData->type === 'missable' ? 'missable-row' : '' }}">
    <div class="flex flex-col gap-y-1">
        {!! $renderedAchievementAvatar !!}
    </div>

    <div class="grid w-full gap-y-1.5 gap-x-5 leading-4 md:grid-cols-6 mt-1">
        <div class="md:col-span-4">
            <div class="flex justify-between gap-x-2 mb-0.5">
                <div>
                    <a class="inline mr-1" href="{{ route('achievement.show', $achievementData->ID) }}">
                        <x-achievement.title :rawTitle="$achievementData->Title" />
                    </a>

                    @if ($achievementData->Points > 0 || $achievementData->TrueRatio > 0)
                        <p class="inline text-xs whitespace-nowrap">
                            <span>({{ $achievementData->Points }})</span>
                            <x-points-weighted-container>
                                ({{ localized_number($achievementData->TrueRatio) }})
                            </x-points-weighted-container>
                        </p>
                    @endif
                </div>

                @if ($achievementData->type && !$useMinimalLayout)
                    <div class="flex items-center gap-x-1 md:hidden -mt-1.5">
                        <div class="-mt-1.5">
                            <x-game.achievements-list.type-indicator
                                :achievementType="$achievementData->type"
                                :beatenGameCreditDialogContext="$beatenGameCreditDialogContext"
                                :isCreditDialogEnabled="$isCreditDialogEnabled"
                            />
                        </div>
                    </div>
                @endif
            </div>

            <p class="leading-4">
                {{ $achievementData->Description }}

                @if ($showAuthorName)
                    <span class="flex gap-x-1 text-[0.6rem] mt-2">
                        Author:
                        <a href="{{ route('user.show', $achievementData->Author) }}">
                            {{ $achievementData->Author }}
                        </a>
                    </span>
                @endif
            </p>

            @if ($userAchievementUnlockData)
                <p class="hidden md:block mt-1.5 text-[0.6rem] text-neutral-400/70">
                    Unlocked
                    @if ($unlockDate) 
                        <a href="/historyexamine.php?d={{$unlockTimestamp}}&u=Scott">
                            {{ $unlockDate }} 
                        </a>
                    @endif
                </p>
            @endif
        </div>

        <div class="md:col-span-2 md:flex md:flex-col-reverse md:justify-end md:pt-1 md:gap-y-1">
            @if ($achievementData->type && !$useMinimalLayout)
                <div class="hidden md:flex items-center justify-end gap-x-1">
                    <x-game.achievements-list.type-indicator
                        :achievementType="$achievementData->type"
                        :beatenGameCreditDialogContext="$beatenGameCreditDialogContext"
                        :isCreditDialogEnabled="$isCreditDialogEnabled"    
                    />
                </div>
            @endif

            @if (!$useMinimalLayout)
                <x-game.achievements-list.list-item-global-progress
                    :achievement="$achievementUnlocksData"
                    :totalPlayerCount="$totalPlayerCount"
                />
            @endif
        </div>

        @if ($unlockDate)
            <p class="text-[0.6rem] text-neutral-400/70 md:hidden">Unlocked {{ $unlockDate }}</p>
        @endif
    </div>
</li>