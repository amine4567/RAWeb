<?php

use App\Community\Actions\AddGameBadgeCreditAction;
use App\Community\Enums\ArticleType;
use App\Community\Enums\ClaimSetType;
use App\Enums\Permissions;
use App\Models\Game;
use App\Models\GameSet;
use App\Models\System;
use App\Models\User;
use App\Platform\Enums\GameSetType;
use App\Platform\Enums\ImageType;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

if (!authenticateFromCookie($user, $permissions, $userDetails, Permissions::JuniorDeveloper)) {
    return back()->withErrors(__('legacy.error.permissions'));
}

$input = Validator::validate(Arr::wrap(request()->post()), [
    'game' => 'required|integer|exists:GameData,ID',
    'type' => ['required', 'string', Rule::in(ImageType::cases())],
    'file' => ['image'],
]);

if ($input['type'] === ImageType::GameIcon) {
    Validator::make(
        request()->all(),
        ['file' => ['dimensions:width=96,height=96']],
        ['file.dimensions' => 'Game icons are required to have dimensions of 96x96 pixels.']
    )->validate();
}

$gameID = (int) $input['game'];
$imageType = $input['type'];

$userModel = User::whereName($user)->first();

// Only allow jr. devs if they are the sole author of the set or have the primary claim
if (
    $permissions == Permissions::JuniorDeveloper
    && (!checkIfSoleDeveloper($userModel, $gameID) && !hasSetClaimed($userModel, $gameID, true, ClaimSetType::NewSet))
) {
    return back()->withErrors(__('legacy.error.permissions'));
}

try {
    $imagePath = UploadGameImage($_FILES['file'], $imageType);
} catch (Exception) {
    return back()->withErrors(__('legacy.error.image_upload'));
}

$field = match ($imageType) {
    ImageType::GameIcon => 'ImageIcon',
    ImageType::GameTitle => 'ImageTitle',
    ImageType::GameInGame => 'ImageIngame',
    ImageType::GameBoxArt => 'ImageBoxArt',
    default => null, // should never hit this because of the match above
};
if (!$field) {
    return back()->withErrors(__('legacy.error.image_upload'));
}

$game = Game::find($gameID);
if (!$game) {
    return back()->withErrors(__('legacy.error.image_upload'));
}

$game->$field = $imagePath;
if (!$game->save()) {
    return back()->withErrors(__('legacy.error.image_upload'));
}

if ($field === 'ImageIcon') {
    // Credit the uploader for artwork. Note that this is smart
    // enough to not create duplicate credit entries.
    (new AddGameBadgeCreditAction())->execute($game, $userModel);

    // Double write to game_sets.
    if ($game->ConsoleID === System::Hubs) {
        $hubGameSet = GameSet::whereType(GameSetType::Hub)->whereGameId($game->id)->first();
        if ($hubGameSet) {
            $hubGameSet->image_asset_path = $imagePath;
            $hubGameSet->save();
        }
    }
}

$label = match ($imageType) {
    ImageType::GameIcon => 'game icon',
    ImageType::GameTitle => 'title screenshot',
    ImageType::GameInGame => 'in-game screenshot',
    ImageType::GameBoxArt => 'game box art',
    default => '?', // should never hit this because of the match above
};

addArticleComment('Server', ArticleType::GameModification, $gameID, "{$userModel->display_name} changed the $label");

return back()->with('success', __('legacy.success.image_upload'));
