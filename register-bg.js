(function() {
    tinymce.create('tinymce.plugins.BibleGateway', {
        init : function(ed, url) {
            ed.addButton('bgbible', {
                title : 'BibleGateway Search Link',
                cmd : 'biblelink',
                image : url + '/bg.png'
            });

            ed.addCommand( 'biblelink', function() {
                ed.focus();
                // check for selection...
                var passage = ed.selection.getContent({format : 'text'});
                // otherwise prompt
                passage = (typeof passage !== 'undefined' && passage.trim()) ? passage : prompt( window.bgbible.passage_text );
                var display, shortcode;

                // If they've provided a passage,
                if (passage !== null) {

                    shortcode = 'passage="' + passage + '"';

                    // Let's check if they want an alternate display
                    display = prompt( window.bgbible.display_text );
                    // if so, update our shortcode parameters
                    if ( display !== null && display )
                        shortcode = shortcode + ' display="' + display + '"';
                    // insert shortcode
                    ed.execCommand('mceInsertContent', 0, '[biblegateway ' + shortcode + ']');
                }
            });
        },

        createControl : function(n, cm) {
            return null;
        },

        getInfo : function() {
            return {
                longname : 'BibleGateway Shortcode Button',
                author : 'Justin Sternberg',
                authorurl : 'http://dsgnwrks.pro',
                infourl : 'http://dsgnwrks.pro/plugins/biblegateway-search-shortcode',
                version : '0.1.0'
            };
        }
    });

    // Visual editor button
    tinymce.PluginManager.add( 'bgbible', tinymce.plugins.BibleGateway );

})();