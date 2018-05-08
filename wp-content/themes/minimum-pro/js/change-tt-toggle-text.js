jQuery(function( $ ){
	$('.cmtt-glossary-tooltip-toggle').contents().filter(function() {
    return this.nodeType == 3
}).each(function(){
    this.textContent = this.textContent.replace('Disable Tooltips','Disable Popups');
    this.textContent = this.textContent.replace('Enable Tooltips','Enable Popups');
	});
});