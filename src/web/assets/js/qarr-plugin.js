/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./development/js/qarr-plugin.js":
/*!***************************************!*\
  !*** ./development/js/qarr-plugin.js ***!
  \***************************************/
/***/ (() => {

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

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
          that.parent().html("<span>Reported!</span>");
        }
      });
    }); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // Star Changer
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~

    $('.qarr-star:not(.static)').on('click', function () {
      var $qarrStarEl = $('.qarr-star');
      $qarrStarEl.removeClass('selected');
      $qarrStarEl.removeClass('active');
      var rating = $(this).data('star-count');
      $(this).addClass('selected');
      $(this).prevAll().addClass('active');
      $('#qarr-rating-input').val(rating);
    }); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // Pagination
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // Initialized from within the twig templates
    // qarr/templates/frontend/_includes/pagination.twig
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
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
      new QarrStarFilterEntries(el);
    }); // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // Sort Order
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~

    var qarrSortOrder = $('.qarr-entry-sort');
    [].forEach.call(qarrSortOrder, function (el) {
      new QarrSortOrderEntries(el);
    });
  };
})();

/***/ }),

/***/ "./development/scss/dashboard.scss":
/*!*****************************************!*\
  !*** ./development/scss/dashboard.scss ***!
  \*****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./development/scss/frontend.scss":
/*!****************************************!*\
  !*** ./development/scss/frontend.scss ***!
  \****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./development/scss/ui.scss":
/*!**********************************!*\
  !*** ./development/scss/ui.scss ***!
  \**********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./development/scss/widgets.scss":
/*!***************************************!*\
  !*** ./development/scss/widgets.scss ***!
  \***************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		if(__webpack_module_cache__[moduleId]) {
/******/ 			return __webpack_module_cache__[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/******/ 	// the startup function
/******/ 	// It's empty as some runtime module handles the default behavior
/******/ 	__webpack_require__.x = x => {}
/************************************************************************/
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => Object.prototype.hasOwnProperty.call(obj, prop)
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// Promise = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"/release/src/web/assets/js/qarr-plugin": 0
/******/ 		};
/******/ 		
/******/ 		var deferredModules = [
/******/ 			["./development/js/qarr-plugin.js"],
/******/ 			["./development/scss/dashboard.scss"],
/******/ 			["./development/scss/frontend.scss"],
/******/ 			["./development/scss/ui.scss"],
/******/ 			["./development/scss/widgets.scss"]
/******/ 		];
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		var checkDeferredModules = x => {};
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime, executeModules] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0, resolves = [];
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					resolves.push(installedChunks[chunkId][0]);
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			for(moduleId in moreModules) {
/******/ 				if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 					__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 				}
/******/ 			}
/******/ 			if(runtime) runtime(__webpack_require__);
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			while(resolves.length) {
/******/ 				resolves.shift()();
/******/ 			}
/******/ 		
/******/ 			// add entry modules from loaded chunk to deferred list
/******/ 			if(executeModules) deferredModules.push.apply(deferredModules, executeModules);
/******/ 		
/******/ 			// run deferred modules when all chunks ready
/******/ 			return checkDeferredModules();
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunk"] = self["webpackChunk"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 		
/******/ 		function checkDeferredModulesImpl() {
/******/ 			var result;
/******/ 			for(var i = 0; i < deferredModules.length; i++) {
/******/ 				var deferredModule = deferredModules[i];
/******/ 				var fulfilled = true;
/******/ 				for(var j = 1; j < deferredModule.length; j++) {
/******/ 					var depId = deferredModule[j];
/******/ 					if(installedChunks[depId] !== 0) fulfilled = false;
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferredModules.splice(i--, 1);
/******/ 					result = __webpack_require__(__webpack_require__.s = deferredModule[0]);
/******/ 				}
/******/ 			}
/******/ 			if(deferredModules.length === 0) {
/******/ 				__webpack_require__.x();
/******/ 				__webpack_require__.x = x => {};
/******/ 			}
/******/ 			return result;
/******/ 		}
/******/ 		var startup = __webpack_require__.x;
/******/ 		__webpack_require__.x = () => {
/******/ 			// reset startup function so it can be called again when more startup code is added
/******/ 			__webpack_require__.x = startup || (x => {});
/******/ 			return (checkDeferredModules = checkDeferredModulesImpl)();
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	// run startup
/******/ 	return __webpack_require__.x();
/******/ })()
;