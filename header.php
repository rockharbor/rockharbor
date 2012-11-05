<?php global $post, $theme; ?>
<?php get_template_part('header', 'prebody') ?>
<body <?php body_class(); ?>>

	
	<div id="page" class="hfeed clearfix">

		<?php
		echo $theme->render('global_navigation');
		?>
		
		<header id="branding" role="banner" class="clearfix">
			<?php 
			if (is_front_page()) {
				$meta = $theme->metaToData($post->ID);
				$locations = get_nav_menu_locations();
				if (isset($locations['featured'])) {
					$items = wp_get_nav_menu_items('featured');
					$first = $items[0];
					if (!empty($meta['first_featured_story_height'])) {
						$theme->set('height', $meta['first_featured_story_height']);
					}
					$theme->set('id', $first->object_id);
					$theme->set('title', $first->title);
					$theme->set('type', $first->object);
					$banner = $theme->Html->tag('div', $theme->render('story_box'));
					
					echo $theme->Html->tag('div', $banner, array(
						'id' => 'main-feature',
						'class' => 'clearfix'
					));
				}
			}
			?>
			<nav id="access" role="navigation" class="clearfix">
				<?php
				$locations = get_nav_menu_locations();
				$menu = wp_get_nav_menu_object($locations['main']);
				$menu_items = wp_get_nav_menu_items($menu->term_id);
				_wp_menu_item_classes_by_context($menu_items);
				$menu = array();
				$ids = array();
				foreach ($menu_items as $key => $menu_item) {
					$a = $theme->Html->tag('a', $menu_item->title, array('href' => $menu_item->url));
					$opts = array(
						'class' => implode(' ', $menu_item->classes)
					);
					if ($menu_item->menu_item_parent == 0) {
						// top level
						$menu[] = array(
							'a' => $a,
							'opts' => $opts,
							'children' => array()
						);
						$ids[$menu_item->ID] = count($menu)-1;
					} else {
						// child
						$menu[$ids[$menu_item->menu_item_parent]]['children'][] = $theme->Html->tag('li', $a, $opts);
					}
				}
				$output = '';
				$max_row = 5;
				foreach ($menu as $key => $top_level_menu_item) {
					$children = null;
					if (!empty($top_level_menu_item['children'])) {
						$class = null;
						if (count($top_level_menu_item['children']) > $max_row) {
							$top_level_menu_item['children'] = array_chunk($top_level_menu_item['children'], $max_row);
							foreach ($top_level_menu_item['children'] as $col) {
								$children .= $theme->Html->tag('ul', implode('', $col));
							}
							$class = 'cols'.count($top_level_menu_item['children']);
						} else {
							$children = $theme->Html->tag('ul', implode('', $top_level_menu_item['children']));
						}
						$children = $theme->Html->tag('div', $children, array('class' => 'submenu '.$class));
					}
					$output .= $theme->Html->tag('li', $top_level_menu_item['a'].$children, $top_level_menu_item['opts']);
				}
				echo $theme->Html->tag('ul', $output, array('class' => 'menu clearfix'));
				?>
			</nav>
			<?php 
			if (is_front_page()) {
				$meta = $theme->metaToData($post->ID);
				$locations = get_nav_menu_locations();
				if (isset($locations['featured'])) {
					echo '<section id="featured">';
					$h1 = $theme->Html->tag('h1', 'Featured Content');
					echo $theme->Html->tag('header', $h1);
					echo '<div class="stories-3">';
					$items = wp_get_nav_menu_items('featured');
					// only items 2,3,4 allowed
					$items = array_slice($items, 1, 3);
					foreach ($items as $item) {
						$theme->set('height', null);
						$theme->set('id', $item->object_id);
						$theme->set('title', $item->title);
						$theme->set('type', $item->object);
						echo $theme->render('story_box');
					}
					echo '</div>';
					echo '</section>';
				}
			}
			?>
		</header>
		
		<?php 
		if (isset($_SESSION['message'])) {
			echo $theme->Html->tag('div', $_SESSION['message'], array('class' => 'flash-message'));
			unset($_SESSION['message']);
		}
		?>

		<div id="main" class="clearfix">