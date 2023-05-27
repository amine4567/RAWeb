<?php
    $sort1 = ($sortBy <= 1) ? 11 : 1;
    $sort2 = ($sortBy == 2) ? 12 : 2;
    $sort3 = ($sortBy == 3) ? 13 : 3;
    $sort4 = ($sortBy == 4) ? 14 : 4;
    $sort5 = ($sortBy == 5) ? 15 : 5;
    $sort6 = ($sortBy == 6) ? 16 : 6;
    $sort7 = ($sortBy == 7) ? 17 : 7;

    $gameCount = 0;
    $pointsTally = 0;
    $achievementsTally = 0;
    $truePointsTally = 0;
    $lbCount = 0;
    $ticketsCount = 0;
?>

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
                    <th>Retro Ratio</th>
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
                    $devLeaderboards = null;
                    $totalAchievements = null;
                    $devTickets = null;
                    if ($dev == null) {
                        $numAchievements = $gameEntry['NumAchievements'];
                        $numTrueRatio = $totalTrueRatio;
                        $numPoints = $maxPoints;
                    } else {
                        $numAchievements = $gameEntry['MyAchievements'];
                        $totalAchievements = $numAchievements + $gameEntry['NotMyAchievements'];
                        $numTrueRatio = $gameEntry['MyTrueRatio'];
                        $numPoints = $gameEntry['MyPoints'];
                        $devLeaderboards = $gameEntry['MyLBs'];
                        $devTickets = $showTickets == true ? $gameEntry['MyOpenTickets'] : null;
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
                        <td>{{$numAchievements}} of {{$totalAchievements}}</td>
                        <td class='whitespace-nowrap'>{{$numPoints}} of {{$maxPoints}} <span class='TrueRatio'>({{$numTrueRatio}})</span></td>
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