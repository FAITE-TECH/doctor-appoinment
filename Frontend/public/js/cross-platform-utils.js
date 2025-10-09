/**
 * Cross-Platform Utilities for Doctor Appointment System
 * Provides Safari-specific and cross-browser compatibility utilities
 */

(function() {
    'use strict';
    
    // Safari-specific utilities
    window.SafariUtils = {
        // Check if running on Safari
        isSafari: function() {
            const userAgent = navigator.userAgent;
            return /Safari/.test(userAgent) && !/Chrome/.test(userAgent) && !/Edge/.test(userAgent);
        },
        
        // Check Safari version
        getSafariVersion: function() {
            if (!this.isSafari()) return null;
            const match = navigator.userAgent.match(/Version\/(\d+(?:\.\d+)*)/);
            return match ? match[1] : null;
        },
        
        // Safari-compatible fetch wrapper
        safeFetch: function(url, options) {
            options = options || {};
            
            // Safari-specific fetch options
            const safariOptions = Object.assign({
                method: 'GET',
                cache: 'no-cache',
                headers: {
                    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache'
                }
            }, options);
            
            // Add timestamp to prevent Safari caching issues
            const separator = url.includes('?') ? '&' : '?';
            const timestampedUrl = url + separator + 'v=' + Date.now();
            
            return fetch(timestampedUrl, safariOptions)
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error('HTTP error! status: ' + response.status + ' - ' + response.statusText);
                    }
                    return response;
                })
                .catch(function(error) {
                    console.warn('Fetch error for', url, ':', error.message);
                    throw error;
                });
        },
        
        // Safari-compatible DOM ready
        domReady: function(callback) {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', callback);
            } else {
                // DOM is already ready, execute callback with a small delay
                setTimeout(callback, 10);
            }
        }
    };

  // Get the application base path dynamically
  function getBasePath() {
    var path = window.location.pathname;
    var segments = path.split('/');
    var doctorAppIndex = -1;
    
    // Find the doctor-appoinment segment
    for (var i = 0; i < segments.length; i++) {
      if (segments[i] === 'doctor-appoinment' || segments[i] === 'doctor-appointment') {
        doctorAppIndex = i;
        break;
      }
    }
    
    if (doctorAppIndex !== -1) {
      return segments.slice(0, doctorAppIndex + 1).join('/');
    }
    
    // Fallback: try to detect from current location
    if (path.indexOf('/Frontend/') !== -1) {
      return path.substring(0, path.indexOf('/Frontend/'));
    }
    
    return '';
  }

  // If a global APP_API_FOLDER exists (set by main script), prefer it. Otherwise, leave getBasePath usage unchanged.
  if (!window.APP_API_FOLDER) {
    // Provide a lightweight BASE_URL fallback that matches the project's rules
    (function(){
      var host = window.location.hostname || '';
      var root = getBasePath();
      if (host === 'localhost' || host === '127.0.0.1') {
        if (root) {
          window.APP_API_FOLDER = window.location.origin + (root || '') + '/Backend/api/';
        } else {
          // Fallback to the actual repo folder name used here
          window.APP_API_FOLDER = window.location.origin + '/doctor-appoinment/Backend/api/';
        }
      } else if (host.indexOf('spchospital.com') !== -1) {
        window.APP_API_FOLDER = 'https://spchospital.com/Backend/api/';
      } else {
        window.APP_API_FOLDER = window.location.origin + (root || '') + '/Backend/api/';
      }
    })();
  }

  // Normalize file paths for cross-platform compatibility
  function normalizePath(path) {
    if (!path) return '';
    
    // Replace backslashes with forward slashes
    path = path.replace(/\\/g, '/');
    
    // Remove duplicate slashes
    path = path.replace(/\/+/g, '/');
    
    // Remove leading slash if present to make it relative
    if (path.charAt(0) === '/') {
      path = path.substring(1);
    }
    
    return path;
  }

    // Cross-platform utilities
    window.CrossPlatformUtils = window.CrossPlatformUtils || {
        // Detect mobile/touch devices
        isMobile: function() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        },
        
        // Detect iOS specifically
        isIOS: function() {
            return /iPad|iPhone|iPod/.test(navigator.userAgent);
        },
        
        // Cross-browser event listener
        addEventListenerSafe: function(element, event, handler, options) {
            if (element.addEventListener) {
                element.addEventListener(event, handler, options);
            } else if (element.attachEvent) {
                // IE8 and below
                element.attachEvent('on' + event, handler);
            } else {
                // Very old browsers
                element['on' + event] = handler;
            }
        },
        
        // Debounce function for performance
        debounce: function(func, wait, immediate) {
            var timeout;
            return function() {
                var context = this, args = arguments;
                var later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                var callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        }
    };

  // Build API URL that works across different setups
  function buildApiUrl(endpoint) {
    // Prefer globally defined APP_API_FOLDER (canonical API folder root)
    var base = (window.APP_API_FOLDER || (function(){ var bp = getBasePath(); return window.location.origin + (bp || '') + '/Backend/api/'; })());
    var cleanEndpoint = normalizePath(endpoint);
    if (cleanEndpoint.charAt(0) === '/') {
      cleanEndpoint = cleanEndpoint.substring(1);
    }
    return base + cleanEndpoint;
  }

  // Build upload URL that works across different setups
  function buildUploadUrl(uploadPath) {
    var basePath = getBasePath();
    var cleanPath = normalizePath(uploadPath);
    
    // Remove leading slash from path if present
    if (cleanPath.charAt(0) === '/') {
      cleanPath = cleanPath.substring(1);
    }
    
    return basePath + '/uploads/' + cleanPath;
  }

  // Expose upload builder globally for pages to use (host-agnostic)
  window.buildUploadUrl = window.buildUploadUrl || buildUploadUrl;

  // Build asset URL for frontend assets
  function buildAssetUrl(assetPath) {
    var currentPath = window.location.pathname;
    var cleanAssetPath = normalizePath(assetPath);
    
    // Determine how many levels up we need to go based on current location
    var levelsUp = 0;
    if (currentPath.indexOf('/pages/admin/') !== -1) {
      levelsUp = 2; // admin pages are 2 levels deep
    } else if (currentPath.indexOf('/pages/') !== -1) {
      levelsUp = 1; // regular pages are 1 level deep
    }
    
    var prefix = '';
    for (var i = 0; i < levelsUp; i++) {
      prefix += '../';
    }
    
    return prefix + 'public/' + cleanAssetPath;
  }

  // Detect browser and OS for specific compatibility handling
  function getBrowserInfo() {
    var ua = navigator.userAgent;
    var browser = 'unknown';
    var version = 0;
    var os = 'unknown';
    
    // Detect OS
    if (ua.indexOf('Windows') !== -1) os = 'windows';
    else if (ua.indexOf('Mac') !== -1) os = 'mac';
    else if (ua.indexOf('Linux') !== -1) os = 'linux';
    else if (ua.indexOf('Android') !== -1) os = 'android';
    else if (ua.indexOf('iOS') !== -1 || ua.indexOf('iPhone') !== -1 || ua.indexOf('iPad') !== -1) os = 'ios';
    
    // Detect browser
    if (ua.indexOf('Firefox') !== -1) {
      browser = 'firefox';
      version = parseFloat(ua.substring(ua.indexOf('Firefox/') + 8));
    } else if (ua.indexOf('Chrome') !== -1) {
      browser = 'chrome';
      version = parseFloat(ua.substring(ua.indexOf('Chrome/') + 7));
    } else if (ua.indexOf('Safari') !== -1 && ua.indexOf('Chrome') === -1) {
      browser = 'safari';
      version = parseFloat(ua.substring(ua.indexOf('Version/') + 8));
    } else if (ua.indexOf('Edge') !== -1) {
      browser = 'edge';
      version = parseFloat(ua.substring(ua.indexOf('Edge/') + 5));
    } else if (ua.indexOf('MSIE') !== -1 || ua.indexOf('Trident') !== -1) {
      browser = 'ie';
      if (ua.indexOf('MSIE') !== -1) {
        version = parseFloat(ua.substring(ua.indexOf('MSIE ') + 5));
      } else {
        version = 11; // IE11 uses Trident instead of MSIE
      }
    }
    
    return {
      browser: browser,
      version: version,
      os: os,
      isOldBrowser: (browser === 'ie' && version < 11) || 
                   (browser === 'chrome' && version < 60) ||
                   (browser === 'firefox' && version < 55) ||
                   (browser === 'safari' && version < 10)
    };
  }

  // Get appropriate MIME type for file extension
  function getMimeType(filename) {
    if (!filename) return 'application/octet-stream';
    
    var ext = filename.toLowerCase().split('.').pop();
    var mimeTypes = {
      'jpg': 'image/jpeg',
      'jpeg': 'image/jpeg',
      'png': 'image/png',
      'gif': 'image/gif',
      'webp': 'image/webp',
      'svg': 'image/svg+xml',
      'pdf': 'application/pdf',
      'doc': 'application/msword',
      'docx': 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'txt': 'text/plain',
      'json': 'application/json',
      'xml': 'application/xml',
      'html': 'text/html',
      'css': 'text/css',
      'js': 'application/javascript'
    };
    
    return mimeTypes[ext] || 'application/octet-stream';
  }

  // Check if file path exists (for client-side validation)
  function validateImagePath(imagePath, callback) {
    if (!imagePath) {
      callback(false);
      return;
    }
    
    var img = new Image();
    img.onload = function() {
      callback(true);
    };
    img.onerror = function() {
      callback(false);
    };
    img.src = imagePath;
  }

  // Format date in a cross-browser compatible way
  function formatDate(dateString, format) {
    if (!dateString) return 'TBA';
    
    var date;
    try {
      // Try to parse the date
      date = new Date(dateString);
      
      // Check if date is valid
      if (isNaN(date.getTime())) {
        // Try alternative parsing for different formats
        if (dateString.indexOf('-') !== -1) {
          var parts = dateString.split('-');
          if (parts.length === 3) {
            date = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
          }
        }
      }
      
      if (isNaN(date.getTime())) {
        return dateString; // Return original if we can't parse it
      }
      
    } catch (e) {
      return dateString;
    }
    
    // Format the date based on the requested format
    format = format || 'default';
    
    var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                  'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    
    switch (format) {
      case 'short':
        return months[date.getMonth()] + ' ' + date.getDate() + ', ' + date.getFullYear();
      case 'long':
        return date.toLocaleDateString();
      case 'iso':
        return date.getFullYear() + '-' + 
               String(date.getMonth() + 1).padStart(2, '0') + '-' + 
               String(date.getDate()).padStart(2, '0');
      default:
        return months[date.getMonth()] + ' ' + date.getDate();
    }
  }

    // Enhanced navigation loader for Safari
    window.SafariNavigationLoader = {
        loadNavigation: function(targetId, options) {
            options = options || {};
            const navUrl = options.url || 'shared-nav.html';
            const fallbackHtml = options.fallback || 
                '<header class="bg-white shadow-md w-full"><div class="max-w-6xl mx-auto flex items-center justify-between px-4 py-3"><a href="index.html" class="flex items-center space-x-2"><img src="../public/assets/logo.png" alt="SPC Hospital Logo" class="h-8 w-auto" /><span class="font-bold text-blue-600">SPC Hospital</span></a><nav class="hidden md:flex items-center space-x-6 text-gray-600"><a href="home.html">Home</a><a href="doctors.html">Doctors</a><a href="departments.html">Departments</a><a href="services.html">Services</a><a href="gallery.html">Gallery</a><a href="events.html">Events</a><a href="contact.html">Contact</a></nav></div></header>';
            
            return SafariUtils.safeFetch(navUrl).then(function(response) {
                return response.text();
            }).then(function(data) {
                document.getElementById(targetId).innerHTML = data;
                setTimeout(function() {
                    if (window.initNavigationAfterLoad) {
                        window.initNavigationAfterLoad();
                    }
                }, 100);
            }).catch(function(error) {
                console.warn('Navigation loading failed, using fallback');
                document.getElementById(targetId).innerHTML = fallbackHtml;
            });
        },
        
        loadFooter: function(targetId, options) {
            options = options || {};
            const footerUrl = options.url || 'footer.html';
            const fallbackHtml = options.fallback || 
                '<footer class="bg-gray-700 text-white py-6 mt-auto"><div class="container mx-auto px-4 text-center"><p>&copy; 2025 SPC Hospital. All rights reserved.</p></div></footer>';
            
            return SafariUtils.safeFetch(footerUrl).then(function(response) {
                return response.text();
            }).then(function(data) {
                document.getElementById(targetId).innerHTML = data;
                // Ensure proper page structure
                document.body.style.minHeight = '100vh';
                document.body.style.display = 'flex';
                document.body.style.flexDirection = 'column';
            }).catch(function(error) {
                console.warn('Footer loading failed, using fallback');
                document.getElementById(targetId).innerHTML = fallbackHtml;
            });
        }
    };

  // Expose our utilities globally - merge with existing CrossPlatformUtils
  window.CrossPlatformUtils = Object.assign(window.CrossPlatformUtils || {}, {
    getBasePath: getBasePath,
    normalizePath: normalizePath,
    buildApiUrl: buildApiUrl,
    buildUploadUrl: buildUploadUrl,
    buildAssetUrl: buildAssetUrl,
    getBrowserInfo: getBrowserInfo,
    getMimeType: getMimeType,
    validateImagePath: validateImagePath,
    formatDate: formatDate
  });

  // Also expose individual functions for convenience
  window.buildApiUrl = buildApiUrl;
  window.buildUploadUrl = buildUploadUrl;
  window.buildAssetUrl = buildAssetUrl;

    // Initialize Safari utilities when DOM is ready
    SafariUtils.domReady(function() {
        console.log('Cross-platform utilities initialized', {
            isSafari: SafariUtils.isSafari(),
            safariVersion: SafariUtils.getSafariVersion(),
            isMobile: CrossPlatformUtils.isMobile(),
            isIOS: CrossPlatformUtils.isIOS()
        });
    });

})();