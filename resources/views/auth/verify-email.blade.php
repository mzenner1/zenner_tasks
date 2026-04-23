<x-guest-layout>
    <h2 class="text-xl font-semibold text-gray-800 mb-2">Verify your email</h2>
    <p class="mb-4 text-sm text-gray-500">
        Thanks for signing up! Before getting started, please verify your email address by clicking the link we sent you.
        If you didn't receive the email, we can send another.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ __('A new verification link has been sent to your email address.') }}
        </div>
    @endif

    <div class="mt-6 flex items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>
                {{ __('Resend Verification Email') }}
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 underline focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
