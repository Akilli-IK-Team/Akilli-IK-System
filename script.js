// script.js
// Frontend Logic and AJAX Operations

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const jobList = document.getElementById('jobList');

    // Fetch jobs when the page loads
    fetchJobs('');

    // Fetch jobs as the user types in the search box (Debounce could be added, kept simple)
    searchInput.addEventListener('input', function(e) {
        const query = e.target.value;
        fetchJobs(query);
    });

    // Function to send requests to the Backend (PHP) API
    function fetchJobs(searchQuery) {
        // Create the API URL
        const url = `api_jobs.php?search=${encodeURIComponent(searchQuery)}`;

        // Fetch data using the Fetch API (modern AJAX method)
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network error: Data could not be retrieved.');
                }
                return response.json();
            })
            .then(data => {
                renderJobs(data);
            })
            .catch(error => {
                console.error("Fetch Error:", error);
                jobList.innerHTML = `<div class="col-span-full text-center text-red-500 font-semibold py-8">An error occurred while loading jobs or there is no Database connection.</div>`;
            });
    }

    // Function to convert received JSON data into HTML and inject it into the DOM
    function renderJobs(jobs) {
        // Show message if no results are found
        if (jobs.length === 0) {
            jobList.innerHTML = `<div class="col-span-full text-center text-gray-500 py-8">No job listings found matching your criteria.</div>`;
            return;
        }

        // Clear existing HTML content
        jobList.innerHTML = '';

        // Create an HTML card for each job listing
        jobs.forEach(job => {
            // Convert skills into HTML badges
            let skillsHtml = '';
            if (job.skills && job.skills.length > 0) {
                job.skills.forEach(skill => {
                    skillsHtml += `<span class="bg-gray-100 text-gray-700 text-xs font-medium px-2.5 py-1 rounded-md border border-gray-200">${skill}</span>`;
                });
            } else {
                skillsHtml = `<span class="text-xs text-gray-400">Not specified</span>`;
            }

            // Card HTML structure
            const card = document.createElement('div');
            card.className = "job-card group border rounded-xl p-6 bg-white border-gray-100 hover:border-brand-300 relative overflow-hidden";
            
            card.innerHTML = `
                <div class="absolute top-0 right-0 bg-brand-100 text-brand-700 text-xs font-bold px-3 py-1 rounded-bl-lg">%${job.match_score} Match</div>
                
                <h3 class="font-bold text-xl text-gray-900 mt-2 group-hover:text-brand-700 transition">${job.title}</h3>
                <p class="text-gray-500 text-sm mt-1 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    ${job.company_name}
                </p>
                
                <div class="mt-5 flex flex-wrap gap-2">
                    ${skillsHtml}
                </div>
                
                <button class="w-full mt-6 bg-white text-brand-600 border-2 border-brand-600 font-semibold py-2.5 rounded-lg group-hover:bg-brand-600 group-hover:text-white transition duration-300">
                    Apply Now
                </button>
            `;
            
            jobList.appendChild(card);
        });
    }
});
