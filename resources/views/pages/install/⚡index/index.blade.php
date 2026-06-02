<form wire:submit="startInstall">
    @csrf
    <flux:card class="space-y-6">
        <div class="flex flex-col items-center justify-center">
            <flux:heading size="xl">
                Application Installer
            </flux:heading>
            <flux:subheading>
                Please complete the form below.
            </flux:subheading>
        </div>

        <flux:card>
            <flux:heading size="lg" accent>
                Forum Settings
            </flux:heading>

            <div class="sm:grid sm:grid-cols-3 sm:items-center sm:gap-4 sm:py-6">
                <flux:label for="forum_title" :badge="__('Required')">
                    {{ __('Forum Title') }}
                </flux:label>
                <div class="mt-2 sm:col-span-2 sm:mt-0">
                    <flux:input id="forum_title" name="forum_title" wire:model="forum_title" required />
                    <flux:error name="forum_title" />
                </div>
            </div>
        </flux:card>

        <flux:separator />

        <flux:card>
            <flux:heading size="lg" accent>
                Database Settings
            </flux:heading>

            <div class="space-y-8 pb-12 sm:space-y-0 sm:divide-y sm:divide-zinc-800/15 sm:dark:divide-white/20 sm:pb-0">
                <div class="sm:grid sm:grid-cols-3 sm:items-center sm:gap-4 sm:py-6">
                    <flux:label for="database_name" :badge="__('Required')">
                        {{ __('Database') }}
                    </flux:label>
                    <div class="mt-2 sm:col-span-2 sm:mt-0">
                        <flux:input id="database_name" name="database_name" wire:model="database_name" required />
                        <flux:error name="database_name" />
                    </div>
                </div>

                <div class="sm:grid sm:grid-cols-3 sm:items-center sm:gap-4 sm:py-6">
                    <flux:label for="database_host" :badge="__('Required')">
                        {{ __('Host') }}
                    </flux:label>
                    <div class="mt-2 sm:col-span-2 sm:mt-0">
                        <flux:input id="database_host" name="database_host" wire:model="database_host" required />
                        <flux:error name="database_host" />
                    </div>
                </div>

                <div class="sm:grid sm:grid-cols-3 sm:items-center sm:gap-4 sm:py-6">
                    <flux:label for="database_username" :badge="__('Required')">
                        {{ __('Username') }}
                    </flux:label>
                    <div class="mt-2 sm:col-span-2 sm:mt-0">
                        <flux:input id="database_username" name="database_username" wire:model="database_username" required />
                        <flux:error name="database_username" />
                    </div>
                </div>

                <div class="sm:grid sm:grid-cols-3 sm:items-center sm:gap-4 sm:py-6">
                    <flux:label for="database_password" :badge="__('Optional')">
                        {{ __('Password') }}
                    </flux:label>
                    <div class="mt-2 sm:col-span-2 sm:mt-0">
                        <flux:input type="password" id="database_password" name="database_password" wire:model="database_password" :placeholder="__('Password')" viewable />
                        <flux:error name="database_name" />
                    </div>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <flux:heading size="lg" accent>
                Admin Account
            </flux:heading>

            <div class="space-y-8 pb-12 sm:space-y-0 sm:divide-y sm:divide-zinc-800/15 sm:dark:divide-white/20 sm:pb-0">
                <div class="sm:grid sm:grid-cols-3 sm:items-center sm:gap-4 sm:py-6">
                    <flux:label for="user_username" :badge="__('Required')">
                        {{ __('Username') }}
                    </flux:label>
                    <div class="mt-2 sm:col-span-2 sm:mt-0">
                        <flux:input id="user_username" name="user_username" wire:model="user_username" :placeholder="__('admin')" required />
                        <flux:error name="user_username" />
                    </div>
                </div>

                <div class="sm:grid sm:grid-cols-3 sm:items-center sm:gap-4 sm:py-6">
                    <flux:label for="user_email" :badge="__('Required')">
                        {{ __('Email') }}
                    </flux:label>
                    <div class="mt-2 sm:col-span-2 sm:mt-0">
                        <flux:input type="email" id="user_email" name="user_email" wire:model="user_email" :placeholder="__('admin@example.test')" required />
                        <flux:error name="user_email" />
                    </div>
                </div>

                <div class="sm:grid sm:grid-cols-3 sm:items-center sm:gap-4 sm:py-6">
                    <flux:label for="user_password" :badge="__('Required')">
                        {{ __('Password') }}
                    </flux:label>
                    <div class="mt-2 sm:col-span-2 sm:mt-0">
                        <flux:input type="password" id="user_password" name="user_password" wire:model="user_password" :placeholder="__('Password')" viewable required />
                        <flux:error name="user_password" />
                    </div>
                </div>

                <div class="sm:grid sm:grid-cols-3 sm:items-center sm:gap-4 sm:py-6">
                    <flux:label for="user_password_confirmation" :badge="__('Required')">
                        {{ __('Username') }}
                    </flux:label>
                    <div class="mt-2 sm:col-span-2 sm:mt-0">
                        <flux:input type="password" id="user_password_confirmation" name="user_password_confirmation" wire:model="user_password_confirmation" :placeholder="__('Password')" viewable required />
                        <flux:error name="user_password_confirmation" />
                    </div>
                </div>

            </div>
        </flux:card>

        <div class="flex items-center justify-center">
            <flux:button type="submit" icon="check" variant="primary" class="w-full">
                {{ __('Install Forum') }}
            </flux:button>
        </div>
    </flux:card>
</form>
