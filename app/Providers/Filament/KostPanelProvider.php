<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\Registration;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;

class KostPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
       return $panel
            ->default()
            ->id('kost')
            ->path('kost')
            ->login(Login::class)
            ->registration(Registration::class)
            ->passwordReset()

            ->plugins([
                FilamentEditProfilePlugin::make()
                    ->slug('my-profile')
                    ->setTitle('My Profile')
                    ->setNavigationLabel('My Profile')
                    ->setNavigationGroup('Group Profile')
                    ->setIcon('heroicon-o-user')
                    ->setSort(10)
                    ->canAccess(
                        fn() => filament()->auth()->user()?->role &&
                            in_array(filament()->auth()->user()?->role, [
                                'admin',
                                'owner_kost',
                                'user',
                            ]),
                    )
                    ->shouldRegisterNavigation(false)
                    ->shouldShowEditProfileForm(false)
                    ->shouldShowEmailForm() // optional validation rules for the theme color field
                    ->shouldShowDeleteAccountForm(true)
                    ->shouldShowBrowserSessionsForm(
                        fn() => filament()->auth()->user()?->role === 'admin',
                    )
                    ->shouldShowMultiFactorAuthentication()
                    ->shouldShowAvatarForm()
                    ->customProfileComponents([\App\Livewire\Profile\EditProfile::class]),
            ])

            ->userMenuItems([
                'profile' => Action::make('profile')
                    ->label(fn() => 'My Profile')
                    ->url(
                        fn(): string => \Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage::getUrl(),
                    )
                    ->icon('heroicon-m-user-circle')
                    ->visible(fn() => filament()->auth()->check()),
                'home' => Action::make('home')
                    ->label('Kembali ke Home')
                    ->url(fn(): string => route('home'))
                    ->icon('heroicon-o-home')
                    ->visible(fn() => filament()->auth()->check()),
            ])
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
