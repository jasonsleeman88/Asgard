<?php

namespace App\Foundation\Console\Commands;

use App\User\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Console\Prohibitable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Laravel\Prompts\Support\Logger;
use Symfony\Component\Console\Attribute\AsCommand;
use Throwable;

use function Laravel\Prompts\form;
use function Laravel\Prompts\info;
use function Laravel\Prompts\pause;
use function Laravel\Prompts\task;

#[AsCommand('forum:install')]
#[Description('Command description')]
class InstallCommand extends Command
{
    use ConfirmableTrait, Prohibitable;

    /**
     * Execute the console command.
     *
     * @throws Throwable
     */
    public function handle()
    {
        if ($this->isProhibited() ||
            ! $this->confirmToProceed()) {
            return Command::FAILURE;
        }

        $this->displayHeader();

        if (Storage::disk('local')->exists('installed.json')) {
            if (! $reinstall = $this->confirm('Application already installed. Do you want to reinstall?')) {
                $this->fail('Install Terminated!');
            } else {
                Storage::disk('local')->delete('installed.json');
                $this->callSilently('down');
                $this->callSilently('db:wipe');
            }
        }

        pause('Press ENTER to continue.');

        $user = $this->buildAdminUser();

        task(
            label: 'Application Installation',
            callback: function ($logger) use ($user) {
                $this->runMigrations($logger);
                $this->createAdminUser($logger, $user);
                $this->finishInstallation($logger);
            },
            keepSummary: true,
        );
    }

    public function displayHeader(): void
    {
        $this->line('');

        $lines = [
            ' ██╗       █████╗  ██████╗   █████╗  ███████╗ ███████╗ ██████╗  ██╗  ██╗ ███╗    ███╗',
            ' ██║      ██╔══██╗ ██╔══██╗ ██╔══██╗ ██╔════╝ ██╔══██║ ██╔══██╗ ██║  ██║ ██║██╗██╝██║',
            ' ██║      ███████║ ██████╔╝ ███████║ ███████╗ ██║  ██║ ██████╔╝ ██║  ██║ ██║╚███╝ ██║',
            ' ██║      ██╔══██║ ██╔══██╗ ██╔══██║ ██╔════╝ ██║  ██║ ██╔══██╗ ██║  ██║ ██║      ██║',
            ' ███████╗ ██║  ██║ ██║  ██║ ██║  ██║ ██║      ███████║ ██║  ██║ ███████║ ██║      ██║',
            ' ╚══════╝ ╚═╝  ╚═╝ ╚═╝  ╚═╝ ╚═╝  ╚═╝ ╚═╝      ╚══════╝ ╚═╝  ╚═╝ ╚══════╝ ╚═╝      ╚═╝',
        ];

        $gradients = [
            'Red' => [196, 160, 124, 88, 52, 88],
            'Gray' => [250, 248, 245, 243, 240, 238],
            'Ocean' => [81, 75, 69, 63, 57, 21],
            'Vaporwave' => [213, 177, 141, 105, 69, 39],
            'Sunset' => [214, 208, 202, 196, 160, 124],
            'Aurora' => [51, 50, 49, 48, 47, 41],
            'Ember' => [227, 221, 215, 209, 203, 197],
            'Cyberpunk' => [201, 165, 129, 93, 57, 21],
        ];

        $themeName = array_rand($gradients);
        $gradient = $gradients[$themeName];

        foreach ($lines as $index => $line) {
            $color = $gradient[$index];
            $this->line("\e[38;5;{$color}m{$line}\e[0m");
        }
    }

    private function runMigrations(Logger $logger): void
    {
        $logger->line('Running migrations...');

        $this->callSilently('migrate:fresh');

        $logger->success('Migrations Completed');
    }

    private function buildAdminUser(): array
    {
        info('First, lets create an admin account.');

        $user = form()
            ->text(
                label: __('Username'),
                placeholder: __('johndoe123'),
                required: true,
                validate: [
                    'string',
                    'min:5',
                    'max:20',
                ],
                name: 'username',
            )->text(
                label: __('Email'),
                placeholder: __('johndoe@example.sendNotificationsJob'),
                required: true,
                validate: [
                    'string',
                    'email',
                    'max:255',
                ],
                name: 'email'
            )->password(
                label: __('Password'),
                placeholder: __('Password'),
                required: true,
                validate: [
                    'string',
                    Password::default(),
                ],
                name: 'password'
            )->submit();

        return $user;
    }

    private function createAdminUser(Logger $logger, array $user): void
    {
        $logger->line('Creating admin user: '.$user['username']);

        $user = User::forceCreate($user)
            ->assignRole('admin')
            ->markEmailAsVerified();

        $logger->success('Admin User Created');
    }

    private function finishInstallation(Logger $logger): void
    {
        $logger->line('Finishing Installation...');

        Storage::disk('local')->put('installed.json', json_encode([
            'installed' => true,
            'date' => now()->toDateTimeString(),
        ]));

        $this->callSilently('up');

        $logger->success('Installation Complete');
    }
}
