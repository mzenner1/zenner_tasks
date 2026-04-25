<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information, email address, and avatar.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        {{-- Avatar --}}
        <div x-data="{
            preview: '{{ $user->avatar ? Storage::url($user->avatar) : '' }}',
            remove: false,
            pick(e) {
                const file = e.target.files[0];
                if (!file) return;
                this.preview = URL.createObjectURL(file);
                this.remove = false;
            },
            doRemove() {
                this.preview = '';
                this.remove = true;
                this.$refs.fileInput.value = '';
            }
        }">
            <x-input-label value="{{ __('Avatar') }}" />
            <div class="mt-2 flex items-center gap-5">
                <template x-if="preview">
                    <img :src="preview" alt="Avatar preview"
                         class="w-16 h-16 rounded-full object-cover ring-2 ring-indigo-300">
                </template>
                <template x-if="!preview">
                    <div class="w-16 h-16 rounded-full bg-indigo-500 flex items-center justify-center text-2xl font-bold text-white">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                </template>

                <div class="flex flex-col gap-2">
                    <label class="cursor-pointer inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12V4m0 0L8 8m4-4l4 4"/>
                        </svg>
                        {{ __('Upload photo') }}
                        <input x-ref="fileInput" type="file" name="avatar" accept="image/*" class="sr-only" @change="pick($event)">
                    </label>

                    @if($user->avatar)
                    <button type="button" @click="doRemove()" x-show="!remove"
                            class="text-xs text-red-500 hover:text-red-700 text-left transition">
                        {{ __('Remove photo') }}
                    </button>
                    <span x-show="remove" class="text-xs text-gray-400 italic">
                        {{ __('Photo will be removed on save.') }}
                    </span>
                    @endif
                </div>
            </div>

            <input type="hidden" name="remove_avatar" :value="remove ? '1' : '0'">
            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}
                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-gray-600">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
