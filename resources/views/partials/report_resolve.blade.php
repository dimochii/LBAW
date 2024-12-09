<div id="resolveModal" class="hidden fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded shadow-lg w-96">
        <form id="resolveForm" method="POST" action="">
            @csrf
            @method('PATCH') 
            <h2 class="text-xl font-semibold mb-4">Are you sure you want to resolve this report?</h2>
            <p class="text-sm text-gray-600 mb-4">Once resolved, the report will be closed and cannot be reopened.</p>

            <input type="hidden" id="report_id" name="report_id">

            <div class="mt-4 flex justify-end gap-2">
                <button 
                    type="button" 
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
                    onclick="closeResolveModal()">
                    Cancel
                </button>
                <button 
                    type="submit" 
                    class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                    Resolve Report
                </button>
            </div>
        </form>
    </div>
</div>
