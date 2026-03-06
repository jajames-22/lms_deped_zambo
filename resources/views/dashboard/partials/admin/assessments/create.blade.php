<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS Student Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>


<div class="p-6 max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Create New Assessment</h2>
        <p class="text-gray-500 mb-6 text-sm">Step 1: Set up the basic details. An access key will be generated automatically.</p>

        <form id="setupForm" onsubmit="submitSetup(event)">
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Assessment Title</label>
                <input type="text" id="title" required placeholder="e.g., Midterm Examination" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Year / Grade Level</label>
                <input type="text" id="year_level" required placeholder="e.g., Grade 10" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Instructions / Description</label>
                <textarea id="description" rows="4" placeholder="General instructions for the students..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#a52a2a]/50 focus:border-[#a52a2a] outline-none transition"></textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" 
                    class="px-6 py-3 bg-[#a52a2a] text-white font-semibold rounded-xl hover:opacity-90 transition shadow-lg shadow-[#a52a2a]/30">
                    Generate Key & Proceed to Builder <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    async function submitSetup(e) {
        e.preventDefault();
        
        const payload = {
            title: document.getElementById('title').value,
            year_level: document.getElementById('year_level').value,
            description: document.getElementById('description').value
        };

        try {
            const response = await fetch("{{ route('dashboard.assessments.store_setup') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();
            if (data.success) {
                // Uses your dashboard's partial loader function to go to page 2 seamlessly
                loadPartial(data.redirect_url, document.querySelector('.nav-btn.active')); 
            } else {
                alert('Error creating assessment.');
            }
        } catch (error) {
            console.error(error);
        }
    }
</script>

</body>
</html>