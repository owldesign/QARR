Garnish.$doc.ready(function () {
  new Vue({
    el: '#email-template-app',
    data: function data() {
      return {
        iframe: null,
        options: {},
        templatePath: 'simple',
        element: 'review',
        elementId: null,
        settings: {
          bgColor: '#f4f4f4',
          containerColor: '#ffffff'
        },
        body: '',
        footer: ''
      };
    },
    mounted: function mounted() {
      templateSuggest.$on('templateSelected', this.onTemplatePathChanged);
      this.elementId = $('#element-id').val();
    },
    created: function created() {
      this.updateIframe();
    },
    computed: {},
    methods: {
      onTemplatePathChanged: function onTemplatePathChanged(options) {
        this.templatePath = options.item.name;
        this.updateIframe();
      },
      onBgColorChanged: function onBgColorChanged(e) {
        this.settings.bgColor = e.target.value;
        this.updateIframe();
      },
      onContainerColorChanged: function onContainerColorChanged(e) {
        this.settings.containerColor = e.target.value;
        this.updateIframe();
      },
      onBodyChanged: function onBodyChanged(e) {
        this.body = e.target.value;
        this.updateIframe();
      },
      onFooterChanged: function onFooterChanged(e) {
        this.footer = e.target.value;
        this.updateIframe();
      },
      onElementChanged: function onElementChanged(e) {
        this.element = e.target.value;
        this.updateIframe(true);
      },
      updateIframe: function updateIframe() {
        var _this = this;

        var $forceUpdate = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
        var payload = {
          templatePath: this.templatePath,
          element: this.element,
          body: this.body,
          footer: this.footer,
          settings: this.settings,
          elementId: this.elementId,
          forceUpdate: $forceUpdate
        };
        payload[Craft.csrfTokenName] = Craft.csrfTokenValue;
        return axios.post(Craft.getActionUrl('qarr/campaigns/email-templates/get-email-template-preview'), payload, {
          headers: {
            'X-CSRF-Token': Craft.csrfTokenValue
          }
        }).then(function (res) {
          // cache element id
          _this.elementId = res.data.elementId;
          var iframe = $('<iframe class="lp-preview" frameborder="0" width="100%" height="100%"/>');
          iframe.appendTo(_this.$el);
          iframe.on('load', function () {
            if (_this.iframe) {
              _this.iframe.remove();
            }

            _this.iframe = iframe;

            _this.iframe.off();
          });
          Garnish.requestAnimationFrame($.proxy(function () {
            iframe[0].contentWindow.document.open();
            iframe[0].contentWindow.document.write(res.data.template);
            iframe[0].contentWindow.document.close();
            iframe[0].height = iframe[0].contentWindow.document.body.scrollHeight + 60;
          }, _this));
        });
      }
    }
  });
});
