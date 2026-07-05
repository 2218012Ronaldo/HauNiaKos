<?php

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class Registration extends BaseRegister
{
    public string $selectedRole = 'user';

    public function mount(): void
    {
        $this->selectedRole = $this->resolveSelectedRole();

        parent::mount();
    }

    public function loginAction(): Action
    {
        return Action::make('login')
            ->link()
            ->label(__('filament-panels::auth/pages/register.actions.login.label'))
            ->url(filament()->getLoginUrl(['role' => $this->getSelectedRole()]));
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            $this->getNameFormComponent(),
            $this->getEmailFormComponent(),
            TextInput::make('phone')
                ->label('Phone number')
                ->tel()
                ->required()
                ->maxLength(20)
                ->autocomplete('tel'),
            $this->getPasswordFormComponent(),
            $this->getPasswordConfirmationFormComponent(),
        ]);
    }

    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $data['role'] = $this->getSelectedRole();

        return $data;
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