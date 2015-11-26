<ul class="ccbpress_upcoming_events <?php echo $events->widget_options->theme; ?>_theme">
	<?php
	/**
	 * Lets start our event count at 0
	 */
	$found_events = 0; ?>
	<?php
	/**
	 * Start the output buffer
	 */
	ob_start(); ?>
	<?php
	/**
	 * Loop through all of the events
	 */
	foreach ($events->events as $event) :  ?>
		<li>
			<?php
			/**
			 * Check which style to use
			 */
			if ( $events->widget_options->theme == 'graphical') : ?>
				<div class="ccbpress_upcoming_events_graphical_date">
					<div><?php echo date( 'M', strtotime( $event->date ) ); ?></div>
					<?php echo date( 'j', strtotime( $event->date ) ); ?>
				</div>
				<?php
				/**
				 * Check if the single event page is set in the Customizer settings
				 */
				if ( ccbpress_is_single_event_page_set() ) : ?>
					<div class="ccbpress_upcoming_events_graphical_name">
						<a href="<?php echo ccbpress_get_event_url( $event->event_name['ccb_id'] ); ?>" title="<?php echo $event->event_name; ?>"><?php echo $event->event_name; ?></a>
					</div>
				<?php else : ?>
					<div class="ccbpress_upcoming_events_graphical_name">
						<?php echo $event->event_name; ?>
					</div>
				<?php endif; ?>
				<div class="ccbpress_upcoming_events_graphical_detail"><?php echo ccbpress_get_upcoming_event_time( $event->start_time, $event->end_time ); ?><?php if ( $event->location != '' ) { echo ' | ' .  $event->location; } ?></div>
				<div class="ccbpress_upcoming_events_clear"></div>
			<?php else : ?>
				<?php
				/**
				 * Check if the single event page is set in the Customizer settings
				 */
				if ( ccbpress_is_single_event_page_set() ) : ?>
					<a href="<?php echo ccbpress_get_event_url( $event->event_name['ccb_id'] ); ?>"><?php echo $event->event_name; ?></a><br />
				<?php else : ?>
					<?php echo $event->event_name; ?><br />
				<?php endif; ?>
				<?php echo ccbpress_get_upcoming_event_time( $event->start_time, $event->end_time ); ?><?php if ( $event->location != '' ) { echo ' | ' .  $event->location; } ?>
			<?php endif; ?>
		</li>
		<?php
		/**
		 * Increase our event count by 1
		 */
		$found_events++; ?>
	<?php endforeach; ?>
	<?php
	/**
	 * Clean the output buffer and save it to a variable
	 */
	$upcoming_events = ob_get_clean(); ?>
	<?php
	/**
	 * Only display the registration form section if we have at least 1 registration form
	 */
	if ( $found_events > 0 ) : ?>
		<?php echo $upcoming_events; ?>
	<?php else : ?>
		<li>
			<?php echo apply_filters( 'ccbpress_upcoming_events_widget_no_events_text', __('No upcoming events are scheduled', 'ccbpress') ); ?>
		</li>
	<?php endif; ?>
	<?php
	/**
	 * Show the calendar link if it's turned on and the Calendar page is set up.
	 */
	if ( $events->widget_options->show_calendar_link == 'show' && ccbpress_is_event_page_set() ) : ?>
		<div class="ccbpress_upcoming_events_calendar_link">
			<a href="<?php echo ccbpress_get_events_url(); ?>"><?php echo apply_filters( 'ccbpress_upcoming_events_widget_view_calendar_text', __('Go To Calendar', 'ccbpress' ) ); ?></a>
		</div>
	<?php endif; ?>
</ul>
