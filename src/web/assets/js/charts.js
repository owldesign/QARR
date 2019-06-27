function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance"); }

function _iterableToArrayLimit(arr, i) { var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

var QarrLineChart, QarrPieChart, QarrDonutChart;
QarrLineChart = Garnish.Base.extend({
  $container: null,
  $chartExplorer: null,
  $totalValue: null,
  $chartContainer: null,
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
    var $chartExplorer = $('<div class="chart-explorer"></div>').appendTo(this.$container);
    var $chartHeader = $('<div class="chart-header"></div>').appendTo($chartExplorer);
    var $timelinePickerWrapper = $('<div class="timeline-wrapper mb-4" />').appendTo($chartHeader);
    this.$chartExplorer = $chartExplorer;
    this.$chartContainer = $('<div class="chart-container"></div>').appendTo($chartExplorer);
    this.$spinner = $('<div class="loader absolute top-0 right-0"><svg width="20px" height="20px" viewBox="0 0 42 42" xmlns="http://www.w3.org/2000/svg" stroke="#E9EFF4"><g fill="none" fill-rule="evenodd"><g transform="translate(4 3)" stroke-width="5"><circle stroke-opacity=".5" cx="18" cy="18" r="18"/><path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"/></path></g></g></svg></div>').prependTo($chartHeader);
    this.$error = $('<div class="error"></div>').appendTo(this.$chartContainer);
    this.$chart = $('<div class="chart"></div>').appendTo(this.$chartContainer);
    this.$monthBtn = $('<button id="month-range" class="qarr-btn-link btn-small btn-active mr-4">' + Craft.t('qarr', 'Last 30 days') + '</buttons>').appendTo($timelinePickerWrapper);
    this.$weekBtn = $('<button id="week-range" class="qarr-btn-link btn-small">' + Craft.t('qarr', 'Week') + '</buttons>').appendTo($timelinePickerWrapper);
    this.addListener(this.$monthBtn, 'click', 'handleMonthChange');
    this.addListener(this.$weekBtn, 'click', 'handleWeekChange');
  },
  handleMonthChange: function handleMonthChange() {
    this.$weekBtn.removeClass('btn-active');
    this.$monthBtn.addClass('btn-active');
    var startTime = this.monthRangeDate();
    var endTime = new Date(new Date().getTime());
    this.params.startDate = startTime;
    this.params.endDate = endTime;
    this.setStorage('startTime', startTime);
    this.setStorage('endTime', endTime);
    this.loadReport();
  },
  handleWeekChange: function handleWeekChange() {
    this.$monthBtn.removeClass('btn-active');
    this.$weekBtn.addClass('btn-active');
    var startTime = this.weekRangeDate();
    var endTime = new Date(new Date().getTime());
    this.params.startDate = startTime;
    this.params.endDate = endTime;
    this.setStorage('startTime', startTime);
    this.setStorage('endTime', endTime);
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
    var requestData = this.params;
    requestData.startDate = this.getDateValue(this.params.startDate);
    requestData.endDate = this.getDateValue(this.params.endDate);
    requestData.elementType = this.element;
    this.$spinner.removeClass('hidden');
    this.$error.addClass('hidden');
    this.$chart.removeClass('error');
    Craft.postActionRequest('qarr/charts/get-entries-count', requestData, $.proxy(function (response, textStatus) {
      this.$spinner.addClass('hidden');

      if (textStatus === 'success' && typeof response.error === 'undefined') {
        if (!this.chart) {
          this.chart = new Craft.charts.Area(this.$chart);
        }

        var chartDataTable = new Craft.charts.DataTable(response.dataTable);
        var chartSettings = {
          orientation: response.orientation,
          dataScale: response.scale,
          formats: response.formats,
          margin: {
            top: 10,
            right: 10,
            bottom: 30,
            left: 10
          } // this.chart.settings.formats = response.formats

        };
        this.chart.draw(chartDataTable, chartSettings);
      } else {
        var msg = Craft.t('An unknown error occurred.');

        if (typeof response !== 'undefined' && response && typeof response.error !== 'undefined') {
          msg = response.error;
        }

        this.$error.html(msg);
        this.$error.removeClass('hidden');
        this.$chart.addClass('error');
      }
    }, this));
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
    if (_typeof(QarrLineChart.storage[namespace]) === (typeof undefined === "undefined" ? "undefined" : _typeof(undefined))) {
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
    var payload = {};
    payload.elementType = this.elementType;
    Craft.postActionRequest('qarr/charts/get-status-stats', payload, $.proxy(function (response, textStatus) {
      if (response.success) {
        this.data = response.data;

        if (response.data.total > 0) {
          this.drawChart();
        } else {
          this.drawEmptyChart();
        }

        this.trigger('response', {
          data: this.data.entries
        });
      }
    }, this));
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
      d3.select(this).transition().duration(300).ease(d3.easeExpOut).attr('transform', 'scale(1.1)');
      that.totalContainer.transition().duration(300).style('opacity', 0).transition().duration(300).style('opacity', 1).text(that.data.entries[i].count);
      that.trigger('pieIn', {
        data: that.data.entries[i]
      });
    }).on('mouseout', function (d, i) {
      d3.select(this).transition().duration(300).ease(d3.easeExpIn).attr('transform', 'scale(1)');
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
    radius: 50
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
    var _this = this;

    data.forEach(function (slice) {
      var _this$_getCoordinates = _this._getCoordinatesForPercent(_this.cumulativePercent),
          _this$_getCoordinates2 = _slicedToArray(_this$_getCoordinates, 2),
          startX = _this$_getCoordinates2[0],
          startY = _this$_getCoordinates2[1];

      _this.cumulativePercent += slice.percent;

      var _this$_getCoordinates3 = _this._getCoordinatesForPercent(_this.cumulativePercent),
          _this$_getCoordinates4 = _slicedToArray(_this$_getCoordinates3, 2),
          endX = _this$_getCoordinates4[0],
          endY = _this$_getCoordinates4[1];

      var largeArcFlag = slice.percent > .5 ? 1 : 0;

      var path = _this._getPath(startX, startY, largeArcFlag, endX, endY);

      var pathEl = document.createElementNS('http://www.w3.org/2000/svg', 'path');
      pathEl.setAttribute('d', path);
      pathEl.setAttribute('fill', slice.color);

      _this.target.append(pathEl);
    });
  },
  _getPath: function _getPath(startX, startY, largeArcFlag, endX, endY) {
    var path = ["M ".concat(startX, " ").concat(startY), // Move
    "A 1 1 0 ".concat(largeArcFlag, " 1 ").concat(endX, " ").concat(endY), // Arc
    "L 0 0"].join(' ');
    return path;
  },
  _getCoordinatesForPercent: function _getCoordinatesForPercent(percent) {
    var x = Math.cos(2 * Math.PI * percent);
    var y = Math.sin(2 * Math.PI * percent);
    return [x, y];
  }
});
