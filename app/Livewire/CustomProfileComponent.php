<?php

namespace App\Livewire;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Joaopaulolndev\FilamentEditProfile\Concerns\HasSort;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Session;
use Rawilk\FilamentPasswordInput\Password;

class CustomProfileComponent extends Component implements HasForms
{
    use InteractsWithForms;
    use HasSort;

    public ?array $data = [];

    protected static int $sort = 0;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Update Password')
                    ->description('Change your account password.')
                    ->schema([
                        Password::make('current_password')
                            ->label('Current Password')
                            ->password()
                            ->required()
                            ->rule('current_password'),

                        Password::make('password')
                            ->label('New Password')
                            ->password()
                            ->required()
                            ->minLength(8)
                            ->different('current_password')
                            ->same('password_confirmation'),

                        Password::make('password_confirmation')
                            ->label('Confirm New Password')
                            ->password()
                            ->minLength(8)
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        if (!Hash::check($data['current_password'], Auth::user()->password)) {
            Notification::make()
                ->title('The current password is incorrect.')
                ->danger()
                ->duration(3000)
                ->send();
            return;
        }

        Auth::user()->update([
            'password' => Hash::make($data['password']),
            'must_change_password' => false,
        ]);

        Notification::make()
            ->title('Password updated successfully. Please log in again.')
            ->success()
            ->duration(3000)
            ->send();

        // Add these lines to properly log out
        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();

        // Redirect to Filament's login route
        $this->redirect(route('filament.backoffice.auth.login'));
    }



    public function render(): View
    {
        return view('livewire.custom-profile-component');
    }
}
