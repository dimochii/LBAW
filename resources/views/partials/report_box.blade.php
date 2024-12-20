<div id="reportModal" class="hidden fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="relative bg-white p-6 rounded shadow-lg w-96">
        <form id="reportForm" method="POST" action="">
            @csrf
            <h2 id="reportTitle" class="text-xl font-semibold mb-4">Report</h2>
            
            {{-- Reason Input --}}
            <label for="reason" class="block mb-2 text-sm font-medium">Reason for Reporting:</label>
            <textarea 
                id="reason" 
                name="reason" 
                rows="4" 
                class="w-full border border-gray-300 rounded p-2" 
                placeholder="Write your reason here..." 
                required></textarea>

            {{-- Hidden Inputs for Report Details --}}
            <input type="hidden" id="report_type" name="report_type">
            <input type="hidden" id="reported_id" name="reported_id" value="{{ $reported_id }}">

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
<script>
    // Para mostrar o modal
document.getElementById('reportModal').classList.remove('hidden');

// Para esconder o modal
document.getElementById('reportModal').classList.add('hidden');
</script>