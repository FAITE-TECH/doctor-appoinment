#!/bin/bash

# Script to add search functionality to all admin pages
# This script adds the search component, modal, CSS, and JavaScript to each admin page

ADMIN_DIR="/Applications/XAMPP/xamppfiles/htdocs/doctor-appoinment/Frontend/pages/admin"

# List of admin pages to update (excluding index.html which is the dashboard)
ADMIN_PAGES=(
    "appointments.html"
    "departments.html"
    "events.html"
    "gallery.html"
    "messages.html"
    "patient-tracking.html"
    "schedule-management.html"
    "services.html"
)

# CSS styles to add
CSS_STYLES='
    <style>
        .search-result-item {
            @apply border border-gray-200 rounded-lg p-4 mb-4 hover:shadow-md transition-shadow duration-200;
        }

        .search-result-item:hover {
            @apply border-blue-300;
        }

        .search-result-type {
            @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
        }

        .search-result-type.user {
            @apply bg-blue-100 text-blue-800;
        }

        .search-result-type.doctor {
            @apply bg-green-100 text-green-800;
        }

        .search-result-type.department {
            @apply bg-purple-100 text-purple-800;
        }

        .search-result-type.appointment {
            @apply bg-yellow-100 text-yellow-800;
        }

        .search-result-type.event {
            @apply bg-indigo-100 text-indigo-800;
        }

        .search-result-type.service {
            @apply bg-pink-100 text-pink-800;
        }

        .search-result-type.gallery {
            @apply bg-gray-100 text-gray-800;
        }

        .search-result-type.message {
            @apply bg-red-100 text-red-800;
        }

        .search-result-type.schedule {
            @apply bg-orange-100 text-orange-800;
        }

        .search-highlight {
            @apply bg-yellow-200 font-semibold;
        }

        .suggestion-item {
            @apply px-4 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0;
        }

        .suggestion-item:hover {
            @apply bg-blue-50;
        }
    </style>'

# Search component HTML
SEARCH_COMPONENT='
            <!-- Search Component -->
            <div id="searchContainer" class="mb-6">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex flex-col md:flex-row gap-4 items-center">
                        <!-- Search Input -->
                        <div class="flex-1 relative">
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="searchInput" 
                                    placeholder="Search by name, email, phone, or any relevant field..." 
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    autocomplete="off"
                                >
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <button 
                                    id="clearSearchBtn" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 hidden"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            
                            <!-- Search Suggestions Dropdown -->
                            <div id="searchSuggestions" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg hidden max-h-60 overflow-y-auto">
                                <!-- Suggestions will be populated here -->
                            </div>
                        </div>
                        
                        <!-- Search Type Filter -->
                        <div class="flex items-center gap-2">
                            <label for="searchType" class="text-sm font-medium text-gray-700">Search in:</label>
                            <select id="searchType" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">All</option>
                                <option value="users">Users</option>
                                <option value="doctors">Doctors</option>
                                <option value="departments">Departments</option>
                                <option value="appointments">Appointments</option>
                                <option value="events">Events</option>
                                <option value="services">Services</option>
                                <option value="gallery">Gallery</option>
                                <option value="messages">Messages</option>
                                <option value="schedules">Schedules</option>
                            </select>
                        </div>
                        
                        <!-- Search Button -->
                        <button 
                            id="searchBtn" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 flex items-center gap-2"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Search
                        </button>
                    </div>
                    
                    <!-- Search Results Summary -->
                    <div id="searchResultsSummary" class="mt-4 hidden">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-600">
                                <span id="searchResultsCount">0</span> results found for "<span id="searchQueryText"></span>"
                            </div>
                            <button 
                                id="clearAllSearchBtn" 
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                            >
                                Clear Search
                            </button>
                        </div>
                    </div>
                </div>
            </div>'

# Search modal HTML
SEARCH_MODAL='
    <!-- Search Results Modal -->
    <div id="searchResultsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-6xl mx-4 max-h-[90vh] overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Search Results</h3>
                <button id="closeSearchModal" class="text-gray-500 hover:text-gray-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
                <div id="searchResultsContent">
                    <!-- Search results will be populated here -->
                </div>
            </div>
        </div>
    </div>'

echo "Adding search functionality to admin pages..."

for page in "${ADMIN_PAGES[@]}"; do
    file_path="$ADMIN_DIR/$page"
    
    if [ -f "$file_path" ]; then
        echo "Processing $page..."
        
        # Create a backup
        cp "$file_path" "$file_path.backup"
        
        # Add CSS styles to head section (after existing stylesheets)
        sed -i '' '/<link rel="stylesheet" href="..\/..\/public\/css\/styles.css">/a\
'"$CSS_STYLES" "$file_path"
        
        # Add search component after the main content div starts
        sed -i '' '/<!-- Main Content -->/,/<div class="flex-1 p-8">/{
            /<div class="flex-1 p-8">/a\
'"$SEARCH_COMPONENT"
        }' "$file_path"
        
        # Add search modal before the closing body tag
        sed -i '' '/<\/div>$/{
            /<\/div>$/a\
'"$SEARCH_MODAL"
        }' "$file_path"
        
        # Add search.js script include
        sed -i '' '/<script src="\.\/js\/shared-auth\.js"><\/script>/a\
    <script src="\.\/js\/search\.js"><\/script>' "$file_path"
        
        echo "✓ Added search functionality to $page"
    else
        echo "✗ File not found: $page"
    fi
done

echo "Search functionality has been added to all admin pages!"
echo "Backup files have been created with .backup extension"
