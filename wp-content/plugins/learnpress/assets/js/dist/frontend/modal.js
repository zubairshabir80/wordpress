this["LP"] = this["LP"] || {}; this["LP"]["modal"] =
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
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "../../../Clouds/Thimpress/Plugins/github.com/learnpress_v4_doing/learnpress/assets/src/apps/js/frontend/modal.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "../../../Clouds/Thimpress/Plugins/github.com/learnpress_v4_doing/learnpress/assets/src/apps/js/frontend/modal.js":
/*!*******************************************************************************************************************************!*\
  !*** E:/Work/Webs/WP/Clouds/Thimpress/Plugins/github.com/learnpress_v4_doing/learnpress/assets/src/apps/js/frontend/modal.js ***!
  \*******************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _modal_index__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./modal/index */ "../../../Clouds/Thimpress/Plugins/github.com/learnpress_v4_doing/learnpress/assets/src/apps/js/frontend/modal/index.js");

/* harmony default export */ __webpack_exports__["default"] = (_modal_index__WEBPACK_IMPORTED_MODULE_0__["default"]);

/***/ }),

/***/ "../../../Clouds/Thimpress/Plugins/github.com/learnpress_v4_doing/learnpress/assets/src/apps/js/frontend/modal/index.js":
/*!*************************************************************************************************************************************!*\
  !*** E:/Work/Webs/WP/Clouds/Thimpress/Plugins/github.com/learnpress_v4_doing/learnpress/assets/src/apps/js/frontend/modal/index.js ***!
  \*************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./store */ "../../../Clouds/Thimpress/Plugins/github.com/learnpress_v4_doing/learnpress/assets/src/apps/js/frontend/modal/store/index.js");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_2__);
/**
 * Create Modal popup.
 * Edit: Use React Hook.
 *
 * @author Nhamdv - ThimPress.
 */




var Modal = function Modal(_ref) {
  var children = _ref.children;

  var _dispatch = Object(_wordpress_data__WEBPACK_IMPORTED_MODULE_2__["dispatch"])('learnpress/modal'),
      show = _dispatch.show,
      hide = _dispatch.hide,
      confirm = _dispatch.confirm;

  var isShow = Object(_wordpress_data__WEBPACK_IMPORTED_MODULE_2__["useSelect"])(function (select) {
    var isOpen = select('learnpress/modal').isOpen();
    return isOpen;
  });
  var message = Object(_wordpress_data__WEBPACK_IMPORTED_MODULE_2__["useSelect"])(function (select) {
    var getMessage = select('learnpress/modal').getMessage();
    return getMessage;
  });

  var dataConfirm = function dataConfirm(c) {
    return function (event) {
      confirm(c);
    };
  };

  var styles = {
    display: isShow ? 'block' : 'none'
  };
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    id: "lp-modal-overlay",
    style: styles
  }), /*#__PURE__*/React.createElement("div", {
    id: "lp-modal-window",
    style: styles
  }, /*#__PURE__*/React.createElement("div", {
    id: "lp-modal-content",
    dangerouslySetInnerHTML: {
      __html: message
    }
  }), /*#__PURE__*/React.createElement("div", {
    id: "lp-modal-buttons"
  }, /*#__PURE__*/React.createElement("button", {
    className: "lp-button modal-button-ok",
    onClick: dataConfirm('yes')
  }, /*#__PURE__*/React.createElement("span", null, Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__["__"])('OK', 'learnpress'))), /*#__PURE__*/React.createElement("button", {
    className: "lp-button modal-button-cancel",
    onClick: dataConfirm('no')
  }, /*#__PURE__*/React.createElement("span", null, Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__["__"])('Cancel', 'learnpress')))))), children);
};

/* harmony default export */ __webpack_exports__["default"] = (Modal);

/***/ }),

/***/ "../../../Clouds/Thimpress/Plugins/github.com/learnpress_v4_doing/learnpress/assets/src/apps/js/frontend/modal/store/actions.js":
/*!*********************************************************************************************************************************************!*\
  !*** E:/Work/Webs/WP/Clouds/Thimpress/Plugins/github.com/learnpress_v4_doing/learnpress/assets/src/apps/js/frontend/modal/store/actions.js ***!
  \*********************************************************************************************************************************************/
/*! exports provided: show, hide, confirm */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "show", function() { return show; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "hide", function() { return hide; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "confirm", function() { return confirm; });
function show(message, cb) {
  return {
    type: 'SHOW_MODAL',
    message: message,
    cb: cb
  };
}
function hide() {
  return {
    type: 'HIDE_MODAL'
  };
}
function confirm(value) {
  return {
    type: 'CONFIRM',
    value: value
  };
}

/***/ }),

/***/ "../../../Clouds/Thimpress/Plugins/github.com/learnpress_v4_doing/learnpress/assets/src/apps/js/frontend/modal/store/index.js":
/*!*******************************************************************************************************************************************!*\
  !*** E:/Work/Webs/WP/Clouds/Thimpress/Plugins/github.com/learnpress_v4_doing/learnpress/assets/src/apps/js/frontend/modal/store/index.js ***!
  \*******************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _reducer__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./reducer */ "../../../Clouds/Thimpress/Plugins/github.com/learnpress_v4_doing/learnpress/assets/src/apps/js/frontend/modal/store/reducer.js");
/* harmony import */ var _actions__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./actions */ "../../../Clouds/Thimpress/Plugins/github.com/learnpress_v4_doing/learnpress/assets/src/apps/js/frontend/modal/store/actions.js");
/* harmony import */ var _selectors__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./selectors */ "../../../Clouds/Thimpress/Plugins/github.com/learnpress_v4_doing/learnpress/assets/src/apps/js/frontend/modal/store/selectors.js");
/* harmony import */ var _middlewares__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./middlewares */ "../../../Clouds/Thimpress/Plugins/github.com/learnpress_v4_doing/learnpress/assets/src/apps/js/frontend/modal/store/middlewares.js");
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) { symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); } keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }






var dataControls = LP.dataControls.controls;
var store = Object(_wordpress_data__WEBPACK_IMPORTED_MODULE_0__["registerStore"])('learnpress/modal', {
  reducer: _reducer__WEBPACK_IMPORTED_MODULE_1__["default"],
  selectors: _selectors__WEBPACK_IMPORTED_MODULE_3__,
  actions: _actions__WEBPACK_IMPORTED_MODULE_2__,
  controls: _objectSpread({}, dataControls)
});
Object(_middlewares__WEBPACK_IMPORTED_MODULE_4__["default"])(store);
/* harmony default export */ __webpack_exports__["default"] = (store);

/***/ }),

/***/ "../../../Clouds/Thimpress/Plugins/github.com/learnpress_v4_doing/learnpress/assets/src/apps/js/frontend/modal/store/middlewares.js":
/*!*************************************************************************************************************************************************!*\
  !*** E:/Work/Webs/WP/Clouds/Thimpress/Plugins/github.com/learnpress_v4_doing/learnpress/assets/src/apps/js/frontend/modal/store/middlewares.js ***!
  \*************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var refx__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! refx */ "../../../Clouds/Thimpress/Plugins/github.com/learnpress_v4_doing/learnpress/node_modules/refx/refx.js");
/* harmony import */ var refx__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(refx__WEBPACK_IMPORTED_MODULE_0__);
/**
 * External dependencies
 */

var effects = {
  ENROLL_COURSE_X: function ENROLL_COURSE_X(action, store) {
    enrollCourse: (function (action, store) {
      var dispatch = store.dispatch; //dispatch()
    });
  }
};
/**
 * Applies the custom middlewares used specifically in the editor module.
 *
 * @param {Object} store Store Object.
 *
 * @return {Object} Update Store Object.
 */

function applyMiddlewares(store) {
  var enhancedDispatch = function enhancedDispatch() {
    throw new Error('Dispatching while constructing your middleware is not allowed. ' + 'Other middleware would not be applied to this dispatch.');
  };

  var middlewareAPI = {
    getState: store.getState,
    dispatch: function dispatch() {
      return enhancedDispatch.apply(void 0, arguments);
    }
  };
  enhancedDispatch = refx__WEBPACK_IMPORTED_MODULE_0___default()(effects)(middlewareAPI)(store.dispatch);
  store.dispatch = enhancedDispatch;
  return store;
}

/* harmony default export */ __webpack_exports__["default"] = (applyMiddlewares);

/***/ }),

/***/ "../../../Clouds/Thimpress/Plugins/github.com/learnpress_v4_doing/learnpress/assets/src/apps/js/frontend/modal/store/reducer.js":
/*!*********************************************************************************************************************************************!*\
  !*** E:/Work/Webs/WP/Clouds/Thimpress/Plugins/github.com/learnpress_v4_doing/learnpress/assets/src/apps/js/frontend/modal/store/reducer.js ***!
  \*********************************************************************************************************************************************/
/*! exports provided: Modal, default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Modal", function() { return Modal; });
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) { symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); } keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

var STORE_DATA = {};
var Modal = function Modal() {
  var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : STORE_DATA;
  var action = arguments.length > 1 ? arguments[1] : undefined;

  switch (action.type) {
    case 'SHOW_MODAL':
      return _objectSpread(_objectSpread({}, state), {}, {
        isOpen: true,
        message: action.message,
        cb: action.cb
      });

    case 'HIDE_MODAL':
      return _objectSpread(_objectSpread({}, state), {}, {
        isOpen: false,
        message: false,
        cb: null
      });

    case 'CONFIRM':
      state.cb && setTimeout(function () {
        state.cb();
      }, 10);
      return _objectSpread(_objectSpread({}, state), {}, {
        confirm: action.value
      });
  }

  return state;
};
/* harmony default export */ __webpack_exports__["default"] = (Modal);

/***/ }),

/***/ "../../../Clouds/Thimpress/Plugins/github.com/learnpress_v4_doing/learnpress/assets/src/apps/js/frontend/modal/store/selectors.js":
/*!***********************************************************************************************************************************************!*\
  !*** E:/Work/Webs/WP/Clouds/Thimpress/Plugins/github.com/learnpress_v4_doing/learnpress/assets/src/apps/js/frontend/modal/store/selectors.js ***!
  \***********************************************************************************************************************************************/
/*! exports provided: isOpen, getMessage, confirm */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "isOpen", function() { return isOpen; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "getMessage", function() { return getMessage; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "confirm", function() { return confirm; });
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_0__);

function isOpen(state) {
  return state.isOpen;
}
function getMessage(state) {
  return state.message;
}
function confirm(state, message, cb) {
  var _dispatch = Object(_wordpress_data__WEBPACK_IMPORTED_MODULE_0__["dispatch"])('learnpress/modal'),
      show = _dispatch.show,
      hide = _dispatch.hide;

  if (!state.message) {
    show(message, cb);
  } else {
    hide();
    return state.confirm;
  }

  return 'no';
}

/***/ }),

/***/ "../../../Clouds/Thimpress/Plugins/github.com/learnpress_v4_doing/learnpress/node_modules/refx/refx.js":
/*!********************************************************************************************************************!*\
  !*** E:/Work/Webs/WP/Clouds/Thimpress/Plugins/github.com/learnpress_v4_doing/learnpress/node_modules/refx/refx.js ***!
  \********************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


function flattenIntoMap( map, effects ) {
	var i;
	if ( Array.isArray( effects ) ) {
		for ( i = 0; i < effects.length; i++ ) {
			flattenIntoMap( map, effects[ i ] );
		}
	} else {
		for ( i in effects ) {
			map[ i ] = ( map[ i ] || [] ).concat( effects[ i ] );
		}
	}
}

function refx( effects ) {
	var map = {},
		middleware;

	flattenIntoMap( map, effects );

	middleware = function( store ) {
		return function( next ) {
			return function( action ) {
				var handlers = map[ action.type ],
					result = next( action ),
					i, handlerAction;

				if ( handlers ) {
					for ( i = 0; i < handlers.length; i++ ) {
						handlerAction = handlers[ i ]( action, store );
						if ( handlerAction ) {
							store.dispatch( handlerAction );
						}
					}
				}

				return result;
			};
		};
	};

	middleware.effects = map;

	return middleware;
}

module.exports = refx;


/***/ }),

/***/ "@wordpress/data":
/*!******************************!*\
  !*** external ["wp","data"] ***!
  \******************************/
/*! no static exports found */
/***/ (function(module, exports) {

(function() { module.exports = window["wp"]["data"]; }());

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/*! no static exports found */
/***/ (function(module, exports) {

(function() { module.exports = window["wp"]["i18n"]; }());

/***/ })

/******/ });
//# sourceMappingURL=modal.js.map