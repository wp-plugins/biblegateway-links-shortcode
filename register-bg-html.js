// text editor button
QTags.addButton( 'bgbible', 'bible', function(el, canvas) {

    // check for selection...
    var passage = getSelectedText(canvas);
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
        QTags.insertContent('[biblegateway ' + shortcode + ']');
    }
});

// helper function to get selected text from text editor
function getSelectedText(canvas){
    canvas.focus();
    if (document.selection) { // IE
        return document.selection.createRange().text;
    } else { // standards
        return canvas.value.substring(canvas.selectionStart, canvas.selectionEnd);
    }
}