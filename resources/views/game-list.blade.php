<?php
    use LegacyApp\Platform\Models\System;
    use LegacyApp\Site\Enums\Permissions;
    use Illuminate\Support\Str;
    
    $consoleList = System::get(['ID', 'Name'])->keyBy('ID')->map(fn ($system) => $system['Name']);

    $consoleIDInput = (int)@request("c", 0);
    $filter = @request("f", 0); // 0 = no filter, 1 = only complete, 2 = only incomplete
    $sortBy = @request("s", 0);
    $dev = @request("d");

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

    $queryParams = ($dev == null) ? "c=$consoleIDInput&f=$filter" : '';
    $showTotals = ($dev == null) ? ($maxCount == 0) : true;

    if ($consoleList->has($consoleIDInput)) {
        $consoleName = $consoleList[$consoleIDInput];
        $requestedConsole = $consoleName;
    } elseif ($consoleIDInput === 0) {
        $consoleName = "All Games";
        $requestedConsole = "All";
    }

    $pageTitle = $requestedConsole . " Games";

    if ($dev !== null) {
        // Determine which consoles the dev has created content for
        $devConsoles = [];
        foreach ($consoleList as $consoleID => $consoleName) {
            $consoleGames = array_filter($gamesList, fn ($game) => $game['ConsoleID'] == $consoleID);
            if (!empty($consoleGames)) {
                $devConsoles[$consoleName] = $consoleGames;
            }
        }

        ksort($devConsoles);
    }
?>

<x-app-layout :page-title=$pageTitle>
    <div>
        @if ($dev !== null)
            @foreach ($devConsoles as $consoleName => $consoleGames)
                <h2>{{$consoleName}}</h2>
                <x-gamelist.game-list-table :gamesList=$consoleGames :dev=$dev :sortBy=$sortBy :showTickets=$showTickets :showTotals="true"/>
                <br/>
            @endforeach
        @else
            <h2 class="flex gap-x-2">
                <img src="assets/images/system/{{$iconName}}.png" alt="" width="32" height="32"></img>
                <span>{{$consoleName}}</span>
            </h2>

            <div style='float:left'>{{$gamesCount}} Games</div>

            <div align='right'>
                <select class='gameselector' onchange='window.location = "/gameList?s={{$sortBy}}&c={{$consoleIDInput}}" + this.options[this.selectedIndex].value'>
                    <option value='' @if ($filter == 0) selected @endif>Games with achievements</option>
                    <option value='&f=1' @if ($filter == 1) selected @endif>Games without achievements</option>
                    <option value='&f=2' @if ($filter == 2) selected @endif>All games</option>
                </select>
            </div>

            <br/>

            <x-gamelist.game-list-table :gamesList=$gamesList :dev=$dev :queryParams=$queryParams :sortBy=$sortBy :showTickets=$showTickets :showTotals=$showTotals/>
            @if ($maxCount != 0 && $gamesCount > $maxCount)
                <br/>
                <div class='float-right row'>
                    @php RenderPaginator($gamesCount, $maxCount, $offset, "/gameList?s=$sortBy&c=$consoleIDInput&f=$filter&o=") @endphp
                </div>
            @endif
        @endif
    </div>
</x-app-layout>