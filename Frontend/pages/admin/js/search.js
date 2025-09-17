/**
 * Admin Search Functionality
 * Provides comprehensive search across all admin pages
 */

class AdminSearch {
    constructor() {
        this.searchInput = null;
        this.searchType = null;
        this.searchBtn = null;
        this.clearSearchBtn = null;
        this.clearAllSearchBtn = null;
        this.searchSuggestions = null;
        this.searchResultsModal = null;
        this.searchResultsContent = null;
        this.searchResultsSummary = null;
        this.searchResultsCount = null;
        this.searchQueryText = null;
        this.closeSearchModal = null;
        
        this.currentQuery = '';
        this.currentType = '';
        this.searchTimeout = null;
        this.suggestionsTimeout = null;
        
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
        this.searchInput = document.getElementById('searchInput');
        this.searchType = document.getElementById('searchType');
        this.searchBtn = document.getElementById('searchBtn');
        this.clearSearchBtn = document.getElementById('clearSearchBtn');
        this.clearAllSearchBtn = document.getElementById('clearAllSearchBtn');
        this.searchSuggestions = document.getElementById('searchSuggestions');
        this.searchResultsModal = document.getElementById('searchResultsModal');
        this.searchResultsContent = document.getElementById('searchResultsContent');
        this.searchResultsSummary = document.getElementById('searchResultsSummary');
        this.searchResultsCount = document.getElementById('searchResultsCount');
        this.searchQueryText = document.getElementById('searchQueryText');
        this.closeSearchModal = document.getElementById('closeSearchModal');
        
        if (!this.searchInput) {
            console.warn('Search component not found on this page');
            return;
        }
        
        this.setupEventListeners();
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
                this.hideSuggestions();
            }
        });
        
        this.searchInput.addEventListener('focus', () => {
            if (this.searchInput.value.length > 1) {
                this.showSuggestions();
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
        
        // Close modal button
        this.closeSearchModal.addEventListener('click', () => {
            this.hideSearchResults();
        });
        
        // Click outside to close suggestions
        document.addEventListener('click', (e) => {
            if (!e.target.closest('#searchContainer')) {
                this.hideSuggestions();
            }
        });
        
        // Search type change
        this.searchType.addEventListener('change', () => {
            if (this.searchInput.value.trim()) {
                this.performSearch();
            }
        });
    }
    
    handleSearchInput(value) {
        const trimmedValue = value.trim();
        
        // Show/hide clear button
        if (trimmedValue.length > 0) {
            this.clearSearchBtn.classList.remove('hidden');
        } else {
            this.clearSearchBtn.classList.add('hidden');
            this.hideSuggestions();
        }
        
        // Clear previous timeout
        if (this.suggestionsTimeout) {
            clearTimeout(this.suggestionsTimeout);
        }
        
        // Get suggestions after a short delay
        if (trimmedValue.length > 1) {
            this.suggestionsTimeout = setTimeout(() => {
                this.getSuggestions(trimmedValue);
            }, 300);
        } else {
            this.hideSuggestions();
        }
    }
    
    async getSuggestions(query) {
        try {
            const response = await fetch(`/doctor-appoinment/Backend/api/search.php?action=suggestions&q=${encodeURIComponent(query)}`, {
                credentials: 'include'
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    this.displaySuggestions(data.suggestions);
                }
            }
        } catch (error) {
            console.error('Failed to get suggestions:', error);
        }
    }
    
    displaySuggestions(suggestions) {
        if (suggestions.length === 0) {
            this.hideSuggestions();
            return;
        }
        
        const suggestionsHtml = suggestions.map(suggestion => `
            <div class="suggestion-item" data-text="${suggestion.text}" data-type="${suggestion.type}">
                <div class="flex items-center justify-between">
                    <span class="text-gray-900">${this.highlightText(suggestion.text, this.searchInput.value)}</span>
                    <span class="search-result-type ${suggestion.type}">${suggestion.type}</span>
                </div>
            </div>
        `).join('');
        
        this.searchSuggestions.innerHTML = suggestionsHtml;
        this.showSuggestions();
        
        // Add click handlers to suggestions
        this.searchSuggestions.querySelectorAll('.suggestion-item').forEach(item => {
            item.addEventListener('click', () => {
                const text = item.dataset.text;
                this.searchInput.value = text;
                this.hideSuggestions();
                this.performSearch();
            });
        });
    }
    
    showSuggestions() {
        this.searchSuggestions.classList.remove('hidden');
    }
    
    hideSuggestions() {
        this.searchSuggestions.classList.add('hidden');
    }
    
    async performSearch() {
        const query = this.searchInput.value.trim();
        const type = this.searchType.value;
        
        if (!query) {
            this.showNotification('Please enter a search term', 'warning');
            return;
        }
        
        this.currentQuery = query;
        this.currentType = type;
        
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
            const url = `/doctor-appoinment/Backend/api/search.php?action=search&q=${encodeURIComponent(query)}${type ? `&type=${type}` : ''}`;
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
                    <p class="mt-1 text-sm text-gray-500">Try adjusting your search terms or filters.</p>
                </div>
            `;
        } else {
            // Group results by type
            Object.entries(results).forEach(([type, items]) => {
                if (items.length > 0) {
                    resultsHtml += this.generateResultsSection(type, items, query);
                }
            });
        }
        
        this.searchResultsContent.innerHTML = resultsHtml;
        this.showSearchResults();
    }
    
    generateResultsSection(type, items, query) {
        const typeLabels = {
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
        
        const sectionHtml = `
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <span class="search-result-type ${type} mr-2">${typeLabels[type] || type}</span>
                    <span class="text-sm text-gray-500">(${items.length} result${items.length !== 1 ? 's' : ''})</span>
                </h3>
                <div class="space-y-4">
                    ${items.map(item => this.generateResultItem(item, query)).join('')}
                </div>
            </div>
        `;
        
        return sectionHtml;
    }
    
    generateResultItem(item, query) {
        const baseHtml = `
            <div class="search-result-item">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        ${this.generateItemContent(item, query)}
                    </div>
                    <div class="ml-4">
                        <span class="search-result-type ${item.type}">${item.type}</span>
                    </div>
                </div>
            </div>
        `;
        
        return baseHtml;
    }
    
    generateItemContent(item, query) {
        switch (item.type) {
            case 'user':
                return `
                    <h4 class="font-medium text-gray-900">${this.highlightText(item.name, query)}</h4>
                    <p class="text-sm text-gray-600">${this.highlightText(item.email, query)}</p>
                    <p class="text-xs text-gray-500">Role: ${item.role} • Joined: ${new Date(item.created_at).toLocaleDateString()}</p>
                `;
                
            case 'doctor':
                return `
                    <h4 class="font-medium text-gray-900">${this.highlightText(item.name, query)}</h4>
                    <p class="text-sm text-gray-600">${this.highlightText(item.email, query)}</p>
                    <p class="text-sm text-gray-600">Specialization: ${this.highlightText(item.specialization, query)}</p>
                    ${item.phone ? `<p class="text-sm text-gray-600">Phone: ${this.highlightText(item.phone, query)}</p>` : ''}
                    ${item.department ? `<p class="text-sm text-gray-600">Department: ${this.highlightText(item.department, query)}</p>` : ''}
                `;
                
            case 'department':
                return `
                    <h4 class="font-medium text-gray-900">${this.highlightText(item.name, query)}</h4>
                    <p class="text-sm text-gray-600">${this.highlightText(item.description, query)}</p>
                    <p class="text-xs text-gray-500">Created: ${new Date(item.created_at).toLocaleDateString()}</p>
                `;
                
            case 'appointment':
                return `
                    <h4 class="font-medium text-gray-900">${this.highlightText(item.patient_name, query)}</h4>
                    <p class="text-sm text-gray-600">Email: ${this.highlightText(item.patient_email, query)}</p>
                    ${item.patient_phone ? `<p class="text-sm text-gray-600">Phone: ${this.highlightText(item.patient_phone, query)}</p>` : ''}
                    <p class="text-sm text-gray-600">Doctor: ${item.doctor_name || 'Not assigned'}</p>
                    <p class="text-sm text-gray-600">Date: ${new Date(item.appointment_date).toLocaleDateString()} at ${item.appointment_time}</p>
                    <p class="text-sm text-gray-600">Status: <span class="font-medium">${item.status}</span></p>
                `;
                
            case 'event':
                return `
                    <h4 class="font-medium text-gray-900">${this.highlightText(item.title, query)}</h4>
                    <p class="text-sm text-gray-600">${this.highlightText(item.description, query)}</p>
                    ${item.location ? `<p class="text-sm text-gray-600">Location: ${this.highlightText(item.location, query)}</p>` : ''}
                    <p class="text-sm text-gray-600">Date: ${new Date(item.event_date).toLocaleDateString()} ${item.event_time ? `at ${item.event_time}` : ''}</p>
                `;
                
            case 'service':
                return `
                    <h4 class="font-medium text-gray-900">${this.highlightText(item.name, query)}</h4>
                    <p class="text-sm text-gray-600">${this.highlightText(item.description, query)}</p>
                    <p class="text-sm text-gray-600">Price: $${item.price}</p>
                `;
                
            case 'gallery':
                return `
                    <h4 class="font-medium text-gray-900">${this.highlightText(item.title, query)}</h4>
                    <p class="text-sm text-gray-600">${this.highlightText(item.description, query)}</p>
                    <p class="text-sm text-gray-600">Category: ${item.category}</p>
                `;
                
            case 'message':
                return `
                    <h4 class="font-medium text-gray-900">${this.highlightText(item.name, query)}</h4>
                    <p class="text-sm text-gray-600">Email: ${this.highlightText(item.email, query)}</p>
                    <p class="text-sm text-gray-600">Subject: ${this.highlightText(item.subject, query)}</p>
                    <p class="text-sm text-gray-600">Status: <span class="font-medium">${item.status}</span></p>
                    <p class="text-xs text-gray-500">Received: ${new Date(item.created_at).toLocaleDateString()}</p>
                `;
                
            case 'schedule':
                return `
                    <h4 class="font-medium text-gray-900">${item.doctor_name || 'Unknown Doctor'}</h4>
                    <p class="text-sm text-gray-600">Date: ${new Date(item.date).toLocaleDateString()}</p>
                    <p class="text-sm text-gray-600">Time Slots: ${item.time_slots}</p>
                    <p class="text-sm text-gray-600">Available: ${item.is_available ? 'Yes' : 'No'}</p>
                `;
                
            default:
                return `<h4 class="font-medium text-gray-900">${JSON.stringify(item)}</h4>`;
        }
    }
    
    highlightText(text, query) {
        if (!query || !text) return text;
        
        const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return text.replace(regex, '<span class="search-highlight">$1</span>');
    }
    
    showSearchResults() {
        this.searchResultsModal.classList.remove('hidden');
        this.searchResultsModal.classList.add('flex');
    }
    
    hideSearchResults() {
        this.searchResultsModal.classList.add('hidden');
        this.searchResultsModal.classList.remove('flex');
    }
    
    clearSearch() {
        this.searchInput.value = '';
        this.clearSearchBtn.classList.add('hidden');
        this.hideSuggestions();
        this.searchInput.focus();
    }
    
    clearAllSearch() {
        this.clearSearch();
        this.searchResultsSummary.classList.add('hidden');
        this.hideSearchResults();
        this.currentQuery = '';
        this.currentType = '';
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

// Initialize search functionality when DOM is ready
let adminSearch;
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        adminSearch = new AdminSearch();
    });
} else {
    adminSearch = new AdminSearch();
}
