// Browser Compatibility Polyfills and Fixes
// This file ensures cross-browser compatibility for all modern and legacy browsers

(function() {
  'use strict';

  // Polyfill for older browsers that don't support fetch API
  if (!window.fetch) {
    // Simple XMLHttpRequest-based fetch polyfill
    window.fetch = function(url, options) {
      return new Promise(function(resolve, reject) {
        var xhr = new XMLHttpRequest();
        var method = (options && options.method) || 'GET';
        var headers = (options && options.headers) || {};
        var body = (options && options.body) || null;

        xhr.open(method, url, true);
        
        // Set headers
        for (var key in headers) {
          if (headers.hasOwnProperty(key)) {
            xhr.setRequestHeader(key, headers[key]);
          }
        }

        xhr.onreadystatechange = function() {
          if (xhr.readyState === 4) {
            if (xhr.status >= 200 && xhr.status < 300) {
              resolve({
                ok: true,
                status: xhr.status,
                text: function() { return Promise.resolve(xhr.responseText); },
                json: function() { return Promise.resolve(JSON.parse(xhr.responseText)); }
              });
            } else {
              reject(new Error('HTTP ' + xhr.status + ': ' + xhr.statusText));
            }
          }
        };

        xhr.onerror = function() {
          reject(new Error('Network error'));
        };

        xhr.send(body);
      });
    };
  }

  // Polyfill for Promise (needed for fetch polyfill and other modern features)
  if (!window.Promise) {
    window.Promise = function(executor) {
      var self = this;
      self.state = 'pending';
      self.value = undefined;
      self.handlers = [];

      function resolve(result) {
        if (self.state === 'pending') {
          self.state = 'fulfilled';
          self.value = result;
          self.handlers.forEach(handle);
          self.handlers = null;
        }
      }

      function reject(error) {
        if (self.state === 'pending') {
          self.state = 'rejected';
          self.value = error;
          self.handlers.forEach(handle);
          self.handlers = null;
        }
      }

      function handle(handler) {
        if (self.state === 'pending') {
          self.handlers.push(handler);
        } else {
          if ((self.state === 'fulfilled' && handler.onFulfilled) ||
              (self.state === 'rejected' && handler.onRejected)) {
            var fn = self.state === 'fulfilled' ? handler.onFulfilled : handler.onRejected;
            try {
              var result = fn(self.value);
              handler.resolve(result);
            } catch (ex) {
              handler.reject(ex);
            }
          } else {
            var action = self.state === 'fulfilled' ? handler.resolve : handler.reject;
            action(self.value);
          }
        }
      }

      this.then = function(onFulfilled, onRejected) {
        return new Promise(function(resolve, reject) {
          handle({
            onFulfilled: onFulfilled,
            onRejected: onRejected,
            resolve: resolve,
            reject: reject
          });
        });
      };

      this.catch = function(onRejected) {
        return this.then(null, onRejected);
      };

      try {
        executor(resolve, reject);
      } catch (ex) {
        reject(ex);
      }
    };

    Promise.resolve = function(value) {
      return new Promise(function(resolve) {
        resolve(value);
      });
    };

    Promise.reject = function(reason) {
      return new Promise(function(resolve, reject) {
        reject(reason);
      });
    };
  }

  // Polyfill for Array.prototype.forEach (IE8 and below)
  if (!Array.prototype.forEach) {
    Array.prototype.forEach = function(callback, thisArg) {
      var T, k;
      if (this == null) {
        throw new TypeError('this is null or not defined');
      }
      var O = Object(this);
      var len = parseInt(O.length) || 0;
      if (typeof callback !== "function") {
        throw new TypeError(callback + ' is not a function');
      }
      if (arguments.length > 1) {
        T = thisArg;
      }
      k = 0;
      while (k < len) {
        var kValue;
        if (k in O) {
          kValue = O[k];
          callback.call(T, kValue, k, O);
        }
        k++;
      }
    };
  }

  // Polyfill for querySelectorAll (IE7 and below)
  if (!document.querySelectorAll) {
    document.querySelectorAll = function(selector) {
      var style = document.createElement('style');
      var elements = [];
      var element;
      
      document.documentElement.firstChild.appendChild(style);
      document._qsa = [];

      style.styleSheet.cssText = selector + '{x-qsa:expression(document._qsa && document._qsa.push(this))}';
      window.scrollBy(0, 0);
      style.parentNode.removeChild(style);

      while (document._qsa.length) {
        element = document._qsa.shift();
        element.style.removeAttribute('x-qsa');
        elements.push(element);
      }
      document._qsa = null;
      return elements;
    };
  }

  // Polyfill for querySelector
  if (!document.querySelector) {
    document.querySelector = function(selector) {
      var elements = document.querySelectorAll(selector);
      return elements.length ? elements[0] : null;
    };
  }

  // Polyfill for addEventListener (IE8 and below)
  if (!Element.prototype.addEventListener) {
    Element.prototype.addEventListener = function(event, callback) {
      var element = this;
      element.attachEvent('on' + event, function(e) {
        e.target = e.srcElement;
        e.currentTarget = element;
        e.preventDefault = function() {
          e.returnValue = false;
        };
        e.stopPropagation = function() {
          e.cancelBubble = true;
        };
        callback.call(element, e);
      });
    };
  }

  // Add classList support for older browsers
  // Provide a getter that returns an object bound to each element so
  // native element methods aren't called with the wrong `this` context.
  if (!('classList' in Element.prototype)) {
    Object.defineProperty(Element.prototype, 'classList', {
      configurable: true,
      enumerable: true,
      get: function() {
        var el = this;
        function getClasses() {
          return (el.className || '').trim().split(/\s+/).filter(Boolean);
        }

        return {
          add: function(className) {
            if (!className) return;
            var classes = getClasses();
            if (classes.indexOf(className) === -1) {
              classes.push(className);
              el.className = classes.join(' ');
            }
          },
          remove: function(className) {
            if (!className) return;
            var classes = getClasses();
            var idx;
            while ((idx = classes.indexOf(className)) !== -1) {
              classes.splice(idx, 1);
            }
            el.className = classes.join(' ');
          },
          contains: function(className) {
            if (!className) return false;
            var classes = getClasses();
            return classes.indexOf(className) !== -1;
          },
          toggle: function(className) {
            if (this.contains(className)) {
              this.remove(className);
              return false;
            } else {
              this.add(className);
              return true;
            }
          }
        };
      }
    });
  }

  // Console polyfill for older browsers
  if (!window.console) {
    window.console = {
      log: function() {},
      error: function() {},
      warn: function() {},
      info: function() {}
    };
  }

  // Add JSON support for really old browsers
  if (!window.JSON) {
    window.JSON = {
      parse: function(str) {
        return eval('(' + str + ')');
      },
      stringify: function(obj) {
        var t = typeof(obj);
        if (t != "object" || obj === null) {
          if (t == "string") obj = '"' + obj + '"';
          return String(obj);
        } else {
          var n, v, json = [], arr = (obj && obj.constructor == Array);
          for (n in obj) {
            v = obj[n];
            t = typeof(v);
            if (t == "string") v = '"' + v + '"';
            else if (t == "object" && v !== null) v = JSON.stringify(v);
            json.push((arr ? "" : '"' + n + '":') + String(v));
          }
          return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
        }
      }
    };
  }

  // Fix for older Safari and Chrome date parsing
  (function() {
    var oldParse = Date.parse;
    Date.parse = function(str) {
      var timestamp = oldParse(str);
      if (isNaN(timestamp) && str) {
        // Try to parse ISO 8601 format manually for older browsers
        var match = str.match(/^(\d{4})-(\d{2})-(\d{2})(?:[T ](\d{2}):(\d{2}):(\d{2})(?:\.(\d{3}))?(?:Z|([+-])(\d{2}):?(\d{2})))?$/);
        if (match) {
          var year = parseInt(match[1], 10);
          var month = parseInt(match[2], 10) - 1; // months are 0-based
          var day = parseInt(match[3], 10);
          var hour = parseInt(match[4] || 0, 10);
          var minute = parseInt(match[5] || 0, 10);
          var second = parseInt(match[6] || 0, 10);
          var ms = parseInt(match[7] || 0, 10);
          
          var date = new Date(Date.UTC(year, month, day, hour, minute, second, ms));
          
          // Handle timezone offset
          if (match[8]) {
            var offsetSign = match[8] === '+' ? 1 : -1;
            var offsetHours = parseInt(match[9], 10);
            var offsetMinutes = parseInt(match[10] || 0, 10);
            var offsetMs = offsetSign * (offsetHours * 60 + offsetMinutes) * 60 * 1000;
            date = new Date(date.getTime() - offsetMs);
          }
          
          return date.getTime();
        }
      }
      return timestamp;
    };
  })();

  // Ensure proper CSS3 support detection
  window.Modernizr = window.Modernizr || {};
  
  // Simple feature detection
  function testCSSProperty(property) {
    var testEl = document.createElement('div');
    var prefixes = ['', '-webkit-', '-moz-', '-ms-', '-o-'];
    
    for (var i = 0; i < prefixes.length; i++) {
      try {
        testEl.style[prefixes[i] + property] = 'test';
        if (testEl.style[prefixes[i] + property] === 'test') {
          return prefixes[i] + property;
        }
      } catch (e) {
        // Property not supported
      }
    }
    return false;
  }

  // Store supported CSS properties
  window.supportedCSS = {
    transform: testCSSProperty('transform'),
    transition: testCSSProperty('transition'),
    borderRadius: testCSSProperty('border-radius'),
    boxShadow: testCSSProperty('box-shadow'),
    gradient: testCSSProperty('background-image')
  };

})();