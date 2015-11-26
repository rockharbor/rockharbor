<p>
    <label for="<?php echo $widget->get_field_id('title'); ?>">Title:</label>
    <input
        class="widefat"
        id="<?php echo $widget->get_field_id('title'); ?>"
        name="<?php echo $widget->get_field_name('title'); ?>"
        value="<?php echo esc_attr($data['title']); ?>" />
</p>
<p class="ccbpress-select-multiple">
    <label for="<?php echo $widget->get_field_id('campus'); ?>">Campus:</label>
    <?php echo ccbpress_get_campus_list_dropdown($data['campusid'], $widget->get_field_id('campusid'), $widget->get_field_name('campusid') . '[]', 'widefat', true); ?>
</p>
<p>
    <label for="<?php echo $widget->get_field_id( 'filterby' ); ?>">Filter by:</label>
    <select id="<?php echo $widget->get_field_id( 'filterby' ); ?>" name="<?php echo $widget->get_field_name( 'filterby' ); ?>">
        <option <?php selected( $data['filterby'], 'group' ); ?> value="group">group</option>
        <option <?php selected( $data['filterby'], 'group_type' ); ?> value="group_type">group type</option>
        <option <?php selected( $data['filterby'], 'department' ); ?> value="department">department</option>
    </select>
</p>
<p class="ccbpress-display-from-group-<?php echo $widget->id; ?> ccbpress-select-multiple">
    <label for="<?php echo $widget->get_field_id( 'groupid' ); ?>">Display Events From:</label>
    <?php ccbpress_get_groups_dropdown_options($data['groupid'], $widget->get_field_id( 'groupid' ), $widget->get_field_name( 'groupid' ) . '[]', 'widefat', true, true); ?>
</p>
<p class="ccbpress-display-from-group_type-<?php echo $widget->id; ?> ccbpress-select-multiple">
    <label for="<?php echo $widget->get_field_id( 'grouptype' ); ?>">Display Events From:</label>
    <select id="<?php echo $widget->get_field_id( 'grouptype' ); ?>" name="<?php echo $widget->get_field_name( 'grouptype' ); ?>[]" multiple>
        <option <?php selected( in_array( 'ccbpress_all_grouptypes', $data['grouptype'] ) );?> value="ccbpress_all_grouptypes">ALL GROUP TYPES</option>
        <?php
        $group_types_array = ccbpress_get_group_type_list( true );
        foreach ( $group_types_array as $key => $value ) {
        ?>
            <option <?php selected( in_array( $value, $data['grouptype'] ) );?> value="<?php echo esc_attr( $value ); ?>"><?php echo $value; ?></option>
        <?php
        }
        ?>
    </select>
</p>
<p class="ccbpress-display-from-department-<?php echo $widget->id; ?> ccbpress-select-multiple">
    <label for="<?php echo $widget->get_field_id( 'department' ); ?>">Display Events From:</label>
    <select id="<?php echo $widget->get_field_id( 'department' ); ?>" name="<?php echo $widget->get_field_name( 'department' ); ?>[]" multiple>
        <option <?php selected( in_array( 'ccbpress_all_departments', $data['department'] ) );?> value="ccbpress_all_departments">ALL DEPARTMENTS</option>
        <?php
        $departments_array = ccbpress_get_department_list( true );
        foreach ( $departments_array as $key => $value ) {
        ?>
            <option <?php selected( in_array( addslashes($value), $data['department'] ) );?> value="<?php echo esc_attr( $value ); ?>"><?php echo $value; ?></option>
        <?php
        }
        ?>
    </select>
</p>
<p>
    <label for="<?php echo $widget->get_field_id( 'theme' ); ?>">Theme events as </label>
    <select id="<?php echo $widget->get_field_id( 'theme' ); ?>" name="<?php echo $widget->get_field_name( 'theme' ); ?>">
        <option <?php selected( $data['theme'], 'text' ); ?> value="text">plain text</option>
        <option <?php selected( $data['theme'], 'graphical' ); ?> value="graphical">graphical</option>
    </select>
</p>
<p>
    <label for="<?php echo $widget->get_field_id( 'daterange' ); ?>">Date range:</label>
    <select id="<?php echo $widget->get_field_id( 'daterange' ); ?>" name="<?php echo $widget->get_field_name( 'daterange' );  ?>">
        <option <?php selected( $data['daterange'], 'today' ); ?> value="today">today</option>
        <?php for ( $i = 1; $i <= 52; $i++ ) : ?>
        <option <?php selected( $data['daterange'], $i . ' ' . _n( 'week', 'weeks', $i ) ); ?> value="<?php echo $i . ' ' . _n( 'week', 'weeks', $i ); ?>"><?php echo $i . ' ' . _n( 'week', 'weeks', $i ); ?></option>
        <?php endfor; ?>
    </select>
</p>
<p>
    <label for="<?php echo $widget->get_field_id( 'howmany' ); ?>">Display up to </label>
    <select id="<?php echo $widget->get_field_id( 'howmany' ); ?>" name="<?php echo $widget->get_field_name( 'howmany' );  ?>">
        <?php for ( $i = 1; $i <= 100; $i++ ) : ?>
        <option <?php selected( $data['howmany'], $i ); ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
        <?php endfor; ?>
    </select>
    <label for="<?php echo $widget->get_field_id( 'howmany' ); ?>">events</label>
</p>
<p>
    <select id="<?php echo $widget->get_field_id( 'showcalendarlink' ); ?>" name="<?php echo $widget->get_field_name( 'showcalendarlink' ); ?>">
        <option <?php selected( $data['showcalendarlink'], 'hide' ); ?> value="hide">Hide</option>
        <option <?php selected( $data['showcalendarlink'], 'show' ); ?> value="show">Show</option>
    </select>
    <label for="<?php echo $widget->get_field_id( 'showcalendarlink' ); ?>"> link to event calendar</label>
</p>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#widgets-right .ccbpress-select-multiple select').select2ccbpress({
		multiple: 'true',
		width: '100%'
	});

	jQuery('#widgets-right').find('[id^=widget-myrh_upcoming_events-][id$=-filterby]').change( function() {
		var thisString = jQuery( this ).attr('id').replace('-filterby', '').replace('widget-', '');
		switch( jQuery( this ).val() ) {
			case 'group':
				jQuery('#widgets-right .ccbpress-display-from-group-' + thisString).show();
				jQuery('#widgets-right .ccbpress-display-from-group_type-' + thisString).hide();
				jQuery('#widgets-right .ccbpress-display-from-department-' + thisString).hide();
				break;
			case 'group_type':
				jQuery('#widgets-right .ccbpress-display-from-group-' + thisString).hide();
				jQuery('#widgets-right .ccbpress-display-from-group_type-' + thisString).show();
				jQuery('#widgets-right .ccbpress-display-from-department-' + thisString).hide();
				break;
			case 'department':
				jQuery('#widgets-right .ccbpress-display-from-group-' + thisString).hide();
				jQuery('#widgets-right .ccbpress-display-from-group_type-' + thisString).hide();
				jQuery('#widgets-right .ccbpress-display-from-department-' + thisString).show();
				break;
		}
	});

	jQuery('#widgets-right').find('[id^=widget-myrh_upcoming_events-][id$=-filterby]').each( function( index ) {
		var thisString = jQuery( this ).attr('id').replace('-filterby', '').replace('widget-', '');
		switch( jQuery( this ).val() ) {
			case 'group':
				jQuery('#widgets-right .ccbpress-display-from-group-' + thisString).show();
				jQuery('#widgets-right .ccbpress-display-from-group_type-' + thisString).hide();
				jQuery('#widgets-right .ccbpress-display-from-department-' + thisString).hide();
				break;
			case 'group_type':
				jQuery('#widgets-right .ccbpress-display-from-group-' + thisString).hide();
				jQuery('#widgets-right .ccbpress-display-from-group_type-' + thisString).show();
				jQuery('#widgets-right .ccbpress-display-from-department-' + thisString).hide();
				break;
			case 'department':
				jQuery('#widgets-right .ccbpress-display-from-group-' + thisString).hide();
				jQuery('#widgets-right .ccbpress-display-from-group_type-' + thisString).hide();
				jQuery('#widgets-right .ccbpress-display-from-department-' + thisString).show();
				break;
		}
	});
});
</script>
