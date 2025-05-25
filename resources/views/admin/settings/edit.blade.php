{{-- resources/views/admin/settings/edit.blade.php --}}
<x-admin-layout title="Store Settings">

    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6 max-w-3xl mx-auto"> {{-- Constrain width --}}
        {{-- Header --}}
        <div class="md:flex md:items-center md:justify-between md:space-x-4">
            <div class="min-w-0 flex-1">
                <h1 class="text-2xl font-semibold leading-7 text-gray-900 sm:truncate sm:tracking-tight">Store Settings</h1>
            </div>
            {{-- No "Add Setting" button here, settings are pre-defined or managed via migrations/seeders --}}
        </div>

        {{-- Session Messages --}}
        @include('admin.partials._session_messages')

        {{-- Settings Form --}}
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            @if($settingsGrouped->isEmpty())
                <div class="bg-white shadow sm:rounded-lg p-6 text-center text-gray-500">
                    No settings found. Please run migrations and seeders if necessary.
                </div>
            @else
                @foreach($settingsGrouped as $groupName => $settingsInGroup)
                    <div class="bg-white shadow sm:rounded-lg mb-6">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <h2 class="text-lg leading-6 font-medium text-gray-900">{{ ucfirst($groupName) }} Settings</h2>
                        </div>
                        <div class="px-4 py-5 sm:p-6 space-y-6">
                            @foreach($settingsInGroup as $setting)
                                <div>
                                    <x-input-label :for="'setting-'.$setting->key" :value="$setting->label" />
                                    @if($setting->description)
                                        <p class="mt-1 text-xs text-gray-500">{{ $setting->description }}</p>
                                    @endif

                                    @switch($setting->type)
                                        @case('text')
                                            <textarea id="setting-{{ $setting->key }}"
                                                      name="settings[{{ $setting->key }}]"
                                                      rows="3"
                                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            >{{ old('settings.'.$setting->key, $setting->value) }}</textarea>
                                            @break

                                        @case('boolean')
                                            <div class="mt-2">
                                                <label for="setting-{{ $setting->key }}" class="inline-flex items-center">
                                                    <input id="setting-{{ $setting->key }}"
                                                           name="settings[{{ $setting->key }}]"
                                                           type="hidden" value="0"> {{-- Hidden input for unchecked state --}}
                                                    <input id="setting-{{ $setting->key }}-checkbox"
                                                           name="settings[{{ $setting->key }}]"
                                                           type="checkbox" value="1"
                                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                           {{ old('settings.'.$setting->key, (bool) $setting->value) ? 'checked' : '' }}>
                                                    <span class="ml-2 text-sm text-gray-600">Enable</span>
                                                </label>
                                            </div>
                                            @break

                                        @case('number')
                                        @case('integer')
                                        @case('float')
                                        @case('decimal')
                                            <x-text-input id="setting-{{ $setting->key }}"
                                                          name="settings[{{ $setting->key }}]"
                                                          type="number"
                                                          step="{{ in_array($setting->type, ['float', 'decimal']) ? '0.01' : '1' }}"
                                                          class="mt-1 block w-full"
                                                          :value="old('settings.'.$setting->key, $setting->value)" />
                                            @break

                                        @case('string')
                                        @default
                                            <x-text-input id="setting-{{ $setting->key }}"
                                                          name="settings[{{ $setting->key }}]"
                                                          type="text"
                                                          class="mt-1 block w-full"
                                                          :value="old('settings.'.$setting->key, $setting->value)" />
                                    @endswitch
                                    {{-- Display validation error for this specific setting key --}}
                                    <x-input-error class="mt-2" :messages="$errors->get('settings.'.$setting->key)" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                {{-- Action Buttons --}}
                <div class="bg-gray-50 px-4 py-3 sm:px-6 text-right rounded-b-lg">
                    <x-primary-button type="submit">
                        {{ __('Save Settings') }}
                    </x-primary-button>
                </div>
            @endif
        </form>
    </div>
</x-admin-layout>