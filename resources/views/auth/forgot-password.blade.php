<x-guest-layout>
    <h2 class="text-xl font-semibold text-gray-800 mb-2">Forgot your password?</h2>
    <p class="mb-6 text-sm text-gray-500">
        No problem. Enter your email address and we'll send you a password reset link.
    </p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button>
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>

        <div class="mt-6 pt-6 border-t border-gray-200 text-center text-sm text-gray-500">
            <a href="{{ route('login') }}" class="text-purple-600 hover:text-purple-800 underline font-medium">&larr; Back to login</a>
        </div>
    </form>
</x-guest-layout>
