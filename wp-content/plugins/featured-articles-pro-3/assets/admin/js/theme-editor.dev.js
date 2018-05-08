/**
 * 
 */
;(function($){
	$(window).load(function(){
		var frame = $('#fa-theme-color-preview').contents();
		
		$('form').on('submit', function( e ){
			var name = $('input[name=color_name]');
			if( name.length > 0 ){
				if( '' == $(name).val() ){
					e.preventDefault();
					$(name).focus();
					$(name).parent().effect( "shake" );
				}				
			}
			
			
		})
		
		$('#theme-views').change(function(){
			var apply_to = $(this).data('apply_to'),
				classes = $(this).val(),
				color = $('input[name=color_name]').val()||'',
				target = $(frame).find( '.' + apply_to );
			
			$( target ).removeClass().addClass( apply_to + ' ' + color + ' ' + classes );
			
		});
		
		$('.fa-size-input').keyup(function(){
			var selector = $(this).parents('.fa-settings-container').data('selector'),
				property = $(this).data('property'),
				unit = 'px';
			if( !$(this).hasClass('size-px') ){
				unit = $(this).next('select').val();
			}
			var val = $(this).val() + unit,
				css = {};
			css[ property ] = val;
			$(frame).find( selector ).css( css );
		})
		
		$('.customize-control-size-unit').change(function(){
			var selector =  $(this).parents('.fa-settings-container').data('selector'),
				property = $( '#' + $(this).data('for') ).data('property'),
				val		 = $( '#' + $(this).data('for') ).val(),
				unit 	 = $(this).val();
			
			var val = val + unit,
				css = {};
				css[ property ] = val;
			
			$(frame).find( selector ).css( css );			
		});
		
		$('.fa-option-prop').change(function(){
			var selector 	= $(this).parents('.fa-settings-container').data('selector'),
				property	= $(this).data('property');
				css			= {};
			css[property] = $(this).val();
			$(frame).find( selector ).css( css );
		})
		
		$('.fa-single-color-picker').wpColorPicker({
			change: function() {
				var color 		= $(this).wpColorPicker('color'),
					selector 	= $(this).parents('.fa-settings-container').data('selector'),
					property	= $(this).data('property');
					css			= {};
					
					css[property] = color;
					$(frame).find( selector ).css( css );				
			},
			clear: function() {
				
			}
		});
		
		var multiple = $('.multi-value');
		$.each( multiple, function(e){
			var inputs = $(this).find('input[type="text"]'),
				property = $(this).data('property'),
				selector 	= $(this).parents('.fa-settings-container').data('selector');
			
			$(this).find('.fa-multi-color-picker').wpColorPicker({
				change: function(){
					var color = $(this).wpColorPicker('color');
					var s = '',
						css = {};
					$.each( inputs, function(){
						if( $(this).hasClass('fa-multi-color-picker') ){
							s += color;
						}else{
							s += $(this).val() + ( $(this).hasClass('fa-size-input') ? 'px' : '' ) + ' ';
						}
					})
					css[property] = s;
					$(frame).find( selector ).css( css );
				}
			})
			
			$(inputs).keyup(function(){
				var s = '',
					css = {};
				$.each( inputs, function(){
					s += $(this).val() + ( $(this).hasClass('fa-size-input') ? 'px' : '' ) + ' ';
					
				})
				css[property] = s;
				$(frame).find( selector ).css( css );
			})
			
		});
		
		$('.fa_upload_image_button').on('ajaxload', function( event, param ){
			var updateElem = $( $(this).data('update') );
			updateElem.empty().append( param.html );
			$('#' + $(this).data('url_field') ).val( param.image_url );
			
			var property = $(this).data('property'),
				selector 	= $(this).parents('.fa-settings-container').data('selector'),
				css			= {};
			
			css[property] = 'url(' + param.image_url + ')';
			$(frame).find( selector ).css( css );			
		})
		
		$('.remove_image').on('click', function(){
			$('#' + $(this).data('url_field') ).val( 'none' );
			var property = $(this).data('property'),
				selector 	= $(this).parents('.fa-settings-container').data('selector'),
				css			= {};			
			css[property] = 'none';
			$(frame).find( selector ).css( css );	
			$(this).parents('.fa_slide_image').remove();
		})
		
	})	
})(jQuery);