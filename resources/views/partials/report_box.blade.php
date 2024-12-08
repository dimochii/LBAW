<div id="reportModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded shadow-lg w-96">
        <form action="{{ route('report', $user->id) }}" method="POST">
            @csrf
            <h2 class="text-xl font-semibold mb-4">Report {{ $user->name }}</h2>
            
            {{-- Reason Input --}}
            <label for="reason" class="block mb-2 text-sm font-medium">Reason for Reporting:</label>
            <textarea 
                id="reason" 
                name="reason" 
                rows="4" 
                class="w-full border border-gray-300 rounded p-2" 
                placeholder="Write your reason here..." 
                required></textarea>

            {{-- Hidden Report Type Input --}}
            <input type="hidden" name="report_type" value="{{ $reportType }}">

            {{-- Submit and Cancel Buttons --}}
            <div class="mt-4 flex justify-end gap-2">
                <button 
                    type="button" 
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
                    onclick="document.getElementById('reportModal').classList.add('hidden')">
                    Cancel
                </button>
                <button 
                    type="submit" 
                    class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                    Submit Report
                </button>
            </div>
        </form>
    </div>
</div>
