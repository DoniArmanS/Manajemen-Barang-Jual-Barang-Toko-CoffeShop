<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Nav -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
               <div class="relative shrink-0 flex items-center">
    <button id="adminDropdownButton"
            class="text-sm font-semibold inline-flex items-center text-gray-700 hover:text-gray-900 focus:outline-none"
            type="button"
            onclick="document.getElementById('adminDropdown').classList.toggle('hidden')">
        CoffeShop Admin
        <svg class="ms-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
                  d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.4a.75.75 0 01-1.08 0l-4.25-4.4a.75.75 0 01.02-1.06z"
                  clip-rule="evenodd" />
        </svg>
    </button>

    {{-- Dropdown menu --}}
    <div id="adminDropdown"
         class="hidden absolute right-0 top-full mt-2 w-40 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
        <div class="py-1">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-red-600">
                    <i class="ni ni-user-run me-2"></i> Logout
                </button>
            </form>
        </div>
    </div>
</div>

            </div>

            <!-- Right -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <!-- Dropdown: hanya Logout -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:text-gray-900 focus:outline-none">
                            {{ Auth::user()->name ?? 'User' }}
                            <svg class="ms-2 -me-0.5 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                      d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.4a.75.75 0 01-1.08 0l-4.25-4.4a.75.75 0 01.02-1.06z"
                                      clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link href="{{ route('logout') }}"
                                             onclick="event.preventDefault();this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Mobile menu button (optional) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:bg-gray-100 focus:outline-none">
                    ☰
                </button>
            </div>
        </div>
    </div>
</nav>

