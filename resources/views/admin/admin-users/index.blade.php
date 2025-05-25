{{-- resources/views/admin/admin-users/index.blade.php --}}
<x-admin-layout title="Administrators">

    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        {{-- Header --}}
        <div class="md:flex md:items-center md:justify-between md:space-x-4">
            <div class="min-w-0 flex-1">
                <h1 class="text-2xl font-semibold leading-7 text-gray-900 sm:truncate sm:tracking-tight">Administrators</h1>
            </div>
            <div class="flex">
                {{-- Add Create Button --}}
                <a href="{{ route('admin.admin-users.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    <x-heroicon-o-plus class="-ml-0.5 mr-1.5 h-5 w-5" />
                    Add Administrator
                </a>
            </div>
        </div>

        {{-- Session Messages --}}
        @include('admin.partials._session_messages')

        {{-- Filters (Optional for admins) --}}
        {{--
        <div class="bg-white shadow sm:rounded-lg p-4">
            <form action="{{ route('admin.admin-users.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Name or Email..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div class="flex items-end space-x-2 col-span-1 md:col-span-2 justify-end">
                    <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Filter</button>
                    <a href="{{ route('admin.admin-users.index') }}" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Clear</a>
                </div>
            </form>
        </div>
         --}}

        {{-- Admins Table Card --}}
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="overflow-x-auto">
                @if($admins->isEmpty())
                    <div class="p-6 text-center text-gray-500">
                        No administrators found.
                    </div>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($admins as $admin)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                     <a href="{{ route('admin.admin-users.edit', $admin) }}" class="text-indigo-600 hover:text-indigo-900">{{ $admin->name }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $admin->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $admin->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                    <a href="{{ route('admin.admin-users.edit', $admin) }}" class="text-indigo-600 hover:text-indigo-900" title="Edit Admin">Edit</a>

                                    {{-- Delete Form - Disable for self / last admin handled in controller --}}
                                    @if(Auth::id() !== $admin->id)
                                        <form action="{{ route('admin.admin-users.destroy', $admin) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this administrator? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Delete Admin">Delete</button>
                                        </form>
                                    @else
                                         <span class="text-gray-400 cursor-not-allowed" title="Cannot delete self">Delete</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Pagination Links --}}
                    @if ($admins->hasPages())
                        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                            {{ $admins->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>