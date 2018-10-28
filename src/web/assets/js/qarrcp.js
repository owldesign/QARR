var QarrCP = Garnish.Base.extend({
    $navContainer: null,
    $navIcon: null,
    $navLabel: null,
    $navLabelBadge: null,

    navIconSvg: null,

    totalPending: null,

    init: function init() {
        this.$navContainer = $('#nav-qarr');
        this.$navIcon = this.$navContainer.find('.icon');
        this.$navLabel = this.$navContainer.find('.label');
        this.navIconSvg = this.$navIcon.find('svg');

        this.$navLabel.append($('<span class="qarr-pending-count-badge">0</span>'));
        this.$navLabelBadge = this.$navLabel.find('span');

        this._getPendingEntries();
    },
    handleResponse: function handleResponse(response) {
        this.totalPending = response.total;
        this.$navLabelBadge.html(this.totalPending);
    },
    updatedPendingCounter: function updatedPendingCounter(value) {
        this.totalPending = value;
        this.$navLabelBadge.html(value);
    },
    _getPendingEntries: function _getPendingEntries() {
        var _this = this;

        Craft.postActionRequest('qarr/elements/check-pending', {}, $.proxy(function (response, textStatus) {
            if (response.success) {
                _this.handleResponse(response);
            }
        }, this));
    }
});

Garnish.$doc.ready(function () {
    window.qarrnav = new QarrCP();

    var $appInfoContainer = $('#app-info');

    // TODO: add dynamic version lookup
    // if ($appInfoContainer) {
    //     let qarrVersion = $('<div id="qarr-version">QARR 1.0.0</div>');
    //     $appInfoContainer.append(qarrVersion);
    // }
});
