define([
    "Honeybee_Core/Widget",
    "Honeybee_Core/ui/HtmlRichTextEditor",
    "jquery",
    "lodash"
], function(Widget, HtmlRichTextEditor, $, _) {

    /* Temp unit-test */

    HtmlRichTextEditorTest.prototype = new Widget();

    var template = '';

    HtmlRichTextEditorTest.prototype.runTests = function() {
        function areElementSetsEqual(set1, set2) {
            var i;
            if (set1.length !== set2.length) {
                return false;
            }
            for (i = 0; i < set1.length; i++) {
                if (!set1[i].isEqualNode(set2[i])) {
                    return false;
                }
            }
            return true;
        }

        var hrteditor = new HtmlRichTextEditor(dom_element, options)

        var testDOMPurifyConfigOnSyncSanitization = function() {
            var fixtures = {
                '<br>': '<br>',
                '<br>Foo': '<br>Foo',
                '<div><br></div>': '<div><br></div>',
                '<div class="hb-paragraph"><br></div>': '<div class="hb-paragraph"><br></div>',
                '<span>': '',
                '<span>foo</span>': 'foo',
                '<div style="display:none;">foo</div>': '<div>foo</div>',
                '<div style="display:none;" onclick="maliciousFunction();">foo</div>': '<div>foo</div>',
                '<button style="display:none;" onclick="maliciousFunction();">foo</button>': 'foo',
                '<a href="file://passwd">foo</a>': '<a>foo</a>',
                'Aliquam <i>sequi</i> ipsum <a href="http://fugiat.com">fugiat</a> similique <br> ex tempore nulla': 'Aliquam <i>sequi</i> ipsum <a href="http://fugiat.com">fugiat</a> similique <br> ex tempore nulla'
            };
console.log('SYNC_CONFIG', function(){_.forOwn(_.sortBy(hrteditor.options.dompurify_sync_config['FORBID_ATTR']), function(el) {console.log(el)})}.call(this));

            for(var fixture in fixtures) {
console.log('sanitized', DOMPurify.sanitize(fixture, hrteditor.options.dompurify_sync_config));
                var sanitized_html = DOMPurify.sanitize(fixture, hrteditor.options.dompurify_sync_config);
                var returned_elements = $.parseHTML(sanitized_html);
                var expected_elements = $.parseHTML(fixtures[fixture]);
console.log('test fixture:', fixtures[fixture],
            'return val:', DOMPurify.sanitize(fixture, hrteditor.options.dompurify_sync_config),
            'return $:', returned_elements,
            'expected $:', expected_elements);
                if (sanitized_html === '' && expected_elements.length !== 0) {
                    throw 'Test with fixture "'+fixture+'" returns nothing, while '+expected_elements.length+' elements were expected.';
                } else if (sanitized_html !== '' && areElementSetsEqual(returned_elements, expected_elements) === false) {
                    throw 'Test with return value "'+sanitized_html+'" doesn\'t reflect the expected value "'+fixtures[fixture]+'".';
                }
            }
            return true;
        };

//         var testDOMPurifyConfigOnPasteSanitization = function() {
//             var fixtures = {
//                 '<br>': '<br>',
//                 '<div><br></div>': '<div><br></div>',
//                 '<div class="hb-paragraph"><br></div>': '<div class="hb-paragraph"><br></div>',
//                 '<span>': '',
//                 '<span>foo</span>': 'foo',
//                 '<div style="display:none;">foo</div>': '<div>foo</div>',
//                 '<div style="display:none;" onclick="maliciousFunction();">foo</div>': '<div>foo</div>',
//                 '<button style="display:none;" onclick="maliciousFunction();">foo</button>': 'foo'
//             };

//             for(var fixture in fixtures) {
//                 var $returned_element = $(DOMPurify.sanitize(fixture, hrteditor.options.dompurify_sync_config));
//                 var $expected_element = $(fixtures[fixture]);
// // console.log('test fixture:', fixtures[fixture],
// //             'return val:', DOMPurify.sanitize(fixture, hrteditor.options.dompurify_sync_config),
// //             'return $:', $returned_element,
// //             'expected $:', $expected_element);
//                 if (_.isUndefined($returned_element.get(0)) && $expected_element.length !== 0) {
//                     throw 'Test with fixture "'+fixture+'" returns nothing, while '+$expected_element.length+' elements were expected.';
//                 } else if (!_.isUndefined($returned_element.get(0)) && $returned_element.get(0).isEqualNode($expected_element.get(0)) === false) {
//                     throw 'Test with return value "'+$returned_element.html()+'" doesn\'t reflect the expected value "'+$expected_element.html()+'".';
//                 }
//             }
//             return true;
//         };

//         var testDOMPurifyConfigOnDefaultSanitization = function() {
//              test the case of missing the 'target' attribute when reloading the data
//         }

        var testInputNotChangedBySanitization = function() {
            var unchanging_fixtures = [
                '',
                'simple spaced text',
                '<a href="http://www.berlinonline.de" target="_blank">Link opening in new window</a>',
                '<br>Line-break and some text',
                '<div>Simple div</div>',
                '<div class="hb-paragraph"><br></div> Empty-line and some text',
                'span',
                '<a href="https://github.com">Link</a>',
                '<a href="https://github.com" target="_blank">Link in new window</a>'
            ];
            for(var index in unchanging_fixtures) {
                var fixture = unchanging_fixtures[index];
                if (!compareMarkup(hrteditor.sanitize(fixture), fixture)) {
                    throw 'Test of fixture "'+fixture+'" failed. Returned value was "' +
                        _.isUndefined(hrteditor.sanitize(fixture))
                            ? ('undefined", while '+ $(fixture).length +' elements were expected.')
                            : $(fixture).html() +'".';
                }
            }
            return true;
        };

        var testInputChangedBySanitization = function() {
            var changing_fixtures = {
                '<br>': '',
                '<div><br></div>': '',
                '<div class="hb-paragraph"><br></div>': '',
                '<span>': '',
                '<span>foo</span>': 'foo',
                '<div>foo</div>': '<div>foo</div>',
                '<div style="display:none;">foo</div>': '<div>foo</div>',
                '<button style="display:none;" onclick="maliciousFunction();">foo</button>': 'foo'
            };
            for(var fixture in changing_fixtures) {
                var expected_value = changing_fixtures[fixture];
                var returned_value = hrteditor.sanitize(fixture);
                if (expected_value !== returned_value) {
                    throw 'Test with return value "'+returned_value+'" doesn\'t reflect the expected value "'+expected_value+'".';
                }
            }
            return true;
        };

        // widget instance should be created inside test functions
        var testChangesToEditorConfig = function() {
            var original_options = hrteditor.options;
            // reset options
            hrteditor.options = {};
            hrteditor.addOptions(default_options, options);


            different_editor_block = { blockTag: 'p', blockAttributes: null };
            hrteditor.options.squire_config = _.merge({}, hrteditor.options.squire_config, different_editor_block);

            hrteditor.editor = hrteditor.createSquireInstance();
            // consistent options
            hrteditor.keepDompurifyConfigConsistent();

            if (hrteditor.sanitize('<p><br></p>') !== '') {
                throw 'Test with return value "'+hrteditor.sanitize('<p><br></p>')+'" doesn\'t reflect the expected value "".';
            }
            if (hrteditor.sanitize('<div><br></div>') !== '') {
                throw 'Test with return value "'+hrteditor.sanitize('<div><br></div>')+'" doesn\'t reflect the expected value "".';
            }
            if (hrteditor.sanitize('<br>') !== '') {
                throw 'Test with return value "'+hrteditor.sanitize('<br>')+'" doesn\'t reflect the expected value "".';
            }
            if (hrteditor.sanitize('Wrap<br>Text') !== 'Wrap<br>Text') {
                throw 'Test with return value "'+hrteditor.sanitize('Wrap<br>Text')+'" doesn\'t reflect the expected value "Wrap<br>Text".';
            }
            if (hrteditor.sanitize('<div>Wrap<br>Text</div>') !== 'Wrap<br>Text') {
                throw 'Test with return value "'+hrteditor.sanitize('<div>Wrap<br>Text</div>')+'" doesn\'t reflect the expected value "Wrap<br>Text".';
            }

            hrteditor.options = original_options;

            return true;
        };

        var compareMarkup = function(markup_a, markup_b) {
            var $element_a = $(markup_a);
            var $element_b = $(markup_b);
            if (_.isUndefined($element_a.get(0)) && $element_b.length !== 0) {
                return false;
            } else if (!_.isUndefined($element_a.get(0)) && $element_a.get(0).isEqualNode($element_b.get(0)) === false) {
                return false;
            }
            return true;
        };

        testDOMPurifyConfigOnSyncSanitization.call(this);
        testInputNotChangedBySanitization.call(this);
        testInputChangedBySanitization.call(this);
        testChangesToEditorConfig.call(this);
        console.log('✔︎ - All tests ran successfully.');
    };

    return HtmlRichTextEditorTest;
});
