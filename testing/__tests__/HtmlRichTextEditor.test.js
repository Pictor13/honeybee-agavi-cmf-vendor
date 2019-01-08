if (process.stdout._handle) process.stdout._handle.setBlocking(true);   // https://stackoverflow.com/questions/5127532/is-node-js-console-log-asynchronous


// @todo Move to config/setup file (any specific setup location provided by Jest?)

// RequireJS init
jest.mock('vm'); // prevent "requirejsVars is not defined" (see: https://github.com/facebook/jest/issues/1914)

// @todo Use AMDefine? Or stick to the plugin?
var requirejs = require('requirejs');   


requirejs.onError = function (err) {
    if (err && err.requireType === 'custom') {
        console.log(err.message);
    } else {
        throw err;
    }   
};


requirejs.config({
    catchError:true,
        //Pass the top-level main.js/index.js require
        //function to requirejs so that node modules
        //are loaded relative to the top-level JS file.
        nodeRequire: require,

        // @todo: the following config should generated via TWIG template "requirejs_config"
        //        (custom action? maybe generate/cache when building assets?)
        baseUrl: __dirname + "/../../pub/static/modules/",
        // urlArgs: "cb=" +  (new Date()).getTime(),
        lodashLoader: {
ext: ".html",       // for lodash templates
        // root: __dirname + "/../../pub/static/modules/",
        templateSettings: {}
    },


    waitSeconds: 30,
    paths: {
        "jquery": "Honeybee_Core/lib/jquery",
        "jsb": "Honeybee_Core/lib/jsb",
        "lodash": "Honeybee_Core/lib/lodash",
        "moment": "Honeybee_Core/lib/moment/moment-with-locales.min",
        "ldsh": "Honeybee_Core/lib/lodash-template-loader",
        "magnific-popup": "Honeybee_Core/lib/magnific-popup",
        "selectize": "Honeybee_Core/lib/selectize",
        "stickyfill": "Honeybee_Core/lib/stickyfill",
        "squire": "Honeybee_Core/lib/squire",  // problems using the symling
        // "squire": "../../../node_modules/squire-rte/build/squire-raw",  // problems using the symling
            // 1 - when installing just on the CMF (with no project, just to test own components) 'node_mdules' is erroneously under 'root/' rather than under 'vendor/'.
            // 2 - when using the symlink, this one is RELATIVE to 'pub/static/modules/<module>/lib',
            //     while atm the symlink is resolved relative to the baseUrl, messing up the retrival of the module file.
            // 3 - atm all the paths need to be checked and specified here; ideally we want to not define them more than once
            //     (in the AllModules or Path.twig; here should just be dependent on it).
        "dompurify": "Honeybee_Core/lib/dompurify",

        "jquery-datetimepicker": "Bo_Tb/lib/jquery-datetimepicker",
        "jquery-mousewheel": "Bo_Tb/lib/jquery-mousewheel",
        "php-date-formatter": "Bo_Tb/lib/php-date-formatter",
        "leaflet": "Bo_Tb/lib/leaflet",
        "mapbox-gl": "Bo_Tb/lib/mapbox-gl",
    },
    shim: {
        "jsb": {
            deps: ["jquery"],
            exports: "jsb"
        },
        "selectize": {
            deps: ["jquery"]
        },
        "magnific-popup": {
            deps: ["jquery"]
        },
        "stickyfill": {
            deps: ["jquery"]
        },
        "jquery-mousewheel": {
            deps: ["jquery"]
        },
        "php-date-formatter": {
            "deps": ["jquery"]
        },
        "leaflet": {
            "deps": ["jquery"]
        },
        "mapbox-gl": {
            "deps": ["jquery"]
        },
        "jquery-datetimepicker": {
            deps: [
                "jquery",
                "jquery-mousewheel",
                "php-date-formatter"
            ]
        },
    }

});

// Widgets definition
requirejs.define([
    "jquery",
    "jsb",
    "lodash",
    "Honeybee_Core/Config",
    "Honeybee_Core/Logging",
    "Honeybee_Core/Widget",
    "magnific-popup",
    "selectize",
    "stickyfill",
    "squire",
    "dompurify",
    "moment",
    "ldsh!Honeybee_Core/lib/calendar.tmpl",
    "Honeybee_Core/lib/selectrect",
    "Honeybee_Core/ui/Autostart",
    "Honeybee_Core/ui/ListFilter",
    "Honeybee_Core/ui/ListFiltersController",
    "Honeybee_Core/ui/DatePicker",
    "Honeybee_Core/ui/DateRangePicker",
    "Honeybee_Core/ui/SelectBox",
    "Honeybee_Core/ui/TextList",
    "Honeybee_Core/ui/AssetList",
    "Honeybee_Core/ui/ImageList",
    "Honeybee_Core/ui/Tabs",
    "Honeybee_Core/ui/ActionGroup",
    "Honeybee_Core/ui/EmbeddedEntityList",
    "Honeybee_Core/ui/EntityReferenceList",
    "Honeybee_Core/ui/HtmlRichTextEditor",
    "Honeybee_Core/ui/HtmlLinkPopup",
    "Honeybee_Core/ui/GlobalErrors",
    "Honeybee_Core/ui/list-filter/BooleanListFilter",
    "Honeybee_Core/ui/list-filter/ChoiceListFilter",
    "Honeybee_Core/ui/list-filter/DatePickerListFilter",
    "Honeybee_Core/ui/list-filter/DateRangePickerListFilter",
    "Honeybee_Core/ui/list-filter/TextListListFilter",
    "Honeybee_Core/ui/SelectizePlugins"
], function(
    $,
    jsb,
    _,
    Config,
    Logging,
    Widget,
    mfp,
    selectize,
    stickyfill,
    squire,
    dompurify,
    moment,
    calendar_tmpl,
    selectrect,
    Autostart,
    Listfilter,
    ListFiltersController,
    DatePicker,
    DateRangePicker,
    SelectBox,
    TextList,
    AssetList,
    ImageList,
    Tabs,
    ActionGroup,
    EmbeddedEntityList,
    EntityReferenceList,
    HtmlRichTextEditor,
    HtmlLinkPopup,
    GlobalErrors,
    BooleanListFilter,
    ChoiceListFilter,
    DatePickerListFilter,
    DateRangePickerListFilter,
    TextListListFilter
) {

    // nothing to do here, only use to load all stuff at once
    // when AgaviConfig setting "requirejs.use_optimized" is "true"

}, function (err) {
    console.log('an error has happened');
    throw "an error has happened";
// err has err.requireType (timeout, nodefine, scripterror)
// and err.requireModules (an array of module Ids/paths)
});



//modules are loaded according to requirejs
//config, and if found, assumed to be an AMD module.
//If they are not found via the requirejs config,
//then node's require is used to load the module,
//and if found, the module is assumed to be a
//node-formatted module. Note: this synchronous
//style of loading a module only works in Node.
var $ = requirejs('jquery');

let $textarea = $('<textarea></textarea>');

//Now export a value visible to Node.
module.exports = function () {

};

// @™odo: need to configure requirejs relying on rjs twig-templates
var load_modules = requirejs(["jquery", "bar"],
function   ($,   bar) {
    //foo and bar are loaded according to requirejs
    //config, but if not found, then node's require
    //is used to load the module.

+throw "debug thrown error";
    // need jQuery
    // need honeybee/widgets

    // let textarea = document.createElement('TEXTAREA');
    let $textarea = jquery('<textarea></textarea>');
    let options = {};
    let hrte = new HtmlRichTextEditor($textarea[0], options);

});
new load_modules();


// Testing test

describe('Addition', () => {
  it('knows that 2 and 2 make 4', () => {
    expect(2 + 2).toBe(4);
  });
});

