/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./development/js/qarr-plugin.js":
/*!***************************************!*\
  !*** ./development/js/qarr-plugin.js ***!
  \***************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

(function () {
  this.QarrPlugin = function () {
    this.qarrplugin = null;
    var defaults = {
      target: '#qarr-display-container'
    };

    if (arguments[0] && _typeof(arguments[0]) === "object") {
      this.options = extendDefaults(defaults, arguments[0]);
    }
  }; // Utility method to extend defaults


  function extendDefaults(source, properties) {
    var property;

    for (property in properties) {
      if (properties.hasOwnProperty(property)) {
        source[property] = properties[property];
      }
    }

    return source;
  }

  QarrPlugin.prototype.init = function () {
    // Initialize plugin methods
    new QarrTabs('#qarr-display-container'); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // Report Abuse
    // TODO: this needs to work for ajax pagination as well
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~

    $('.qarr-entry-ra-btn').on('click', function (e) {
      e.preventDefault();
      var that = $(this);
      var payload = {
        id: $(this).data('element-id'),
        type: $(this).data('type')
      };
      payload[QARR.csrfTokenName] = QARR.csrfTokenValue;
      $.post(QARR.actionUrl + 'qarr/elements/report-abuse', payload, function (response, textStatus, jqXHR) {
        if (response.success) {
          that.parent().html('<span>Reported</span>');
        }
      });
    }); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // Star Changer
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~

    $('.qarr-star:not(.static)').on('click', function () {
      $('.qarr-star').removeClass('selected');
      $('.qarr-star').removeClass('active');
      var rating = $(this).data('star-count');
      $(this).addClass('selected');
      $(this).prevAll().addClass('active');
      $('#qarr-rating-input').val(rating);
    }); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // Pagination
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~

    var qarrPaginations = $('.qarr-pagination');
    [].forEach.call(qarrPaginations, function (el) {
      new QarrPagination(el);
    }); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // Questions
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~

    $('.add-answer').on('click', function (e) {
      e.preventDefault();
      var payload = {
        target: $(this),
        questionId: $(this).data('id'),
        authorName: $(this).data('user-name'),
        authorId: $(this).data('user-id')
      };
      var answerHud = new QarrAnswerHud(payload);
    }); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // Star Filter
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~

    var qarrFilterItem = $('.qarr-filter-item');
    [].forEach.call(qarrFilterItem, function (el) {
      var payload = {
        target: $(el),
        type: $(el).data('type'),
        rating: $(el).data('rating'),
        total: $(el).data('total-entries')
      };
      new QarrStarFilterEntries(payload);
    }); // Sort Filter

    $('.qarr-entry-sort').on('change', function (e) {
      var $loader = $('.qarr-loader');
      var $container = $('#qarr-' + $(this).data('type') + '-container');
      var value = $(this).val();
      var type = $(this).data('type');
      $loader.addClass('active');
      $container.addClass('transition');
      var payload = {
        value: value,
        type: type,
        limit: QARR.limit,
        elementId: QARR.elementId
      };
      payload[QARR.csrfTokenName] = QARR.csrfTokenValue;
      $.post(QARR.actionUrl + 'qarr/elements/query-sort-elements', payload, function (response, textStatus, jqXHR) {
        if (response.success) {
          setTimeout(function () {
            $loader.removeClass('active');
            $container.html(response.template);
            var entrySetId = $(response.template).attr('id');
            var $entrySet = $('#' + entrySetId);
            $('html, body').animate({
              scrollTop: $entrySet.offset().top
            }, 'fast');
            $container.removeClass('transition'); // TODO: add dynamic pagination

            $('.qarr-pagination').hide();
          }, 1000); // Set localstorage for clicked star rating

          window.localStorage.setItem('qarr-sort-filter-type', type);
          window.localStorage.setItem('qarr-sort-filter-value', value);
        }
      });
    });
  };
})();

/***/ }),

/***/ "./development/scss/dashboard.scss":
/*!*****************************************!*\
  !*** ./development/scss/dashboard.scss ***!
  \*****************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./development/scss/elements/element-edit.scss":
/*!*****************************************************!*\
  !*** ./development/scss/elements/element-edit.scss ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./development/scss/elements/element-index.scss":
/*!******************************************************!*\
  !*** ./development/scss/elements/element-index.scss ***!
  \******************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./development/scss/elements/element-shared.scss":
/*!*******************************************************!*\
  !*** ./development/scss/elements/element-shared.scss ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./development/scss/elements/element-static.scss":
/*!*******************************************************!*\
  !*** ./development/scss/elements/element-static.scss ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./development/scss/frontend.scss":
/*!****************************************!*\
  !*** ./development/scss/frontend.scss ***!
  \****************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./development/scss/pages/configuration.scss":
/*!***************************************************!*\
  !*** ./development/scss/pages/configuration.scss ***!
  \***************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./development/scss/styles.scss":
/*!**************************************!*\
  !*** ./development/scss/styles.scss ***!
  \**************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./development/scss/ui.scss":
/*!**********************************!*\
  !*** ./development/scss/ui.scss ***!
  \**********************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./development/scss/widgets/widget-cp.scss":
/*!*************************************************!*\
  !*** ./development/scss/widgets/widget-cp.scss ***!
  \*************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./development/scss/widgets/widget-plugin.scss":
/*!*****************************************************!*\
  !*** ./development/scss/widgets/widget-plugin.scss ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./development/scss/widgets/widget-shared.scss":
/*!*****************************************************!*\
  !*** ./development/scss/widgets/widget-shared.scss ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 0:
/*!*********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** multi ./development/js/qarr-plugin.js ./development/scss/dashboard.scss ./development/scss/frontend.scss ./development/scss/ui.scss ./development/scss/styles.scss ./development/scss/widgets/widget-cp.scss ./development/scss/widgets/widget-plugin.scss ./development/scss/widgets/widget-shared.scss ./development/scss/elements/element-static.scss ./development/scss/elements/element-shared.scss ./development/scss/elements/element-index.scss ./development/scss/elements/element-edit.scss ./development/scss/pages/configuration.scss ***!
  \*********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! /Users/gonchav/QARR/plugins/qarr/development/js/qarr-plugin.js */"./development/js/qarr-plugin.js");
__webpack_require__(/*! /Users/gonchav/QARR/plugins/qarr/development/scss/dashboard.scss */"./development/scss/dashboard.scss");
__webpack_require__(/*! /Users/gonchav/QARR/plugins/qarr/development/scss/frontend.scss */"./development/scss/frontend.scss");
__webpack_require__(/*! /Users/gonchav/QARR/plugins/qarr/development/scss/ui.scss */"./development/scss/ui.scss");
__webpack_require__(/*! /Users/gonchav/QARR/plugins/qarr/development/scss/styles.scss */"./development/scss/styles.scss");
__webpack_require__(/*! /Users/gonchav/QARR/plugins/qarr/development/scss/widgets/widget-cp.scss */"./development/scss/widgets/widget-cp.scss");
__webpack_require__(/*! /Users/gonchav/QARR/plugins/qarr/development/scss/widgets/widget-plugin.scss */"./development/scss/widgets/widget-plugin.scss");
__webpack_require__(/*! /Users/gonchav/QARR/plugins/qarr/development/scss/widgets/widget-shared.scss */"./development/scss/widgets/widget-shared.scss");
__webpack_require__(/*! /Users/gonchav/QARR/plugins/qarr/development/scss/elements/element-static.scss */"./development/scss/elements/element-static.scss");
__webpack_require__(/*! /Users/gonchav/QARR/plugins/qarr/development/scss/elements/element-shared.scss */"./development/scss/elements/element-shared.scss");
__webpack_require__(/*! /Users/gonchav/QARR/plugins/qarr/development/scss/elements/element-index.scss */"./development/scss/elements/element-index.scss");
__webpack_require__(/*! /Users/gonchav/QARR/plugins/qarr/development/scss/elements/element-edit.scss */"./development/scss/elements/element-edit.scss");
module.exports = __webpack_require__(/*! /Users/gonchav/QARR/plugins/qarr/development/scss/pages/configuration.scss */"./development/scss/pages/configuration.scss");


/***/ })

/******/ });