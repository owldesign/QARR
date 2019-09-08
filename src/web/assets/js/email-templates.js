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
            console.log('iframe loaded');

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
  }); // new Vue({
  //         el: "#{{ containerId|namespaceInputId|e('js') }}",
  //
  //         data() {
  //             {% block data %}
  //             var data = {{ {
  //                 selected: '',
  //                     filteredOptions: [],
  //                     suggestions: suggestions ?? [],
  //                     inputProps: {
  //                 class: class,
  //                     initialValue: value ?? '',
  //                         style: style ?? '',
  //                         id: id|namespaceInputId,
  //                         name: (name ?? '')|namespaceInputName,
  //                         size: size ?? '',
  //                         maxlength: maxlength ?? '',
  //                         autofocus: autofocus ?? false,
  //                         disabled: disabled ?? false,
  //                         title: title ?? '',
  //                         placeholder: placeholder ?? '',
  //                 },
  //                 limit: limit ?? 5
  //             }|json_encode|raw }};
  //             data.inputProps.onInputChange = this.onInputChange;
  //             {% endblock %}
  //             return data;
  //         },
  //
  //         methods: {
  //     {% block methods %}
  //     onInputChange(text) {
  //         if (text === '' || text === undefined) {
  //             this.filteredOptions = this.suggestions;
  //             return;
  //         }
  //
  //         text = text.toLowerCase();
  //
  //         var filtered = [];
  //         var i, j, sectionFilter, item, name;
  //         var that = this;
  //
  //         for (i = 0; i < this.suggestions.length; i++) {
  //             sectionFilter = [];
  //             for (j = 0; j < this.suggestions[i].data.length; j++) {
  //                 item = this.suggestions[i].data[j];
  //                 if (
  //                     (item.name || item).toLowerCase().indexOf(text) !== -1 ||
  //                     (item.hint && item.hint.toLowerCase().indexOf(text) !== -1)
  //                 ) {
  //                     sectionFilter.push(item.name ? item : {name: item});
  //                 }
  //             }
  //             if (sectionFilter.length) {
  //                 sectionFilter.sort(function(a, b) {
  //                     var scoreA = that.scoreItem(a, text);
  //                     var scoreB = that.scoreItem(b, text);
  //                     if (scoreA === scoreB) {
  //                         return 0;
  //                     }
  //                     return scoreA < scoreB ? 1 : -1;
  //                 });
  //                 filtered.push({
  //                     label: this.suggestions[i].label || null,
  //                     data: sectionFilter.slice(0, this.limit)
  //                 });
  //             }
  //         }
  //
  //         this.filteredOptions = filtered;
  //     },
  //     scoreItem(item, text) {
  //         var score = 0;
  //         if (item.name.toLowerCase().indexOf(text) !== -1) {
  //             score += 100 + text.length / item.name.length;
  //         }
  //         if (item.hint && item.hint.toLowerCase().indexOf(text) !== -1) {
  //             score += text.length / item.hint.length;
  //         }
  //         return score;
  //     },
  //     onSelected(option) {
  //         this.selected = option.item;
  //     },
  //     getSuggestionValue(suggestion) {
  //         return suggestion.item.name || suggestion.item;
  //     },
  //     {% endblock %}
  // }
  // })
});
