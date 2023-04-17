/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!**********************************!*\
  !*** ./development/js/charts.js ***!
  \**********************************/
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
function _iterableToArrayLimit(arr, i) { var _i = null == arr ? null : "undefined" != typeof Symbol && arr[Symbol.iterator] || arr["@@iterator"]; if (null != _i) { var _s, _e, _x, _r, _arr = [], _n = !0, _d = !1; try { if (_x = (_i = _i.call(arr)).next, 0 === i) { if (Object(_i) !== _i) return; _n = !1; } else for (; !(_n = (_s = _x.call(_i)).done) && (_arr.push(_s.value), _arr.length !== i); _n = !0); } catch (err) { _d = !0, _e = err; } finally { try { if (!_n && null != _i["return"] && (_r = _i["return"](), Object(_r) !== _r)) return; } finally { if (_d) throw _e; } } return _arr; } }
function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
QarrLineChart = Garnish.Base.extend({
  $container: null,
  $chartContainer: null,
  $timelinePickerWrapper: null,
  $chartExplorer: null,
  $chartHeader: null,
  $totalValue: null,
  $spinner: null,
  $error: null,
  $chart: null,
  element: null,
  params: {
    startDate: null,
    endDate: null
  },
  init: function init(el, element) {
    this.$container = $(el);
    this.element = element;
    this.createChartExplorer();
    this.handleMonthChange();
  },
  getStorage: function getStorage(key) {
    return QarrLineChart.getStorage(this._namespace, key);
  },
  setStorage: function setStorage(key, value) {
    QarrLineChart.setStorage(this._namespace, key, value);
  },
  createChartExplorer: function createChartExplorer() {
    this.$chartContainer = $('<div class="chart hidden"></div>').appendTo(this.$container);
    this.$chartHeader = $('<div class="chart-header"></div>').prependTo(this.$container);
    this.$timelinePickerWrapper = $('<div class="timeline-wrapper mb-4" />').appendTo(this.$chartHeader);
    this.$spinner = $('<div class="loader"><svg width="20px" height="20px" viewBox="0 0 42 42" xmlns="http://www.w3.org/2000/svg" stroke="#E9EFF4"><g fill="none" fill-rule="evenodd"><g transform="translate(4 3)" stroke-width="5"><circle stroke-opacity=".5" cx="18" cy="18" r="18"/><path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"/></path></g></g></svg></div>').prependTo(this.$chartHeader);
    this.$error = $('<div class="error"></div>').appendTo(this.$chartContainer);
    this.$monthBtn = $('<div id="month-range" class="btn secondary small">' + Craft.t('qarr', 'Last 30 days') + '</div>').appendTo(this.$timelinePickerWrapper);
    this.$weekBtn = $('<div id="week-range" class="btn small">' + Craft.t('qarr', 'Week') + '</div>').appendTo(this.$timelinePickerWrapper);
    this.addListener(this.$monthBtn, 'click', 'handleMonthChange');
    this.addListener(this.$weekBtn, 'click', 'handleWeekChange');
  },
  handleMonthChange: function handleMonthChange() {
    this.$weekBtn.removeClass('secondary');
    this.$monthBtn.addClass('secondary');
    var startTime = this.monthRangeDate();
    var endTime = new Date(new Date().getTime());
    this.params.startDate = startTime;
    this.params.endDate = endTime;
    this.setStorage('startTime', startTime);
    this.setStorage('endTime', endTime);
    this.$chartContainer.html('');
    this.loadReport();
  },
  handleWeekChange: function handleWeekChange() {
    this.$monthBtn.removeClass('secondary');
    this.$weekBtn.addClass('secondary');
    var startTime = this.weekRangeDate();
    var endTime = new Date(new Date().getTime());
    this.params.startDate = startTime;
    this.params.endDate = endTime;
    this.setStorage('startTime', startTime);
    this.setStorage('endTime', endTime);
    this.$chartContainer.html('');
    this.loadReport();
  },
  monthRangeDate: function monthRangeDate() {
    var today = new Date();
    return new Date(new Date().setDate(today.getDate() - 30));
  },
  weekRangeDate: function weekRangeDate() {
    var firstDay = new Date(new Date().getTime());
    return new Date(firstDay.getTime() - 7 * 24 * 60 * 60 * 1000);
  },
  loadReport: function loadReport() {
    var _this = this;
    var requestData = this.params;
    requestData.startDate = this.getDateValue(this.params.startDate);
    requestData.endDate = this.getDateValue(this.params.endDate);
    requestData.elementType = this.element;
    this.$spinner.removeClass('hidden');
    this.$error.addClass('hidden');
    Craft.sendActionRequest('POST', 'qarr/charts/get-entries-count', {
      data: requestData
    }).then(function (response) {
      _this.$spinner.addClass('hidden');
      if (_this.$chartContainer.removeClass('hidden'), response.data.errors && response.data.errors.length) return Promise.reject();
      _this.chart = new Craft.charts.Area(_this.$chartContainer, {
        yAxis: {
          formatter: function formatter(chart) {
            return function (d) {
              var format = ',.0f';
              if (d !== Math.round(d)) {
                format = ',.1f';
              }
              return chart.formatLocale.format(format)(d);
            };
          }
        }
      });
      var chartDataTable = new Craft.charts.DataTable(response.data.dataTable);
      var chartSettings = {
        orientation: response.data.orientation,
        dataScale: response.data.scale,
        formats: response.data.formats
      };
      _this.chart.draw(chartDataTable, chartSettings);
      _this.chart.resize();
    })["catch"](function (e) {
      var error = e.response.data.message || Craft.t('A server error occurred');
      this.$error.html(error);
      this.$error.removeClass('hidden');
      // this.$chart.addClass('error')
    });
  },
  getDateFromDatepickerInstance: function getDateFromDatepickerInstance(inst) {
    return new Date(inst.currentYear, inst.currentMonth, inst.currentDay);
  },
  getDateValue: function getDateValue(date) {
    return date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();
  }
}, {
  storage: {},
  getStorage: function getStorage(namespace, key) {
    if (QarrLineChart.storage[namespace] && QarrLineChart.storage[namespace][key]) {
      return QarrLineChart.storage[namespace][key];
    }
    return null;
  },
  setStorage: function setStorage(namespace, key, value) {
    if (_typeof(QarrLineChart.storage[namespace]) === ( true ? "undefined" : 0)) {
      QarrLineChart.storage[namespace] = {};
    }
    QarrLineChart.storage[namespace][key] = value;
  }
});
QarrDonutChart = Garnish.Base.extend({
  $el: null,
  elementType: null,
  width: null,
  height: null,
  radius: null,
  data: null,
  totalContainer: null,
  totalCount: null,
  counter: 0,
  currentValue: null,
  color: null,
  pie: null,
  svg: null,
  g: null,
  arc: null,
  path: null,
  init: function init(el, element) {
    this.el = el;
    this.elementType = element;
    this.width = QarrDonutChart.settings.width;
    this.height = QarrDonutChart.settings.height;
    this.radius = QarrDonutChart.settings.radius;
    this._fetchData();
  },
  _fetchData: function _fetchData() {
    var _this2 = this;
    var payload = {};
    payload.elementType = this.elementType;
    Craft.sendActionRequest('POST', 'qarr/charts/get-status-stats', {
      data: payload
    }).then(function (response) {
      if (!response.data.success) return Promise.reject();
      _this2.data = response.data;
      if (response.data.total > 0) {
        _this2.drawChart();
      } else {
        _this2.drawEmptyChart();
      }
      _this2.trigger('response', {
        data: _this2.data.entries
      });
    })["catch"](function (e) {});

    // Craft.postActionRequest('qarr/charts/get-status-stats', payload, $.proxy(function (response, textStatus) {
    //     if (response.success) {
    //         this.data = response.data;
    //
    //         if (response.data.total > 0) {
    //             this.drawChart();
    //         } else {
    //             this.drawEmptyChart();
    //         }
    //
    //         this.trigger('response', {
    //             data: this.data.entries
    //         })
    //     }
    // }, this));
  },
  refreshData: function refreshData() {
    // TODO: fix this
    // this.path
    this.svg.remove();
    this._fetchData();
  },
  drawEmptyChart: function drawEmptyChart() {
    this.drawArc();
    this.drawPie();
    this.drawSvg();
    this.drawTotalText();
    this.drawEmptyPath();
  },
  drawChart: function drawChart() {
    this.drawArc();
    this.drawPie();
    this.drawSvg();
    this.drawTotalText();
    this.drawPaths();
    this.setMouseEvents();
  },
  drawArc: function drawArc() {
    this.arc = d3.arc().outerRadius(this.radius - 10).innerRadius(this.radius / 1.7).cornerRadius(2).padAngle(.04);
  },
  drawPie: function drawPie() {
    this.pie = d3.pie()($.map(this.data.entries, function (d) {
      return d.count;
    }));
  },
  drawSvg: function drawSvg() {
    this.svg = d3.select(this.el).append('svg').attr('width', this.width).attr('height', this.height).attr('fill', 'transparent').append('g').attr('transform', 'translate(' + this.width / 2 + ',' + this.height / 2 + ')');
  },
  drawTotalText: function drawTotalText() {
    this.totalContainer = this.svg.append("text").attr("text-anchor", "middle").attr('font-size', '1em').attr('y', 7).attr('fill', '#a5a6a8').text(this.data.total);
  },
  drawPaths: function drawPaths() {
    var that = this;
    this.path = this.svg.selectAll('path').data(this.pie).enter().append('path').transition().delay(function (d, i) {
      return i * 400;
    }).attr('d', this.arc).attrTween('d', function (d) {
      var i = d3.interpolate(d.startAngle + 0.1, d.endAngle);
      return function (t) {
        d.endAngle = i(t);
        return that.arc(d);
      };
    }).style('fill', function (d, i) {
      return that.data.entries[i].color;
    });
  },
  drawEmptyPath: function drawEmptyPath() {
    var that = this;
    this.path = this.svg.selectAll('.background').data(d3.pie()([1])).enter().append('path').transition().delay(function (d, i) {
      return i * 400;
    }).attr('d', this.arc).attrTween('d', function (d) {
      var i = d3.interpolate(d.startAngle + 0.1, d.endAngle);
      return function (t) {
        d.endAngle = i(t);
        return that.arc(d);
      };
    }).style('fill', function (d, i) {
      return '#E9EFF4';
    });
  },
  setMouseEvents: function setMouseEvents() {
    var that = this;
    this.svg.selectAll('path').on('mouseover', function (d, i) {
      d3.select(this).transition().duration(300).ease(d3.easeExpOut).style('opacity', 0.5);
      that.totalContainer.transition().duration(300).style('opacity', 0).transition().duration(300).style('opacity', 1).text(that.data.entries[i].count);
      that.trigger('pieIn', {
        data: that.data.entries[i]
      });
    }).on('mouseout', function (d, i) {
      d3.select(this).transition().duration(300).ease(d3.easeExpIn).style('opacity', 1);
      that.totalContainer.transition().duration(300).style('opacity', 0).transition().duration(300).style('opacity', 1).text(that.data.total);
      that.trigger('pieOut', {
        data: that.data.entries[i]
      });
    });
  }
}, {
  settings: {
    width: 100,
    height: 100,
    radius: 60
  }
});
QarrPieChart = Garnish.Base.extend({
  target: null,
  cumulativePercent: 0,
  init: function init(target, data) {
    this.target = $(target);
    this.addSlices(data);
  },
  addSlices: function addSlices(data) {
    var _this3 = this;
    data.forEach(function (slice) {
      var _this3$_getCoordinate = _this3._getCoordinatesForPercent(_this3.cumulativePercent),
        _this3$_getCoordinate2 = _slicedToArray(_this3$_getCoordinate, 2),
        startX = _this3$_getCoordinate2[0],
        startY = _this3$_getCoordinate2[1];
      _this3.cumulativePercent += slice.percent;
      var _this3$_getCoordinate3 = _this3._getCoordinatesForPercent(_this3.cumulativePercent),
        _this3$_getCoordinate4 = _slicedToArray(_this3$_getCoordinate3, 2),
        endX = _this3$_getCoordinate4[0],
        endY = _this3$_getCoordinate4[1];
      var largeArcFlag = slice.percent > .5 ? 1 : 0;
      var path = _this3._getPath(startX, startY, largeArcFlag, endX, endY);
      var pathEl = document.createElementNS('http://www.w3.org/2000/svg', 'path');
      pathEl.setAttribute('d', path);
      pathEl.setAttribute('fill', slice.color);
      _this3.target.append(pathEl);
    });
  },
  _getPath: function _getPath(startX, startY, largeArcFlag, endX, endY) {
    return ["M ".concat(startX, " ").concat(startY), // Move
    "A 1 1 0 ".concat(largeArcFlag, " 1 ").concat(endX, " ").concat(endY), // Arc
    "L 0 0" // Line
    ].join(' ');
  },
  _getCoordinatesForPercent: function _getCoordinatesForPercent(percent) {
    var x = Math.cos(2 * Math.PI * percent);
    var y = Math.sin(2 * Math.PI * percent);
    return [x, y];
  }
});
/******/ })()
;