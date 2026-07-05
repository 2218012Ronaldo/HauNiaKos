<?php

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;

class Login extends BaseLogin
{
    public string $selectedRole = 'user';

    public function mount(): void
    {
        $this->selectedRole = $this->resolveSelectedRole();

        parent::mount();
    }

    public function authenticate(): ?LoginResponse
    {
        $response = parent::authenticate();

        if ($response) {
            session()->flash('status', 'Login berhasil! Selamat datang.');
            session()->flash('status_type', 'login');
        }

        return $response;
    }

    public function registerAction(): Action
    {
        return parent::registerAction()->url(
            filament()->getRegistrationUrl(['role' => $this->getSelectedRole()]),
        );
    }

    protected function getSelectedRole(): string
    {
        return $this->selectedRole;
    }

    protected function resolveSelectedRole(): string
    {
        $role = request()->query('role', 'user');

        return in_array($role, ['user', 'owner_kost'], true) ? $role : 'user';
    }
}