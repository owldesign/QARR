Garnish.$doc.ready(function(){if($("#rule-icon").on("keyup",function(e){var n=$(this).val(),t=$(this).parent().find(".qarr-input-icon"),a=window.FontAwesome.icon({prefix:"fal",iconName:n});a&&t.html(a.html)}),$("#rule-data").length>0){var e=document.getElementById("rule-data"),n=new Tagify(e);n.DOM.input.classList.add("tagify__input--outside"),n.DOM.scope.parentNode.insertBefore(n.DOM.input,n.DOM.scope),document.querySelector(".tags--removeAllBtn").addEventListener("click",function(e){n.removeAllTags(),$(".tags--removeAllBtn").addClass("btn-disabled")}.bind(n)),n.on("add",function(e){n.value.length>0&&$(".tags--removeAllBtn").removeClass("btn-disabled")}),n.on("remove",function(e){0===n.value.length&&$(".tags--removeAllBtn").addClass("btn-disabled")})}});