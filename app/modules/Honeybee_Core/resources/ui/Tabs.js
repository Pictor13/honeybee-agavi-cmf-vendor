define([
    "Honeybee_Core/Widget"
], function(Widget) {

    "use strict";

    var default_options = {
        prefix: "Honeybee_Core/ui/Tabs",
    };

    function Tabs(dom_element, options) {
        this.init(dom_element, default_options);
        this.addOptions(options);

        if (this.$widget.length === 0) {
            this.logError(this.getPrefix() + " behaviour not applied as expected DOM doesn't match.");
            return;
        }

        var self = this;

        /**
         * highlighting of currently open tabs
         */
        self.$widget.on("click focus blur", ".hb-tabs__trigger", function(ev) {
            /* setTimout 0 to defer the event.
             * otherwise the :focus selector will not find the newly focused
             * element if it gets focus via tab-key because the browser has not finished
             * applying the new focus state.
             */
            setTimeout(function() {
                self.highlight();
            }, 0);
        });
        self.highlight();
    }

    Tabs.prototype = new Widget();
    Tabs.prototype.constructor = Tabs;

    Tabs.prototype.highlight = function() {
        var active_id = this.$widget.find(".hb-tabs__trigger:checked").attr("id");
        var $selected_label = this.$widget.find(".hb-tabs__header label[for='"+active_id+"']");

        this.$widget.find(".hb-tabs__header label").removeClass("selected");
        $selected_label.addClass("selected");

        var focus_id = this.$widget.find(".hb-tabs__trigger:focus").attr("id");
        //console.log("focus id", focus_id);
        var $focus_label = this.$widget.find(".hb-tabs__header label[for='"+focus_id+"']");
        this.$widget.find(".hb-tabs__header label").removeClass("focus");
        $focus_label.addClass("focus");
    };

    return Tabs;
});
