<?php

namespace Backstage\Mails\Tests\Fixtures;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    protected $table = 'users';

    protected $guarded = [];

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function getTenants(Panel $panel): Collection
    {
        return Team::all();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return true;
    }
}
