<?php

use App\Install\Steps\ConfigureDatabase;
use App\Install\Steps\CreateAdminUser;
use App\Install\Steps\EnableBundledExtensions;
use App\Install\Steps\FinishInstall;
use App\Install\Steps\SeedDefaultSettings;
use App\User\Concerns\PasswordValidationRules;
use App\User\Concerns\ProfileValidationRules;
use Flux\Flux;
use Illuminate\Support\Facades\Pipeline;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::install')] class extends Component
{
    use PasswordValidationRules, ProfileValidationRules;

    public string $forum_title = '';

    public string $database_name = '';

    public string $database_host = '';

    public string $database_username = '';

    public string $database_password = '';

    public string $user_username = '';

    public string $user_email = '';

    public string $user_password = '';

    public string $user_password_confirmation = '';

    public function mount(): void
    {
        $this->forum_title = __('A New Forum');
        $this->database_name = __('forum');
        $this->database_host = __('localhost');
        $this->database_username = __('root');
    }

    public function rules(): array
    {
        return [
            'forum_title' => ['required'],
            'database_name' => ['required'],
            'database_host' => ['required'],
            'database_username' => ['required'],
            'database_password' => ['nullable'],
            'user_username' => [
                'required',
                'regex:/^[a-z0-9_-]+$/i',
                'min:3',
                'max:80',
            ],
            'user_email' => [
                'required',
                'string',
                'email',
                'max:255',
            ],
            'user_password' => $this->passwordRules(),
            'user_password_confirmation' => ['required'],
        ];
    }

    public function startInstall(): void
    {
        try {
            Pipeline::send($this->validate())
                ->through([
                    ConfigureDatabase::class,
                    SeedDefaultSettings::class,
                    CreateAdminUser::class,
                    //                    EnableBundledExtensions::class,
                    FinishInstall::class,
                ])
                ->thenReturn();
        } catch (Exception $exception) {
            Flux::toast(
                text: $exception->getMessage(),
                variant: 'danger',
            );
        }

        $this->redirect('/');
    }
};
