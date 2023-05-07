<?php
    use LegacyApp\Platform\Models\System;
    use LegacyApp\Site\Enums\Permissions;
    use Illuminate\Support\Str;
    
    $consoleList = System::get(['ID', 'Name'])->keyBy('ID')->map(fn ($system) => $system['Name']);

    $consoleIDInput = @request("c", 0);
    $filter = @request("f", 0); // 0 = no filter, 1 = only complete, 2 = only incomplete
    $sortBy = @request("s", 0);
    $dev = @request("d");

    $queryParams = "c=$consoleIDInput&f=$filter";

    $sort1 = ($sortBy <= 1) ? 11 : 1;
    $sort2 = ($sortBy == 2) ? 12 : 2;
    $sort3 = ($sortBy == 3) ? 13 : 3;
    $sort4 = ($sortBy == 4) ? 14 : 4;
    $sort5 = ($sortBy == 5) ? 15 : 5;
    $sort6 = ($sortBy == 6) ? 16 : 6;
    $sort7 = ($sortBy == 7) ? 17 : 7;

    if ($dev == null && ($consoleIDInput == 0 || $filter != 0)) {
        $maxCount = 50;
        $offset = max(@request('o', 0, 'integer'), 0);
    } else {
        $maxCount = 0;
        $offset = 0;
    }

    authenticateFromCookie($user, $permissions, $userDetails);

    $showTickets = (isset($user) && $permissions >= Permissions::Developer);
    $gamesList = [];
    $gamesCount = getGamesListByDev($dev, $consoleIDInput, $gamesList, (int) $sortBy, $showTickets, $filter, $offset, $maxCount);
    
    $gameCount = 0;
    $pointsTally = 0;
    $achievementsTally = 0;
    $truePointsTally = 0;
    $lbCount = 0;

    if ($consoleList->has($consoleIDInput)) {
        $consoleName = $consoleList[$consoleIDInput];
        $requestedConsole = $consoleName;
    } elseif ($consoleIDInput === 0) {
        $consoleName = "All Games";
        $requestedConsole = "All";
    } else {
        abort(404);
    }

    $cleanSystemShortName = Str::lower(str_replace("/", "", config("systems.$consoleIDInput.name_short")));
    $iconName = $cleanSystemShortName ? Str::kebab($cleanSystemShortName) : "unknown";

    $showTotals = false;

    if ($consoleList->has($consoleIDInput)) {
        $consoleName = $consoleList[$consoleIDInput];
        $requestedConsole = $consoleName;
    } elseif ($consoleIDInput === 0) {
        $consoleName = "All Games";
        $requestedConsole = "All";
    } else {
        abort(404);
    }

    $pageTitle = $requestedConsole . " Games";
?>

<x-app-layout :page-title=$pageTitle>
    <div id="mainpage">
        <div id="fullcontainer">
            <div>
                @if ($dev !== null)
                    <!-- TODO -->
                @else
                    <h2 class="flex gap-x-2">
                        <img src="assets/images/system/{{$iconName}}.png" alt="" width="32" height="32"></img>
                        <span>{{$consoleName}}</span>
                    </h2>

                    <div style='float:left'>{{$gamesCount}} Games</div>

                    <!-- TODO: fix the logic -->
                    <div align='right'>
                        <select class='gameselector' onchange='window.location = "/gameList?s={{$sortBy}}&c={{$consoleIDInput}}" + this.options[this.selectedIndex].value'>
                            <option value='' @if ($filter == 0) selected @endif>Games with achievements</option>
                            <option value='&f=1' @if ($filter == 1) selected @endif>Games without achievements</option>
                            <option value='&f=2' @if ($filter == 2) selected @endif>All games</option>
                        </select>
                    </div>
                    <!-- END TODO -->

                    <br/>

                    <!-- gameList.php/ListGames -->
                    <div class='table-wrapper'>
                        <table class='table-highlight'>
                            <tbody>
                                <tr class='do-not-highlight'>
                                    <th class='pr-0'></th>
                                    @if ($dev == null)
                                        <th><a href='/gameList?s={{$sort1}}&{{$queryParams}}'>Title</a></th>
                                        <th><a href='/gameList?s={{$sort2}}&{{$queryParams}}'>Achievements</a></th>
                                        <th><a href='/gameList?s={{$sort3}}&{{$queryParams}}'>Points</a></th>
                                        <th><a href='/gameList?s={{$sort7}}&{{$queryParams}}'>Retro Ratio</a></th>
                                        <th style='white-space: nowrap'><a href='/gameList?s={{$sort6}}&{{$queryParams}}'>Last Updated</a></th>
                                        <th><a href='/gameList?s={{$sort4}}&{{$queryParams}}'>Leaderboards</a></th>

                                        @if ($showTickets) 
                                            <th class='whitespace-nowrap'><a href='/gameList?s={{$sort5}}&{{$queryParams}}'>Open Tickets</a></th>
                                        @endif
                                    @else 
                                        <th>Title</th>
                                        <th>Achievements</th>
                                        <th>Points</th>
                                        <th style='white-space: nowrap'>Last Updated</th>
                                        <th>Leaderboards</th>

                                        @if ($showTickets) 
                                            <th class='whitespace-nowrap'>Open Tickets</th>
                                        @endif
                                    @endif
                                </tr>
                                @foreach ($gamesList as $gameEntry)
                                    @php
                                        $gameID = $gameEntry['ID'];
                                        $retroRatio = $gameEntry['RetroRatio'];
                                        $maxPoints = $gameEntry['MaxPointsAvailable'] ?? 0;
                                        $totalTrueRatio = $gameEntry['TotalTruePoints'];

                                        if ($dev == null) {
                                            $numAchievements = $gameEntry['NumAchievements'];
                                            $numTrueRatio = $totalTrueRatio;
                                            $numPoints = $maxPoints;
                                        } else {
                                            $numAchievements = $gameEntry['MyAchievements'];
                                            $numTrueRatio = $gameEntry['MyTrueRatio'];
                                            $numPoints = $gameEntry['MyPoints'];
                                        }

                                        $numLBs = $gameEntry['NumLBs'];
                                    @endphp
                                    <tr>
                                        <td class='pr-0'>
                                            <!-- TODO -->
                                            @php
                                                echo gameAvatar($gameEntry, label: false);
                                            @endphp
                                            <!--END TODO -->
                                        </td>
                                        <td class='w-full'>
                                            <!-- TODO -->
                                            @php
                                                $gameLabelData = $gameEntry;
                                                unset($gameLabelData['ConsoleName']);
                                                echo gameAvatar($gameLabelData, icon: false);
                                                
                                            @endphp
                                            <!--END TODO -->
                                        </td>
                                        @if ($dev == null)
                                            <td>{{$numAchievements}}</td>
                                            <td class='whitespace-nowrap'>{{$maxPoints}} <span class='TrueRatio'>({{$numTrueRatio}})</span></td>
                                        @else
                                            <td>{{$numAchievements}} of $totalAchievements</td>
                                            <td class='whitespace-nowrap'>$numPoints of $maxPoints <span class='TrueRatio'>($numTrueRatio)</span></td>
                                        @endif

                                        <td>{{$retroRatio}}</td>

                                        @if ($gameEntry['DateModified'] != null)
                                            <td>{{date("d M, Y", strtotime($gameEntry['DateModified']))}}</td>
                                        @else
                                            <td/>
                                        @endif

                                        <td class=''>
                                        @if ($numLBs > 0) 
                                            @if ($dev == null) 
                                                <a href="game/{{$gameID}}">{{$numLBs}}</a>
                                                @php $lbCount += $numLBs; @endphp
                                            @else
                                                <a href="game/{{$gameID}}">{{$devLeaderboards}} of {{$numLBs}}</a>
                                                @php $lbCount += $devLeaderboards; @endphp
                                            @endif
                                        @endif
                                        </td>

                                        @if ($showTickets)
                                            @php $openTickets = $gameEntry['OpenTickets']; @endphp
                                            <td class=''>
                                                @if ($openTickets > 0)
                                                    @if ($dev == null)
                                                        <a href='ticketmanager.php?g={{$gameID}}'>{{$openTickets}}</a>
                                                        @php $ticketsCount += $openTickets; @endphp
                                                    @else
                                                        <a href='ticketmanager.php?g=$gameID'>{{$devTickets}} of {{$openTickets}}</a>
                                                        @php $ticketsCount += $devTickets; @endphp 
                                                    @endif
                                                @endif
                                            </td>
                                        @endif
                                    </tr>

                                    @php
                                        $gameCount++;
                                        $pointsTally += $numPoints;
                                        $achievementsTally += $numAchievements;
                                        $truePointsTally += $numTrueRatio;
                                    @endphp
                                @endforeach

                                @if ($showTotals)
                                    <tr class='do-not-highlight'>
                                        <td></td>
                                        <td><b>Totals: {{$gameCount}} games</b></td>
                                        <td><b>{{$achievementsTally}}</b></td>
                                        <td><b>{{$pointsTally}}</b><span class='TrueRatio'> ({{$truePointsTally}})</span></td>
                                        <td></td>
                                        <td><b>{{$lbCount}}</b></td>
                                        @if ($showTickets) 
                                            <td><b>{{$ticketsCount}}</b></td>
                                        @endif
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    @if ($maxCount != 0 && $gamesCount > $maxCount)
                        <br/>
                        <div class='float-right row'>
                            @php RenderPaginator($gamesCount, $maxCount, $offset, "/gameList?s=$sortBy&c=$consoleIDInput&f=$filter&o=") @endphp
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>