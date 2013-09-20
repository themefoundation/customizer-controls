<?php

/**
 * Adds multiple category selection support to the theme customizer via checkboxes
 *
 * The category IDs are saved in the database as a comma separated string.
 */
class Cstmzr_Category_Checkboxes_Control extends WP_Customize_Control {
	public $type = 'category-checkboxes';

	public function render_content() {

		// Loads javascript
		echo <<<END

<script>

// Holds the status of whether or not the rest of the code should be run
var cstmzr_multicat_js_run = true;

jQuery(window).load(function() {

	// Prevents code from running twice due to live preview window.load firing in addition to the main customizer window.
	if( true == cstmzr_multicat_js_run ) {
		cstmzr_multicat_js_run = false;
	} else {
		return;
	}

	var api = wp.customize;

	// Loops through each instance of the category checkboxes control.
	jQuery('.cstmzr-hidden-categories').each(function(){
		
		var id = jQuery(this).prop('id');
		var categoryString = api.instance(id).get();
		var categoryArray = categoryString.split(',');

		// Checks/unchecks category checkboxes based on saved data.
		jQuery('#' + id).closest('li').find('.cstmzr-category-checkbox').each(function() {

			var elementID = jQuery(this).prop('id').split('-');

			if( jQuery.inArray( elementID[1], categoryArray ) < 0 ) {
				jQuery(this).prop('checked', false);
			} else {
				jQuery(this).prop('checked', true);
			}

		});		

	});

	// Sets listeners for checkboxes
	jQuery('.cstmzr-category-checkbox').live('change', function(){

		var id = jQuery(this).closest('li').find('.cstmzr-hidden-categories').prop('id');
		var elementID = jQuery(this).prop('id').split('-');

		if( jQuery(this).prop('checked' ) == true ) {
			addCategory(elementID[1], id);
		} else {
			removeCategory(elementID[1], id);
		}

	});

	// Adds category ID to hidden input.
	function addCategory( catID, controlID ) {
		
		var categoryString = api.instance(controlID).get();
		var categoryArray = categoryString.split(',');

		if ( '' == categoryString ) {
			var delimiter = '';
		} else {
			var delimiter = ',';
		}

		// Updates hidden field value.
		if( jQuery.inArray( catID, categoryArray ) < 0 ) {
			api.instance(controlID).set( categoryString + delimiter + catID );
		}
	}

	// Removes category ID from hidden input.
	function removeCategory( catID, controlID ) {

		var categoryString = api.instance(controlID).get();
		var categoryArray = categoryString.split(',');
		var catIndex = jQuery.inArray( catID, categoryArray );

		if( catIndex >= 0 ) {

			// Removes element from array.
			categoryArray.splice(catIndex, 1);

			// Creates new category string based on remaining array elements.
			var newCategoryString = '';
			jQuery.each( categoryArray, function() {
				if ( '' == newCategoryString ) {
					var delimiter = '';
				} else {
					var delimiter = ',';
				}
			 	newCategoryString = newCategoryString + delimiter + this;
			});

			// Updates hidden field value.
			api.instance(controlID).set( newCategoryString );
		}
	}
});

</script>

END;

		// Displays checkbox heading
		echo '<span class="customize-control-title">' . esc_html( $this->label ) . '</span>';

		// Displays category checkboxes.
		foreach ( get_categories() as $category ) {
			echo '<label><input type="checkbox" name="category-' . $category->term_id . '" id="category-' . $category->term_id . '" class="cstmzr-category-checkbox"> ' . $category->cat_name . '</label><br>';	
		}

		// Loads the hidden input field that stores the saved category list.
		?><input type="hidden" id="<?php echo $this->id; ?>" class="cstmzr-hidden-categories" <?php $this->link(); ?> value="<?php echo sanitize_text_field( $this->value() ); ?>"><?php
	}
}
