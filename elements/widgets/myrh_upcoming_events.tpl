<?php
/**
 * myRH Upcoming Events widget
 *
 * @package rockharbor
 * @subpackage rockharbor.libs.widgets
 * @version 0.1
 */

global $ccbpress_ccb;
extract($myrh_upcoming_events);

$date_start = date('Y-m-d', current_time('timestamp'));

switch ($daterange) {
    case 'today':
        // The end date is the end of today
        $date_end = date('Y-m-d', current_time('timestamp'));
        break;
    default:
        // The end date is sometime in the future
        $daterange_string = '+' . $daterange; // +4 weeks, e.g.
        $date_end = date('Y-m-d', strtotime($daterange_string, current_time('timestamp')));
        break;
}

$args = array(
    'date_start' => $date_start,
    'date_end' => $date_end
);

$ccbpress_data = $ccbpress_ccb->public_calendar_listing($args);

// Define the array to hold all found events
$found_events = array();

// Keep track of how many events have been found
$how_many_found = 0;

if ((int) $ccbpress_data->response->items['count'] == 0 || strlen($ccbpress_data->response->errors->error > 0)) {
    $found_events = '';
} else {
    // Loop through the events
    foreach ($ccbpress_data->response->items->item as $event) {
        // Get the event group id
        $event_group_id = (string) $event->group_name['ccb_id'];
        $event_group_type = (string) $event->group_type;
        $event_department = (string) $event->grouping_name;

        // See if we have found as many as we need
        if ($how_many_found < $howmany) {
            switch ($filterby) {
                case 'group':
                    // Check that it is the correct group id
                    if (in_array($event_group_id, $groupid) || in_array('ccbpress_all_groups', $groupid)) {
                        if (strtotime($event->date . ' ' . $event->start_time, current_time('timestamp')) > current_time('timestamp')) {
                            // Add the event to the $found_events array
                            $found_events[$how_many_found] = $event;

                            // Increase the events found by 1
                            $how_many_found++;
                        }
                    }
                    break;
                case 'group_type':
                    // Check that it is the correct group type
                    if (in_array(addslashes($event_group_type), $grouptype) || in_array('ccbpress_all_grouptypes', $grouptype)) {
                        if (strtotime($event->date . ' '. $event->start_time, current_time('timestamp')) > current_time('timestamp')) {
                            // Add the event to the $found_events array
                            $found_events[$how_many_found] = $event;

                            // Increase the events found by 1
                            $how_many_found++;
                        }
                    }
                    break;
                case 'department':
                    // Check that it is the correct department
                    if (in_array(addslashes($event_department), $department) || in_array('ccbpress_all_departments', $department)) {
                        if (strtotime($event->date . ' ' . $event->start_time, current_time('timestamp')) > current_time('timestamp')) {
                            // Add the event to the $found_events array
                            $found_events[$how_many_found] = $event;

                            // Increase the events found by 1
                            $how_many_found++;
                        }
                    }
                    break;
            }
        } else {
            break;
        }
    }

    unset($ccbpress_data);
}

// Setup the object to hold the events
$found_events_object = new stdClass();

// Set the values passed from the widget options
$found_events_object->widget_options = new stdClass();
$found_events_object->widget_options->show_calendar_link = $showcalendarlink;
if ( $widgettheme == '') {
    $widgettheme = 'text';
}
$found_events_object->widget_options->theme = $widgettheme;

// Add the events to the object
$found_events_object->events = $found_events;

// Free up some memory
unset($found_events);

// Echo the event data and apply any filters
echo apply_filters('ccbpress_upcoming_events_widget', $found_events_object);
?>