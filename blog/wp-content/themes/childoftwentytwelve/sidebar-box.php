<?php
/**
 * The sidebar containing the main widget area.
 *
 * If no active widgets in sidebar, let's hide it completely.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */
?>


	<?php if ( is_active_sidebar( 'sidebar-4' ) ) : ?>
		<div id="secondary" class="widget-area" role="complementary">
		
		
<aside id="social-icons" class="widget clear">
	<h3 class="widget-title">Connect with Wildfire Health</h3>
	<ul>
		<li class="social-twitter">
			<a href="#" title="Follow Wildfire Health on Twitter" target="_blank">Follow Wildfire Health on Twitter</a>
		</li>
		
		<li class="social-facebook">
			<a href="#" title="Like Wildfire Health on Facebook" target="_blank">Like Wildfire Health on Facebook</a>
		</li>
		
		<li class="social-contact">
			<a href="#" title="Get in touch with Wildfire Health" target="_blank">Get in touch with Wildfire Health</a>
		</li>
		
		<li class="social-youtube">
			<a href="#" title="Find Wildfire Health on Youtube" target="_blank">Find Wildfire Health on Youtube</a>
		</li>
		
		<li class="social-rss">
			<a href="#" title="Subscribe to RSS Feed" target="_blank">Subscribe to RSS Feed</a>
		</li>
		
		
	</ul>
</aside>
		
		
		
		
          <div class="block-border"></div>
            <div class="wf-block">
			<?php dynamic_sidebar( 'sidebar-4' ); ?>
            </div><!--/wf-block-->
        </div><!-- #secondary -->
	<?php endif; ?>
	
