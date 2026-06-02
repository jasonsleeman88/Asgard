<?php

namespace App\User\Concerns;

use App\Settings\Contracts\SettingsRepository;
use App\User\Models\User;
use Illuminate\Container\EntryNotFoundException;
use Illuminate\Contracts\Container\CircularDependencyException;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

trait ProfileValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @throws CircularDependencyException
     * @throws ContainerExceptionInterface
     * @throws EntryNotFoundException
     * @throws NotFoundExceptionInterface
     */
    protected function profileRules(?int $userId = null): array
    {
        return [
            'username' => $this->usernameRules($userId),
//            'nickname' => $this->nicknameRules($userId),
            'email' => $this->emailRules($userId),
        ];
    }

    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function usernameRules(?int $userId = null): array
    {
        $rules = [
            'required',
            'regex:/^[a-z0-9_-]+$/i',
            Rule::unique('users', 'email')
                ->ignore(Auth::user() ? ','.Auth::user()->getKey() : ''),
            'min:3',
            'max:80',
        ];

        if (resolve(SettingsRepository::class)->get('nicknames.unique')) {
            $rules[] = Rule::unique('users', 'nickname')->ignore($userId);
        }

        return $rules;
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws CircularDependencyException
     * @throws EntryNotFoundException
     */
    protected function nicknameRules(?int $userId = null): array
    {
        $settings = resolve(SettingsRepository::class);

        $rules = [
            'sometimes',
            function ($attribute, $value, $fail) use ($settings) {
                $regex = $settings->get('nicknames.regex');
                if ($regex && ! preg_match_all("/$regex/", $value)) {
                    $fail('invalid_nickname_message');
                }
            },
            'min:'.$settings->get('nicknames.min', 1),
            'max:'.$settings->get('nicknames.max', 150),
        ];

        if ($settings->get('nicknames.unique')) {
            $rules[] = Rule::unique('users', 'username')->ignore($userId);
            $rules[] = Rule::unique('users', 'nickname')->ignore($userId);
        }

        return $rules;
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }
}
