<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Full Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <div style="position: relative;">
                <x-text-input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="new-password" />
                <i class="fas fa-eye toggle-password text-gray-400" style="position: absolute; top: 50%; right: 12px; transform: translateY(-50%); cursor: pointer;"></i>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <div style="position: relative;">
                <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />
                <i class="fas fa-eye toggle-password text-gray-400" style="position: absolute; top: 50%; right: 12px; transform: translateY(-50%); cursor: pointer;"></i>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>
        
        <hr class="my-4">

        <div class="mt-4">
            <x-input-label for="legal_name" :value="__('Business Legal Name')" />
            <x-text-input id="legal_name" class="block mt-1 w-full" type="text" name="legal_name" :value="old('legal_name')" required />
            <x-input-error :messages="$errors->get('legal_name')" class="mt-2" />
        </div>
        
        <div class="mt-4">
            <x-input-label for="registration_number" :value="__('Registration / CNIC No.')" />
            <x-text-input id="registration_number" class="block mt-1 w-full" type="text" name="registration_number" :value="old('registration_number')" required />
            <x-input-error :messages="$errors->get('registration_number')" class="mt-2" />
        </div>


        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>