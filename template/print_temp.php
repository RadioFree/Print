<!DOCTYPE html>
<html>
<head>
	<title>Print View - <?php echo get_the_title(); ?></title>
	<link rel='stylesheet' href="<?php echo plugin_dir_url( __FILE__ ) . 'print.css'; ?>" type='text/css' media='all' />	
</head>
<body class="single-post">
	<header class="pageheader">
		<h1 class="entry-title"><?php the_title(); ?></h1>
	</header>
	<div id="content">
		<div class="printer-block">
			<?php $pa_settings = @unserialize(get_option( 'pa_settings' )); ?>
			<img src="<?php echo @$pa_settings['favicon_url']; ?>" alt="" />
			<div class="text-block">
				<div id="text"><?php echo @$pa_settings['desc']; ?></div>
			</div>
		</div>
		<div class="pa_contents">
			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
				<?php the_post_thumbnail(); ?>  
				<?php the_content(); ?>
			<?php endwhile; else: ?>
			<?php endif; ?>
			<aside class="meta details">
				<p><span id="author"><?php echo @$pa_settings['txt_before_author'] ? $pa_settings['txt_before_author'] : 'via:' . ' '; the_author(); ?></span></p>
			</aside>
		</div>
		<?php show_reference_links(get_the_content()); ?>		
	</div>
	<script src="<?php echo plugin_dir_url( __FILE__ ) . 'jquery.js'; ?>"></script>
	<script>
		var index = 0;
		jQuery('.pa_contents a').each(function(){
			var text = jQuery(this).html();
			jQuery(this).html(text +'<sup class="link-ref-print"> '+ ++index + ' </sup>');
		});
	</script>
</body>
</html>