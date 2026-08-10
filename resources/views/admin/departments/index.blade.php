@extends('layouts.app')

@section('title', 'Departments')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Departments</h1>
            <p class="mt-1 text-sm text-gray-500">Manage company departments.</p>
        </div>
        @can('manage_users')
        <button onclick="document.getElementById('createModal').classList.remove('hidden')" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"><i class="fa-solid fa-plus mr-1"></i>Add Department</button>
        @endcan
    </div>

    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-400 p-4 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Positions</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Postings</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($departments as $department)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $department->code }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $department->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $department->description ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $department->job_positions_count }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $department->job_postings_count }}</td>
                    <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                        @can('manage_users')
                        <button onclick="editDept({{ $department->id }}, '{{ $department->name }}', '{{ $department->code }}', '{{ $department->description }}')" class="text-amber-600 hover:text-amber-500">Edit</button>
                        <form method="POST" action="{{ route('admin.departments.destroy', $department) }}" class="inline" onsubmit="return confirm('Delete this department?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-500">Delete</button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-4 text-sm text-gray-500 text-center">No departments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Create Modal -->
    <div id="createModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Department</h3>
            <form method="POST" action="{{ route('admin.departments.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Code</label>
                    <input type="text" name="code" maxlength="10" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" rows="2" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Cancel</button>
                    <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Department</h3>
            <form method="POST" action="" id="editForm" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" id="editName" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Code</label>
                    <input type="text" name="code" id="editCode" maxlength="10" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="editDescription" rows="2" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Cancel</button>
                    <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editDept(id, name, code, description) {
    document.getElementById('editForm').action = '/admin/departments/' + id;
    document.getElementById('editName').value = name;
    document.getElementById('editCode').value = code;
    document.getElementById('editDescription').value = description || '';
    document.getElementById('editModal').classList.remove('hidden');
}
</script>
@endsection
