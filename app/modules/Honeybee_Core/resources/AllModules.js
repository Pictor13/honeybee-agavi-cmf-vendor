define([
    "jquery",
    "jsb",

    "Honeybee_Core/Config",
    "Honeybee_Core/Logging",
    "Honeybee_Core/Widget",

    "magnific-popup",
    "selectize",
    "moment",
    "ldsh!Honeybee_Core/lib/calendar.tmpl",
    "Honeybee_Core/lib/lodash",
    "Honeybee_Core/lib/selectrect",

    "Honeybee_Core/ui/Autostart",
    "Honeybee_Core/ui/DatePicker",
    "Honeybee_Core/ui/SelectBox",
    "Honeybee_Core/ui/TextList",
    "Honeybee_Core/ui/ImageList",
    "Honeybee_Core/ui/Tabs",
    "Honeybee_Core/ui/ActionGroup",
    "Honeybee_Core/ui/EmbeddedEntityList",
    "Honeybee_Core/ui/EntityReferenceList"
], function($, jsb) {

    // nothing to do here, only use to load all stuff at once
    // when AgaviConfig setting "requirejs.use_optimized" is "true"

}, function (err) {
// err has err.requireType (timeout, nodefine, scripterror)
// and err.requireModules (an array of module Ids/paths)
});
