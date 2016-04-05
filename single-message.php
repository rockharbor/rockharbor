<?php
global $theme, $post;
get_header();
the_post();
?>
		<header id="content-title">
			<?php the_title('<h1>', '</h1>');
			$theme->set('date', $post->post_date);
			echo $theme->render('posted_date');
			?>
		</header>
		<section id="content" role="main" class="full">
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php
				$meta = $theme->metaToData($post->ID);
				?>
				<div class="entry-content">
					<div class="message-details clearfix">
						<?php
						echo apply_filters('the_content', $post->post_content);
						?>
						<div class="message-meta">
							<p><span class="meta-title">Series:</span><span class="meta-value"><?php echo get_the_term_list($post->ID, 'series'); ?></span></p>
							<p><span class="meta-title">Teacher:</span><span class="meta-value"><?php echo get_the_term_list($post->ID, 'teacher', '', ', '); ?></span></p>
							<p><span class="meta-title">Scripture:</span><span class="meta-value"><?php echo $meta['scripture']; ?></span></p>
							<p><span class="meta-title">Length:</span><span class="meta-value"><?php echo $meta['length']; ?></span></p>
							<p><span class="meta-title">Tags:</span><span class="meta-value"><?php echo the_tags('', ', '); ?></span></p>
						</div>
					</div>
					<div class="message-more share">
						<div class="message-share">
							<h3>Share</h3>
							<?php
							$theme->set('message', 'Watch "'.get_the_title().'" from @'.$theme->options('twitter_user').' at ');
							echo $theme->render('share');
							?>
						</div>
						<div class="message-download">
							<h3>Download</h3>
							<?php
							$downloader = $theme->info('base_url').'/download.php?';
							if (!empty($meta['video_url']) && !$meta['vimeo_url']) {
								echo $theme->Html->tag('a', '<span class="icon icon-download-video"></span>', array(
									'href' => $downloader.'file='.urlencode($meta['video_url']).'&filename='.urlencode(get_the_title()),
									'title' => 'Download Video'
								));
							}
							if (!empty($meta['audio_url'])) {
								echo $theme->Html->tag('a', '<span class="icon icon-download-1"></span>', array(
									'href' => $downloader.'file='.urlencode($meta['audio_url']).'&filename='.urlencode(get_the_title()),
									'title' => 'Download Audio'
								));
							}
							?>
						</div>
						<?php if (!empty($meta['audio_url'])): ?>
						<div class="message-audio">
							<h3>Listen</h3>
							<?php
							$theme->set('src', $meta['audio_url']);
							echo $theme->render('audio');
							?>
						</div>
						<?php endif; ?>
					</div>
					<footer class="message-related">
						<h1>Related</h1>
						<?php
						$theme->set('types', array('post', 'message', 'page', 'curriculum'));
						echo $theme->render('related_content');
						?>
					</footer>
				</div>
			</article>
		</section>
		<script type="text/javascript">
			jQuery('.icon-download-video').on('click', function() {
				ga('send', 'event', 'message-media', 'download', 'video', {
						'page': '<?php echo get_permalink(); ?>'
					}
				);
			});
			jQuery('.icon-download-1').on('click', function() {
				ga('send', 'event', 'message-media', 'download', 'audio', {
						'page': '<?php echo get_permalink(); ?>'
					}
				);
			});
		</script>
<?php
get_footer();
