<?php

namespace App\Livewire\Profile;

use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Joaopaulolndev\FilamentEditProfile\Livewire\EditProfileForm;

class EditProfile extends EditProfileForm
{
    protected static int $sort = 10;

    public function mount(): void
    {
        $plugin = $this->getEditProfilePlugin();

        $this->user = $this->getUser();

        $this->userClass = get_class($this->user);

        $fields = [config('filament-edit-profile.name_column', 'name'), 'email', 'phone'];

        if ($plugin->getShouldShowAvatarForm()) {
            $fields[] = config('filament-edit-profile.avatar_column', 'avatar_url');
        }

        $this->form->fill($this->user->only($fields));
    }

    public function form(Schema $schema): Schema
    {
        $plugin = $this->getEditProfilePlugin();

        return $schema
            ->components([
                Section::make('Profile Information')
                    ->aside()
                    ->description('Update your account profile information and email address.')
                    ->schema([
                        FileUpload::make(
                            config('filament-edit-profile.avatar_column', 'avatar_url'),
                        )
                            ->label('Avatar')
                            ->avatar()
                            ->imageEditor()
                            ->disk(config('filament-edit-profile.disk', 'public'))
                            ->visibility(config('filament-edit-profile.visibility', 'public'))
                            ->directory($plugin->getAvatarDirectory())
                            ->rules($plugin->getAvatarRules())
                            ->hidden(! $plugin->getShouldShowAvatarForm()),
                        TextInput::make(config('filament-edit-profile.name_column', 'name'))
                            ->label('Name')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->hidden(! $plugin->getShouldShowEmailForm())
                            ->unique($this->userClass, ignorable: $this->user),
                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->placeholder('08xxxxxxxxxx atau +628xxxxxxxxxx')
                            ->rule('regex:/^(08\d{8,13}|\+628\d{7,12})$/')
                            ->validationMessages([
                                'regex' => 'Nomor telepon harus format 08xxxxxxxxxx atau +628xxxxxxxxxx.',
                            ])
                            ->maxLength(20),
                    ]),
            ])
            ->statePath('data');
    }

    private function getEditProfilePlugin(): FilamentEditProfilePlugin
    {
        /** @var FilamentEditProfilePlugin $plugin */
        $plugin = Filament::getCurrentOrDefaultPanel()?->getPlugin('filament-edit-profile');

        return $plugin;
    }
}