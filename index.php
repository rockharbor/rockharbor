<?php 
global $wp_rewrite, $wp_query;
get_header(); 
?>
		<section id="content" role="main">
			<header id="content-title">
				<h1 class="page-title">
					<span>
						<?php echo wp_title(''); ?>
					</span>
				</h1>
			</header>
			<nav id="submenu">
				<?php get_sidebar(); ?>
			</nav>
			<?php if (have_posts()): 

				while (have_posts()) {
					the_post(); 
					get_template_part('content', is_search() ? 'search' : get_post_type()); 
				}
				$theme->set('wp_rewrite', $wp_rewrite);
				$theme->set('wp_query', $wp_query);
				echo $theme->render('pagination');
			 else: ?>

			<article id="post-0" class="post no-results not-found">
				<header class="entry-header">
					<h1 class="entry-title"><?php _e('Nothing Found', 'rockharbor'); ?></h1>
				</header>

				<div class="entry-content">
					<p><?php _e('Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'rockharbor'); ?></p>
					<?php get_search_form(); ?>
				</div>
			</article>

			<?php endif; ?>

		</section>
		
		<section id="sidebar" role="complementary">
			<header id="sidebar-title">
				<h1 class="sub-title"><span>CORE shiz</span></h1>
			</header>
			<?php
			get_sidebar('core');
			?>
		</section>

<?php 
get_footer();