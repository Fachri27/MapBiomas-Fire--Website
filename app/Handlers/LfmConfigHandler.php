<?php

namespace App\Handlers;

use UniSharp\LaravelFilemanager\Handlers\ConfigHandler;

class LfmConfigHandler extends ConfigHandler
{
    public function userField()
    {
        return session('id') ?: (auth()->id() ?: 'shared');
    }
}
