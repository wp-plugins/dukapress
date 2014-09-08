<?php

/**
 * Image Resize Function
 * We want to resize images using WordPress image resize function
 */
if(!function_exists('dp_img_resize')){
	function dp_img_resize($attach_id = null, $img_url = null, $width, $height, $crop = true){
		if($width && $height){
			if($attach_id){
				// this is an attachment, so we have the ID
				$image_src = wp_get_attachment_image_src($attach_id, 'full');
				$file_path = get_attached_file($attach_id);
			} elseif($img_url){
				// this is not an attachment, let's use the image url
				$file_path = parse_url($img_url);
				$file_path = $_SERVER['DOCUMENT_ROOT'].$file_path['path'];
				// Look for Multisite Path
				if(file_exists($file_path) === false){
					global $blog_id;
					$file_path = parse_url($img_url);
					if(preg_match('/files/', $file_path['path'])){
						$path = explode('/', $file_path['path']);
						foreach($path as $k => $v){
							if($v == 'files'){
								$path[$k-1] = 'wp-content/blogs.dir/'.$blog_id;
							}
						}
						$path = implode('/', $path);
					}
					$file_path = $_SERVER['DOCUMENT_ROOT'].$path;
				}
				//$file_path = ltrim( $file_path['path'], '/' );
				//$file_path = rtrim( ABSPATH, '/' ).$file_path['path'];
				$orig_size = getimagesize($file_path);
				$image_src[0] = $img_url;
				$image_src[1] = $orig_size[0];
				$image_src[2] = $orig_size[1];
			}
			$file_info = pathinfo($file_path);
			// check if file exists
			$base_file = $file_info['dirname'].'/'.$file_info['filename'].'.'.$file_info['extension'];
			if(!file_exists($base_file))
			return;
			$extension = '.'. $file_info['extension'];
			// the image path without the extension
			$no_ext_path = $file_info['dirname'].'/'.$file_info['filename'];
			$cropped_img_path = $no_ext_path.'-'.$width.'x'.$height.$extension;
			if(file_exists($cropped_img_path))
				if(time() - @filemtime(utf8_decode($cropped_img_path)) >= 2*24*60*60){
					unlink($cropped_img_path);
				}
			// checking if the file size is larger than the target size
			// if it is smaller or the same size, stop right here and return
			if($image_src[1] > $width){
				// the file is larger, check if the resized version already exists (for $crop = true but will also work for $crop = false if the sizes match)
				if(file_exists($cropped_img_path)){
					$cropped_img_url = str_replace(basename($image_src[0]), basename($cropped_img_path), $image_src[0]);
					$dp_image = array(
						'url'   => $cropped_img_url,
						'width' => $width,
						'height'    => $height
					);
					return $dp_image['url'];
				}
				// $crop = false or no height set
				if($crop == false OR !$height){
					// calculate the size proportionaly
					$proportional_size = wp_constrain_dimensions($image_src[1], $image_src[2], $width, $height);
					$resized_img_path = $no_ext_path.'-'.$proportional_size[0].'x'.$proportional_size[1].$extension;
					// checking if the file already exists
					if(file_exists($resized_img_path)){
						$resized_img_url = str_replace(basename($image_src[0]), basename($resized_img_path), $image_src[0]);
						$dp_image = array(
							'url'   => $resized_img_url,
							'width' => $proportional_size[0],
							'height'    => $proportional_size[1]
						);
						return $dp_image['url'];
					}
				}
				// check if image width is smaller than set width
				$img_size = getimagesize($file_path);
				if($img_size[0] <= $width) $width = $img_size[0];
				// Check if GD Library installed
				if(!function_exists('imagecreatetruecolor')){
					echo 'GD Library Error: imagecreatetruecolor does not exist - please contact your webhost and ask them to install the GD library';
					return;
				}
				
				$new_img_path = wp_get_image_editor($file_path);
				if ( ! is_wp_error( $new_img_path ) ) {
					$new_img_path->resize( $width, $height, true );
					$filename = $new_img_path->generate_filename();
					$new_img_path->save($filename);
					
					// no cache files - let's finally resize it
					$new_img_path = image_resize($file_path, $width.'px', $height.'px', $crop);
					$new_img = str_replace(basename($image_src[0]), basename($filename), $image_src[0]);

					return $new_img;
				}else{
					return $img_url;;
				}	
			}
			return $image_src[0];
		}else{
			return $img_url;
		}
		
	}
}

/**
 * DukaPress Custom Pagination links for header 
 * From wp-includes/general-template.php
 */
if(!function_exists('dp_paginate_links')){
	function dp_paginate_links( $args = '' ) {
		$defaults = array(
			'base' => '%_%', // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
			'format' => '?page=%#%', // ?page=%#% : %#% is replaced by the page number
			'total' => 1,
			'current' => 0,
			'show_all' => false,
			'prev_next' => true,
			'prev_text' => __('&laquo; Previous'),
			'next_text' => __('Next &raquo;'),
			'end_size' => 1,
			'mid_size' => 2,
			'type' => 'plain',
			'add_args' => false, // array of query args to add
			'add_fragment' => '',
			'before_page_number' => '',
			'after_page_number' => ''
		);

		$args = wp_parse_args( $args, $defaults );
		extract($args, EXTR_SKIP);

		// Who knows what else people pass in $args
		$total = (int) $total;
		if ( $total < 2 )
			return;
		$current  = (int) $current;
		$end_size = 0  < (int) $end_size ? (int) $end_size : 1; // Out of bounds?  Make it the default.
		$mid_size = 0 <= (int) $mid_size ? (int) $mid_size : 2;
		$add_args = is_array($add_args) ? $add_args : false;
		$r = '';
		$page_links = array();
		$n = 0;
		$dots = false;

		if ( $prev_next && $current && 1 < $current ) :
			$link = str_replace('%_%', 2 == $current ? '' : $format, $base);
			$link = str_replace('%#%', $current - 1, $link);
			if ( $add_args )
				$link = add_query_arg( $add_args, $link );
			$link .= $add_fragment;

			/**
			 * Filter the paginated links for the given archive pages.
			 *
			 * @since 3.0.0
			 *
			 * @param string $link The paginated link URL.
			 */
			$page_links[] = '<link rel="prev" href="' . esc_url( apply_filters( 'paginate_links', $link ) ) . '"/>';
		endif;
		for ( $n = 1; $n <= $total; $n++ ) :
			if ( $n == $current ) :
				$dots = true;
			else :
				if ( $show_all || ( $n <= $end_size || ( $current && $n >= $current - $mid_size && $n <= $current + $mid_size ) || $n > $total - $end_size ) ) :
					$link = str_replace('%_%', 1 == $n ? '' : $format, $base);
					$link = str_replace('%#%', $n, $link);
					if ( $add_args )
						$link = add_query_arg( $add_args, $link );
					$link .= $add_fragment;

					$dots = true;
				elseif ( $dots && !$show_all ) :
					$dots = false;
				endif;
			endif;
		endfor;
		if ( $prev_next && $current && ( $current < $total || -1 == $total ) ) :
			$link = str_replace('%_%', $format, $base);
			$link = str_replace('%#%', $current + 1, $link);
			if ( $add_args )
				$link = add_query_arg( $add_args, $link );
			$link .= $add_fragment;

			/** This filter is documented in wp-includes/general-template.php */
			$page_links[] = '<link rel="next" href="' . esc_url( apply_filters( 'paginate_links', $link ) ) . '"/>';
		endif;
		switch ( $type ) :
			case 'array' :
				return $page_links;
				break;
			default :
				$r = join("\n", $page_links);
				break;
		endswitch;
		return $r;
	}
}

?>