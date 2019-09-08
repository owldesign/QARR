Garnish.$doc.ready(function () {
  new Vue({
    el: '#email-template-app',
    data: function data() {
      return {
        iframe: null,
        options: {},
        settings: {},
        fields: {
          bgColor: '#f4f4f4',
          containerColor: '#ffffff',
          headline: 'Thanks for your input!',
          subheadline: 'Our Response',
          footerText: 'Cheers!'
        }
      };
    },
    created: function created() {
      this.updateIframe();
    },
    methods: {
      onBgColorChanged: function onBgColorChanged(e) {
        this.fields.bgColor = e.target.value;
        this.updateIframe();
      },
      onContainerColorChanged: function onContainerColorChanged(e) {
        this.fields.containerColor = e.target.value;
        this.updateIframe();
      },
      onHeadlineChanged: function onHeadlineChanged(e) {
        this.fields.headline = e.target.value;
        this.updateIframe();
      },
      onSubheadlineChanged: function onSubheadlineChanged(e) {
        this.fields.subheadline = e.target.value;
        this.updateIframe();
      },
      onFooterTextChanged: function onFooterTextChanged(e) {
        this.fields.footerText = e.target.value;
        this.updateIframe();
      },
      updateIframe: function updateIframe() {
        var _this = this;

        var payload = {
          fields: this.fields
        };
        payload[Craft.csrfTokenName] = Craft.csrfTokenValue;
        return axios.post(Craft.getActionUrl('qarr/campaigns/email-templates/get-email-template-preview'), payload, {
          headers: {
            'X-CSRF-Token': Craft.csrfTokenValue
          }
        }).then(function (res) {
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
