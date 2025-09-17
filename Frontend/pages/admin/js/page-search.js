/**
 * Page-Specific Search Functionality
 * Provides search functionality specific to each admin page
 */

class PageSearch {
    constructor(pageType) {
        this.pageType = pageType;
        this.searchInput = null;
        this.searchBtn = null;
        this.clearSearchBtn = null;
        this.searchResultsContainer = null;
        this.searchResultsSummary = null;
        this.searchResultsCount = null;
        this.searchQueryText = null;
        this.clearAllSearchBtn = null;
        
        this.currentQuery = '';
        this.searchTimeout = null;
        this.originalContent = null;
        
        this.init();
    }
    
    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupElements());
        } else {
            this.setupElements();
        }
    }
    
    setupElements() {
        // Get all search elements
        this.searchInput = document.getElementById('pageSearchInput');
        this.searchBtn = document.getElementById('pageSearchBtn');
        this.clearSearchBtn = document.getElementById('clearPageSearchBtn');
        this.searchResultsContainer = document.getElementById('pageSearchResults');
        this.searchResultsSummary = document.getElementById('pageSearchResultsSummary');
        this.searchResultsCount = document.getElementById('pageSearchResultsCount');
        this.searchQueryText = document.getElementById('pageSearchQueryText');
        this.clearAllSearchBtn = document.getElementById('clearAllPageSearchBtn');
        
        if (!this.searchInput) {
            console.warn('Page search component not found on this page');
            return;
        }
        
        // Store original content
        this.storeOriginalContent();
        
        this.setupEventListeners();
    }
    
    storeOriginalContent() {
        // Store the original content of the main data container
        const mainContainer = document.querySelector('.main-data-container') || 
                             document.querySelector('table tbody') ||
                             document.querySelector('.data-container');
        
        if (mainContainer) {
            this.originalContent = mainContainer.innerHTML;
        }
    }
    
    setupEventListeners() {
        // Search input events
        this.searchInput.addEventListener('input', (e) => {
            this.handleSearchInput(e.target.value);
        });
        
        this.searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.performSearch();
            } else if (e.key === 'Escape') {
                this.clearSearch();
            }
        });
        
        // Search button
        this.searchBtn.addEventListener('click', () => {
            this.performSearch();
        });
        
        // Clear search button
        this.clearSearchBtn.addEventListener('click', () => {
            this.clearSearch();
        });
        
        // Clear all search button
        this.clearAllSearchBtn.addEventListener('click', () => {
            this.clearAllSearch();
        });
    }
    
    handleSearchInput(value) {
        const trimmedValue = value.trim();
        
        // Show/hide clear button
        if (trimmedValue.length > 0) {
            this.clearSearchBtn.classList.remove('hidden');
        } else {
            this.clearSearchBtn.classList.add('hidden');
        }
        
        // Clear previous timeout
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }
        
        // Auto-search after a short delay
        if (trimmedValue.length > 2) {
            this.searchTimeout = setTimeout(() => {
                this.performSearch();
            }, 500);
        } else if (trimmedValue.length === 0) {
            this.clearSearch();
        }
    }
    
    async performSearch() {
        const query = this.searchInput.value.trim();
        
        if (!query) {
            this.showNotification('Please enter a search term', 'warning');
            return;
        }
        
        this.currentQuery = query;
        
        // Show loading state
        this.searchBtn.disabled = true;
        this.searchBtn.innerHTML = `
            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Searching...
        `;
        
        try {
            const url = `/doctor-appoinment/Backend/api/page_search.php?action=search&q=${encodeURIComponent(query)}&page=${this.pageType}`;
            const response = await fetch(url, {
                credentials: 'include'
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    this.displaySearchResults(data);
                } else {
                    this.showNotification(data.error || 'Search failed', 'error');
                }
            } else {
                this.showNotification('Search request failed', 'error');
            }
        } catch (error) {
            console.error('Search error:', error);
            this.showNotification('Search failed. Please try again.', 'error');
        } finally {
            // Reset button state
            this.searchBtn.disabled = false;
            this.searchBtn.innerHTML = `
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Search
            `;
        }
    }
    
    displaySearchResults(data) {
        const { results, total_results, query } = data;
        
        // Update search results summary
        this.searchResultsCount.textContent = total_results;
        this.searchQueryText.textContent = query;
        this.searchResultsSummary.classList.remove('hidden');
        
        // Generate results HTML
        let resultsHtml = '';
        
        if (total_results === 0) {
            resultsHtml = `
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No results found</h3>
                    <p class="mt-1 text-sm text-gray-500">Try adjusting your search terms.</p>
                </div>
            `;
        } else {
            resultsHtml = this.generateResultsHTML(results, query);
        }
        
        this.searchResultsContainer.innerHTML = resultsHtml;
        this.searchResultsContainer.classList.remove('hidden');
        
        // Hide original content
        this.hideOriginalContent();
    }
    
    generateResultsHTML(results, query) {
        const pageLabels = {
            users: 'Users',
            doctors: 'Doctors',
            departments: 'Departments',
            appointments: 'Appointments',
            events: 'Events',
            services: 'Services',
            gallery: 'Gallery',
            messages: 'Messages',
            schedules: 'Schedules'
        };
        
        let html = `
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Search Results for ${pageLabels[this.pageType]}</h3>
                    <p class="text-sm text-gray-600">${results.length} result${results.length !== 1 ? 's' : ''} found</p>
                </div>
                <div class="divide-y divide-gray-200">
        `;
        
        results.forEach(item => {
            html += this.generateResultItem(item, query);
        });
        
        html += `
                </div>
            </div>
        `;
        
        return html;
    }
    
    generateResultItem(item, query) {
        switch (this.pageType) {
            case 'users':
                return `
                    <div class="px-6 py-4 hover:bg-gray-50 transition-colors duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-blue-600 font-semibold text-sm">
                                            ${item.name.charAt(0).toUpperCase()}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">${this.highlightText(item.name, query)}</div>
                                    <div class="text-sm text-gray-500">${this.highlightText(item.email, query)}</div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                    item.role === 'admin' ? 'bg-red-100 text-red-800' :
                                    item.role === 'doctor' ? 'bg-blue-100 text-blue-800' :
                                    'bg-green-100 text-green-800'
                                }">
                                    ${item.role.charAt(0).toUpperCase() + item.role.slice(1)}
                                </span>
                                <span class="text-xs text-gray-500">${new Date(item.created_at).toLocaleDateString()}</span>
                            </div>
                        </div>
                    </div>
                `;
                
            case 'doctors':
                return `
                    <div class="px-6 py-4 hover:bg-gray-50 transition-colors duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">${this.highlightText(item.name, query)}</div>
                                <div class="text-sm text-gray-500">${this.highlightText(item.email, query)}</div>
                                <div class="text-sm text-gray-600">Specialization: ${this.highlightText(item.specialization, query)}</div>
                                ${item.phone ? `<div class="text-sm text-gray-600">Phone: ${this.highlightText(item.phone, query)}</div>` : ''}
                                ${item.department ? `<div class="text-sm text-gray-600">Department: ${this.highlightText(item.department, query)}</div>` : ''}
                            </div>
                        </div>
                    </div>
                `;
                
            case 'departments':
                return `
                    <div class="px-6 py-4 hover:bg-gray-50 transition-colors duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">${this.highlightText(item.name, query)}</div>
                                <div class="text-sm text-gray-500">${this.highlightText(item.description, query)}</div>
                            </div>
                            <div class="text-xs text-gray-500">${new Date(item.created_at).toLocaleDateString()}</div>
                        </div>
                    </div>
                `;
                
            case 'appointments':
                return `
                    <div class="px-6 py-4 hover:bg-gray-50 transition-colors duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">${this.highlightText(item.patient_name, query)}</div>
                                <div class="text-sm text-gray-500">Email: ${this.highlightText(item.patient_email, query)}</div>
                                ${item.patient_phone ? `<div class="text-sm text-gray-500">Phone: ${this.highlightText(item.patient_phone, query)}</div>` : ''}
                                <div class="text-sm text-gray-600">Doctor: ${item.doctor_name || 'Not assigned'}</div>
                                <div class="text-sm text-gray-600">Date: ${new Date(item.appointment_date).toLocaleDateString()} at ${item.appointment_time}</div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                    item.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                    item.status === 'confirmed' ? 'bg-green-100 text-green-800' :
                                    item.status === 'cancelled' ? 'bg-red-100 text-red-800' :
                                    'bg-blue-100 text-blue-800'
                                }">
                                    ${item.status.charAt(0).toUpperCase() + item.status.slice(1)}
                                </span>
                            </div>
                        </div>
                    </div>
                `;
                
            case 'events':
                return `
                    <div class="px-6 py-4 hover:bg-gray-50 transition-colors duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">${this.highlightText(item.title, query)}</div>
                                <div class="text-sm text-gray-500">${this.highlightText(item.description, query)}</div>
                                ${item.location ? `<div class="text-sm text-gray-600">Location: ${this.highlightText(item.location, query)}</div>` : ''}
                                <div class="text-sm text-gray-600">Date: ${new Date(item.event_date).toLocaleDateString()} ${item.event_time ? `at ${item.event_time}` : ''}</div>
                            </div>
                        </div>
                    </div>
                `;
                
            case 'services':
                return `
                    <div class="px-6 py-4 hover:bg-gray-50 transition-colors duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">${this.highlightText(item.name, query)}</div>
                                <div class="text-sm text-gray-500">${this.highlightText(item.description, query)}</div>
                                <div class="text-sm text-gray-600">Price: $${item.price}</div>
                            </div>
                        </div>
                    </div>
                `;
                
            case 'gallery':
                return `
                    <div class="px-6 py-4 hover:bg-gray-50 transition-colors duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">${this.highlightText(item.title, query)}</div>
                                <div class="text-sm text-gray-500">${this.highlightText(item.description, query)}</div>
                                <div class="text-sm text-gray-600">Category: ${item.category}</div>
                            </div>
                        </div>
                    </div>
                `;
                
            case 'messages':
                return `
                    <div class="px-6 py-4 hover:bg-gray-50 transition-colors duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">${this.highlightText(item.name, query)}</div>
                                <div class="text-sm text-gray-500">Email: ${this.highlightText(item.email, query)}</div>
                                <div class="text-sm text-gray-600">Subject: ${this.highlightText(item.subject, query)}</div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                    item.status === 'read' ? 'bg-green-100 text-green-800' :
                                    item.status === 'unread' ? 'bg-yellow-100 text-yellow-800' :
                                    'bg-gray-100 text-gray-800'
                                }">
                                    ${item.status.charAt(0).toUpperCase() + item.status.slice(1)}
                                </span>
                                <span class="text-xs text-gray-500">${new Date(item.created_at).toLocaleDateString()}</span>
                            </div>
                        </div>
                    </div>
                `;
                
            case 'schedules':
                return `
                    <div class="px-6 py-4 hover:bg-gray-50 transition-colors duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">${item.doctor_name || 'Unknown Doctor'}</div>
                                <div class="text-sm text-gray-500">Date: ${new Date(item.date).toLocaleDateString()}</div>
                                <div class="text-sm text-gray-600">Time Slots: ${item.time_slots}</div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                    item.is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                }">
                                    ${item.is_available ? 'Available' : 'Unavailable'}
                                </span>
                            </div>
                        </div>
                    </div>
                `;
                
            default:
                return `<div class="px-6 py-4">${JSON.stringify(item)}</div>`;
        }
    }
    
    highlightText(text, query) {
        if (!query || !text) return text;
        
        const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return text.replace(regex, '<span class="bg-yellow-200 font-semibold">$1</span>');
    }
    
    hideOriginalContent() {
        // Hide the original content containers
        const containers = [
            document.querySelector('table tbody'),
            document.querySelector('.main-data-container'),
            document.querySelector('.data-container'),
            document.querySelector('#usersTable'),
            document.querySelector('#doctorsTable'),
            document.querySelector('#appointmentsTable')
        ];
        
        containers.forEach(container => {
            if (container) {
                container.style.display = 'none';
            }
        });
    }
    
    showOriginalContent() {
        // Show the original content containers
        const containers = [
            document.querySelector('table tbody'),
            document.querySelector('.main-data-container'),
            document.querySelector('.data-container'),
            document.querySelector('#usersTable'),
            document.querySelector('#doctorsTable'),
            document.querySelector('#appointmentsTable')
        ];
        
        containers.forEach(container => {
            if (container) {
                container.style.display = '';
            }
        });
    }
    
    clearSearch() {
        this.searchInput.value = '';
        this.clearSearchBtn.classList.add('hidden');
        this.searchResultsSummary.classList.add('hidden');
        this.searchResultsContainer.classList.add('hidden');
        this.showOriginalContent();
        this.currentQuery = '';
    }
    
    clearAllSearch() {
        this.clearSearch();
    }
    
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white ${
            type === 'error' ? 'bg-red-500' :
            type === 'warning' ? 'bg-yellow-500' :
            type === 'success' ? 'bg-green-500' :
            'bg-blue-500'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Remove notification after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
}

// Initialize page search functionality when DOM is ready
let pageSearch;
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        // Determine page type from URL or page title
        const path = window.location.pathname;
        let pageType = '';
        
        if (path.includes('users.html')) pageType = 'users';
        else if (path.includes('doctors.html')) pageType = 'doctors';
        else if (path.includes('departments.html')) pageType = 'departments';
        else if (path.includes('appointments.html')) pageType = 'appointments';
        else if (path.includes('events.html')) pageType = 'events';
        else if (path.includes('services.html')) pageType = 'services';
        else if (path.includes('gallery.html')) pageType = 'gallery';
        else if (path.includes('messages.html')) pageType = 'messages';
        else if (path.includes('schedule-management.html')) pageType = 'schedules';
        
        if (pageType) {
            pageSearch = new PageSearch(pageType);
        }
    });
} else {
    // Determine page type from URL or page title
    const path = window.location.pathname;
    let pageType = '';
    
    if (path.includes('users.html')) pageType = 'users';
    else if (path.includes('doctors.html')) pageType = 'doctors';
    else if (path.includes('departments.html')) pageType = 'departments';
    else if (path.includes('appointments.html')) pageType = 'appointments';
    else if (path.includes('events.html')) pageType = 'events';
    else if (path.includes('services.html')) pageType = 'services';
    else if (path.includes('gallery.html')) pageType = 'gallery';
    else if (path.includes('messages.html')) pageType = 'messages';
    else if (path.includes('schedule-management.html')) pageType = 'schedules';
    
    if (pageType) {
        pageSearch = new PageSearch(pageType);
    }
}
