<?php
/*
Plugin Name: AlgaeCal Carousel
Description: AlgaeCal's super awesome carousel
Version: 0.1
Author: Shawn Pick
License: GPLv2
*/

//
//   PART 1
//   Adding the carousel post type
//

add_action( 'init', 'Create_Carousel' );
function Create_Carousel() {
    register_post_type( 'Carousel',
        array(
            'labels' => array(
                'name' => 'Carousels',
                'singular_name' => 'Carousel',
                'add_new' => 'Add New Carousel',
                'add_new_item' => 'Add New Carousel',
                'edit' => 'Edit',
                'edit_item' => 'Edit Carousel',
                'new_item' => 'New Carousel',
                'view' => 'View',
                'view_item' => 'View Carousel',
                'not_found' => 'No Carousels found',
                'not_found_in_trash' => 'No Carousels found in Trash',
                'parent' => 'Parent Carousel'
            ),
 
            'public' => true,
            'menu_position' => 5,
            'supports' => array( 'title', 'thumbnail' ),
            'taxonomies' => array( '' ),
            'menu_icon' => plugins_url( 'img/icon.png', __FILE__ ),
            'has_archive' => true
        )
    );
}

//Saving the carousel data 
add_action( 'save_post', 'add_carousel_fields', 10, 2 );
function add_carousel_fields( $carousel_id, $carousel ) {
    
	// Check post type for carousels
    if ( $carousel->post_type == 'carousel' && isset( $_POST['ImageCollection'])) {
		
        // Store data in carousels table if present in post data
		if ( isset( $_POST['carousel_description'] ) && $_POST['carousel_description'] != '' ) {
            update_post_meta( $carousel_id, 'String_carousel_description', $_POST['carousel_description'] );
			//Loging a successful save for demo purposes only
			error_log("Saved carousel description ", 0);
        }
		
		//Using wp_nonce_field as an extra bit of security.
		//Since we are posing from the backend we use check_admin_referer() to authenticate.
		//The check_admin_referer() function checks if we are either posting from a admin screen or if we have a valid nonce 
		if (check_admin_referer( 'Update_Images', 'ImageCollection' ) ) {		
			if ( isset( $_POST['images'] ) ) {
				update_post_meta( $carousel_id, 'images', $_POST['images'] );
				//Loging a successful save for demo purposes only
				error_log("Saved Images ", 0);
			} else {
				update_post_meta( $carousel_id, 'images', null);
			}
		}
    }
}



/*
	Meta boxs
*/



//
//Adding the carousel description (Static Meta Box)
//
add_action( 'admin_init', 'carousel_description' );
function carousel_description() {
    add_meta_box( 'carousel_meta_box', 'Carousel Details', 'display_static_carousel_meta_box', 'Carousel', 'normal', 'high' );
}

//callback for carousel description (Static Meta Box)
function display_static_carousel_meta_box( $carousel ) {
    $carousel_description = esc_html( get_post_meta( $carousel->ID, 'String_carousel_description', true ) );

    $html = '<table>
			<tr>
				<td style="width: 100%">Carousel description</td>
				<td><textarea rows="4" cols="50" name="carousel_description" id="carousel_description">' . $carousel_description . '</textarea>
				</td>
			</tr>
		</table>';
		
	echo $html;
}



//
//Adding the carousel images (Dynamic Meta Box)
//
add_action( 'add_meta_boxes', 'dynamic_add_carousel_images' );
function dynamic_add_carousel_images() {
    add_meta_box('dynamic_sectionid', __( 'Carousel Images', 'carousel_images' ), 'dynamic_inner_carousel_images', 'Carousel');
}
//Callback for carousel images (Dynamic Meta Box)
function dynamic_inner_carousel_images($carousel) {

    // Use nonce for demonstration of verification
    wp_nonce_field( 'Update_Images', 'ImageCollection' );

    //get the saved meta as an array
    $images = get_post_meta($carousel->ID,'images',false);
	$html = "<div id='carousel_images_wrapper'><table id='carousel'><tr><th>Main Image</th><th>Thumb Image</th></tr><tr>";
    $c = 0;

	//Placing the (pre-filled) controls on the page 
	if (count($images) > 0 && $images[0] != null){
		foreach( $images[0] as $key => $image ) {
			if ( isset( $image['img_url'] ) || isset( $image['thumb_url'] ) ) {
				
				$html .= '<tr><td>Image/video/URL <input class="my_upl_button" type="button" value="Upload Image" data-target="images'. $c .'" value="" />';
				$html .= '<input id="images'. $c .'" type="text" name="images['. $c .'][img_url]" value="'. $image['img_url'] .'" /> </td>';
				$html .= '<td> Thumb URL <input class="my_upl_button" type="button" value="Upload Image" data-target="images'. $c .'thumb" value="" />';
				$html .= '<input id="images'. $c .'thumb" type="text" name="images['. $c .'][thumb_url]" value="'. $image['thumb_url'] .'" /><span class="remove">'. __( ' <img src="'. plugins_url('img/remove.png', __FILE__).'" />' ) .'</span></td></tr>';
				$c = $c +1;
			}
		}
	}
	$html .= "</tr></table>";
	$html .= "<span class='add'><img src='" . plugins_url('img/add.png', __FILE__) . "' /> Add Images </span></div>";
	
	echo $html;
	?>
	<script>



	//
	// Adding new fields dynamically
	//
    var $ =jQuery.noConflict();
    $(document).ready(function() {
		//Here we jack into the PHP counter which is already counted up to our current image
        var count = <?php echo $c; ?>;
		var removeimageurl = "<?php echo plugins_url('img/remove.png', __FILE__); ?>";
		//Here we see the old method of binding to a JQuery object.
		//Its use here is still legal as the “Add Images” button was present in the DOM at render.
        $(".add").click(function() {
            count = count + 1;
			
            $('#carousel').append('<tr><td> Image/Video/URL  <input class="my_upl_button" type="button" value="Upload Image" data-target="images'+count+'" /><input id="images'+count+'" type="text" name="images['+count+'][img_url]" value="" /> </td><td> Image URL <input id="testme" class="my_upl_button" type="button" value="Upload Image" data-target="images'+count+'thumb" /><input type="text" id="images'+count+'thumb" name="images['+count+'][thumb_url]" value="" /><span class="remove"> <img src="'+ removeimageurl +'" /> </span></td></tr>' ); return false;
        });
		
		//Using .on() to bind JQuery objects.  
		//As of JQuery 1.7 .bind(), .live() and .delegate() have all been deprecated for use on dynamically create DOM objects
        $(document).on('click','.remove', function() {
			console.log('clicked!');
            $(this).parent().parent().remove();
        });
    });

	
	//
	// Linking the media box output (URL) to the appropriate input box
	//
    $(document).ready( function( $ ) {
        $(document).on('click', '.my_upl_button', function() {
		var $this = $(this);
            window.send_to_editor = function(html) {                
				imgurl = $(html).attr('src')
				//Using HTML5 attributes to store variables within the DOM (data-*).
				var x=document.getElementById($this.attr("data-target"));
				$(x).val(imgurl);												
                tb_remove();
            }
            tb_show( '', 'media-upload.php?type=image&amp;TB_iframe=true' );
            return false;
        });

    });
    </script>

<?php
//Ends dynamic_inner_carousel_images()
}


//
//  shortcode for carousel
//	Adding the ability to call the carousel by either the name or the ID -- Bounes points ???
//
add_shortcode( 'carousel', 'shortcode_carousel' );
function shortcode_carousel( $atts ){
    $atts = shortcode_atts( array(
		'id'    => 0,
		'name'  => "",
    ), $atts, 'carousel' );
    
	//End the function if the user did not supply shortcode attribute
	if($atts['name'] =="" && $atts['id'] == 0){return "No Carousels Found!";}
	
	//Check what the user passed in and find the appropriate carousel
	if($atts['id'] <= 0){
		$my_query = new WP_Query('post_type=carousel&post_title='.$atts['name']);	
	} else {
		$my_query = new WP_Query('post_type=carousel&p='.$atts['id']);
	}
	
	if ( $my_query->have_posts() ){
	$image_array = get_post_meta($my_query->posts[0]->ID,'images',false);
	
	//Build the carousel to hand to the front end
	$html = '<ul id="image-gallery" class="gallery list-unstyled cS-hidden">';
	if ( is_array( $image_array[0] )) {
		foreach( $image_array[0] as $key => $image ) {
			if ( isset( $image['img_url'] ) || isset( $image['thumb_url'] ) ) {
				
				//if the user doesn't supply a thumbnail for the carousel we use the default image
				if($image['thumb_url'] == ""){
					$image['thumb_url'] = plugins_url('img/nothumb.png', __FILE__);
				}
				
				//Here we check if the user supplied a image
				if(strpos($image['img_url'], '.jpg') || strpos($image['img_url'], '.png') || strpos($image['img_url'], '.gif') || strpos($image['img_url'], '.bmp')){
					$html .= ' <li data-thumb="'. $image['thumb_url'] .'"> <img data-lity src="'. $image['img_url'] .'" /></li>';
				} else {
					//No image was found, we assume it was a video
					$html .= ' <li data-thumb="'. $image['thumb_url'] .'"><a data-thumb="'. $image['thumb_url'] .'" href="'. $image['img_url'] .'" data-lity><img src="'. $image['thumb_url'] .'" /></a></li>';
				}
			}			
		}
	}
	$html .= '</ul>';
	
	return $html;
	
	} else {
		//Show the user there was a problem finding their carousel
		return "No carousels found with that name or ID!";
	}
}



//
//  PART 2
//  Overriding the WP JQuery and replacing with latest version
//
add_action('wp_enqueue_scripts', 'carousel_scripts');
//wp_enqueue_scripts is the proper hook to use when enqueuing items that are meant to appear on the front end. 
//Despite the name, it is used for enqueuing both scripts and styles.
//Reference - https://codex.wordpress.org/Plugin_API/Action_Reference/wp_enqueue_scripts
function carousel_scripts() {
	//It is not recommended to override the base JQ script file in the admin area so here we check if the admin area or dashboard is attempting to load.
	//If not we execute the override.
	if (!is_admin()){
		//Deregister the default JQuery script
		wp_deregister_script('jquery');
		//Register a new JQuery script 
		wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js', false, '3.2.2');
		//Enqueue new JQuery script for load in the front end
		wp_enqueue_script('jquery');
	}
	wp_register_script( 'carousel_slider_functions', plugins_url('js/lightslider.js', __FILE__),array("jquery"));
	wp_enqueue_script('carousel_slider_functions');
	
	wp_register_script('carousel_slider_init', plugins_url('js/slider-init.js', __FILE__),array("jquery"));
	wp_enqueue_script('carousel_slider_init');
	
	wp_register_script('carousel_lity', plugins_url('js/lity.min.js', __FILE__),array("jquery"));
	wp_enqueue_script('carousel_lity');

	//CSS
	wp_enqueue_style( 'slider', plugins_url() . '/algaecal-carousel/css/lightslider.css',false,'1.1','all');
	wp_enqueue_style( 'lity', plugins_url() . '/algaecal-carousel/css/lity.min.css',false,'1.1','all');
}