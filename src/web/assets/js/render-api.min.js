/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!**************************************!*\
  !*** ./development/js/render-api.js ***!
  \**************************************/
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
$.extend(QRAPI, {
  getActionUrl: function getActionUrl(path, params) {
    return QRAPI.getUrl(path, params, QRAPI.actionUrl);
  },
  escapeChars: function escapeChars(chars) {
    if (!Garnish.isArray(chars)) {
      chars = chars.split();
    }
    var escaped = '';
    for (var i = 0; i < chars.length; i++) {
      escaped += "\\" + chars[i];
    }
    return escaped;
  },
  ltrim: function ltrim(str, chars) {
    if (!str) {
      return str;
    }
    if (typeof chars === 'undefined') {
      chars = ' \t\n\r\0\x0B';
    }
    var re = new RegExp('^[' + QRAPI.escapeChars(chars) + ']+');
    return str.replace(re, '');
  },
  rtrim: function rtrim(str, chars) {
    if (!str) {
      return str;
    }
    if (typeof chars === 'undefined') {
      chars = ' \t\n\r\0\x0B';
    }
    var re = new RegExp('[' + QRAPI.escapeChars(chars) + ']+$');
    return str.replace(re, '');
  },
  trim: function trim(str, chars) {
    str = QRAPI.ltrim(str, chars);
    str = QRAPI.rtrim(str, chars);
    return str;
  },
  getUrl: function getUrl(path, params, baseUrl) {
    if (typeof path !== 'string') {
      path = '';
    }

    // Normalize the params
    var anchor = '';
    if ($.isPlainObject(params)) {
      var aParams = [];
      for (var name in params) {
        if (!params.hasOwnProperty(name)) {
          continue;
        }
        var value = params[name];
        if (name === '#') {
          anchor = value;
        } else if (value !== null && value !== '') {
          aParams.push(name + '=' + value);
        }
      }
      params = aParams;
    }
    if (Garnish.isArray(params)) {
      params = params.join('&');
    } else {
      params = QRAPI.trim(params, '&?');
    }

    // Was there already an anchor on the path?
    var apos = path.indexOf('#');
    if (apos !== -1) {
      // Only keep it if the params didn't specify a new anchor
      if (!anchor) {
        anchor = path.substr(apos + 1);
      }
      path = path.substr(0, apos);
    }

    // Were there already any query string params in the path?
    var qpos = path.indexOf('?');
    if (qpos !== -1) {
      params = path.substr(qpos + 1) + (params ? '&' + params : '');
      path = path.substr(0, qpos);
    }

    // Return path if it appears to be an absolute URL.
    if (path.search('://') !== -1 || path[0] === '/') {
      return path + (params ? '?' + params : '') + (anchor ? '#' + anchor : '');
    }
    path = QRAPI.trim(path, '/');

    // Put it all together
    var url;
    if (baseUrl) {
      url = baseUrl;
      if (path && QRAPI.pathParam) {
        // Does baseUrl already contain a path?
        var pathMatch = url.match(new RegExp('[&\?]' + QRAPI.escapeRegex(QRAPI.pathParam) + '=[^&]+'));
        if (pathMatch) {
          url = url.replace(pathMatch[0], QRAPI.rtrim(pathMatch[0], '/') + '/' + path);
          path = '';
        }
      }
    } else {
      url = QRAPI.baseUrl;
    }

    // Does the base URL already have a query string?
    qpos = url.indexOf('?');
    if (qpos !== -1) {
      params = url.substr(qpos + 1) + (params ? '&' + params : '');
      url = url.substr(0, qpos);
    }
    if (!QRAPI.omitScriptNameInUrls && path) {
      if (QRAPI.usePathInfo || !QRAPI.pathParam) {
        // Make sure that the script name is in the URL
        if (url.search(QRAPI.scriptName) === -1) {
          url = QRAPI.rtrim(url, '/') + '/' + QRAPI.scriptName;
        }
      } else {
        // Move the path into the query string params

        // Is the path param already set?
        if (params && params.substr(0, QRAPI.pathParam.length + 1) === QRAPI.pathParam + '=') {
          var basePath,
            endPath = params.indexOf('&');
          if (endPath !== -1) {
            basePath = params.substring(2, endPath);
            params = params.substr(endPath + 1);
          } else {
            basePath = params.substr(2);
            params = null;
          }

          // Just in case
          basePath = QRAPI.rtrim(basePath);
          path = basePath + (path ? '/' + path : '');
        }

        // Now move the path into the params
        params = QRAPI.pathParam + '=' + path + (params ? '&' + params : '');
        path = null;
      }
    }
    if (path) {
      url = QRAPI.rtrim(url, '/') + '/' + path;
    }
    if (params) {
      url += '?' + params;
    }
    if (anchor) {
      url += '#' + anchor;
    }
    return url;
  },
  escapeRegex: function escapeRegex(str) {
    return str.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&');
  },
  sendActionRequest: function sendActionRequest(method, action, options) {
    var _this = this;
    return new Promise(function (resolve, reject) {
      options = options ? $.extend({}, options) : {};
      options.method = method;
      options.url = QRAPI.actionUrl + action;
      options.headers = $.extend({
        'X-Requested-With': 'XMLHttpRequest'
      }, options.headers || {}, _this._actionHeaders());
      options.params = $.extend({}, options.params || {}, {
        // Force Safari to not load from cache
        v: new Date().getTime()
      });
      axios.request(options).then(resolve)["catch"](reject);
    });
  },
  postActionRequest: function postActionRequest(action, data, callback, options) {
    // Make 'data' optional
    if (typeof data === 'function') {
      options = callback;
      callback = data;
      data = {};
    }
    options = options || {};
    if (options.contentType && options.contentType.match(/\bjson\b/)) {
      if (_typeof(data) === 'object') {
        data = JSON.stringify(data);
      }
      options.contentType = 'application/json; charset=utf-8';
    }
    var jqXHR = $.ajax($.extend({
      url: QRAPI.getActionUrl(action),
      type: 'POST',
      dataType: 'json',
      headers: this._actionHeaders(),
      data: data,
      success: callback,
      error: function error(jqXHR, textStatus, errorThrown) {
        // Ignore incomplete requests, likely due to navigating away from the page
        // h/t https://stackoverflow.com/a/22107079/1688568
        if (jqXHR.readyState !== 4) {
          return;
        }
        alert('A server error occurred.');
        if (callback) {
          callback(null, textStatus, jqXHR);
        }
      }
    }, options));

    // Call the 'send' callback
    if (typeof options.send === 'function') {
      options.send(jqXHR);
    }
    return jqXHR;
  },
  _actionHeaders: function _actionHeaders() {
    var headers = {};
    if (QRAPI.csrfTokenValue) {
      headers['X-CSRF-Token'] = QRAPI.csrfTokenValue;
    }
    return headers;
  }
});
/******/ })()
;