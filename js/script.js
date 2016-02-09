function showAddCollectionDialog() {
	jQuery('#addCollectionModal').fadeIn('fast');
}

function hideAddCollectionDialog() {
	jQuery('#addCollectionModal').fadeOut('fast');
}

function newShowAddCollectionDialog() {
	jQuery('#newAddCollectionModal').fadeIn('fast');
}
function closeTopBanner() {
	jQuery('#top-banner').fadeOut('fast');
}

function newHideAddCollectionDialog() {
	jQuery('#newAddCollectionModal').fadeOut('fast');
}
function openLogin(){
	jQuery('#new-pop-log').fadeIn('fast');
}
function closeLogin(){
	jQuery('#new-pop-log').fadeOut('fast');
}
jQuery(document).ready(function() {
	jQuery('input[type=file]').bootstrapFileInput();
	jQuery('.file-inputs').bootstrapFileInput();

  	jQuery( "#ntabs" ).tabs({active: 1});
  
	jQuery( "#ntabs-2" ).tabs({active: 1});
	jQuery( "#ntabs-3" ).tabs({active: 1});
	jQuery('#ntabs-2 ul li a').on ('click', function()
	{
		jQuery("a",jQuery(this).parents("ul")).removeClass('activated');
		jQuery(this).addClass('activated');
	});
	jQuery('#ntabs ul li a').on ('click', function()
	{
		jQuery("a",jQuery(this).parents("ul")).removeClass('activated');
		jQuery(this).addClass('activated');
	});

	jQuery( "#tabs" ).tabs({active: 1});
	jQuery( "#tabs2" ).tabs({active: 1});
	jQuery( "#tabs3" ).tabs({active: 1});
	jQuery('#tabs2 ul li a').on ('click', function()
	{
		jQuery("a",jQuery(this).parents("ul")).removeClass('activated');
		jQuery(this).addClass('activated');
	});
	jQuery('#tabs ul li a').on ('click', function()
	{
		jQuery("a",jQuery(this).parents("ul")).removeClass('activated');
		jQuery(this).addClass('activated');
	});

	jQuery( "#tabs-1" ).tabs({active: 1});
	jQuery( "#tabs-2" ).tabs({active: 1});
	jQuery( "#tabs-3" ).tabs({active: 1});
	jQuery('#tabs3 ul li a').on ('click', function()
	{
		jQuery("a",jQuery(this).parents("ul")).removeClass('activated');
		jQuery(this).addClass('activated');
	});
	jQuery('#tabs3 ul li a').on ('click', function()
	{
		jQuery("a",jQuery(this).parents("ul")).removeClass('activated');
		jQuery(this).addClass('activated');
	});


	jQuery(".overlayer-pink").on('click',function(){
    
    jQuery('#preset_image').attr('value', jQuery(this).parent().find('img').attr('src'));
    jQuery('#epreset_image').attr('value', jQuery(this).parent().find('img').attr('src')); 
    jQuery('#new_preset_image').attr('value', jQuery(this).parent().find('img').attr('src')); 
		jQuery(".overlayer-pink").removeClass('chosen-pic');
		jQuery(this).addClass('chosen-pic');
	});

	jQuery(".poper").on ('click', function(){
		jQuery(".check-to-clicking").toggleClass('pop-it');
	});

  jQuery("#new-category-selector").change(function() {
    var categoryId = jQuery(this).val();
    jQuery.ajax({
      type: "POST",
      url: '/social/index/filterpresetimage',
      data: {'category_id': categoryId},
      success: function(data) {
        jQuery('#new-preset-image-container').empty();
        jQuery('#new-preset-image-container').html(data);
        jQuery(".overlayer-pink").on('click',function(){
    
          jQuery('#new_preset_image').attr('value', jQuery(this).parent().find('img').attr('src'));
		      jQuery(".overlayer-pink").removeClass('chosen-pic');
		      jQuery(this).addClass('chosen-pic');
	      });
      }
    });

  });

  jQuery("#category-selector").change(function() {
    var categoryId = jQuery(this).val();
    jQuery.ajax({
      type: "POST",
      url: '/social/index/filterpresetimage',
      data: {'category_id': categoryId},
      success: function(data) {
        jQuery('#preset-image-container').empty();
        jQuery('#preset-image-container').html(data);
        jQuery(".overlayer-pink").on('click',function(){
    
          jQuery('#preset_image').attr('value', jQuery(this).parent().find('img').attr('src'));
          jQuery('#epreset_image').attr('value', jQuery(this).parent().find('img').attr('src'));
		      jQuery(".overlayer-pink").removeClass('chosen-pic');
		      jQuery(this).addClass('chosen-pic');
	      });
      }
    });

  });
});


