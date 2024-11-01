<?php
$options = array();

$options[] = array( 'name' => __( 'General', 'activecampaign' ), 'type' => 'heading' );

$options[] = array(
	'name' => __( 'Active Campaign URL', 'activecampaign' ),
	'id'   => ACAP_PREFIX . 'endpoint',
	'type' => 'text',
);

$options[] = array(
	'name' => __( 'API KEY', 'activecampaign' ),
	'id'   => ACAP_PREFIX . 'api_key',
	'type' => 'text',
);

$options[] = array(
	'name' => __( 'Event KEY', 'activecampaign' ),
	'id'   => ACAP_PREFIX . 'event_key',
	'type' => 'text',
);

$options[] = array(
	'name' => __( 'Event ID', 'activecampaign' ),
	'id'   => ACAP_PREFIX . 'event_id',
	'type' => 'text',
);

$options[] = array(
    'name' => __('Do not show tracking code', 'activecampaign'),
    'id'   => ACAP_PREFIX . 'dont_show_tracking_code',
    'type' => 'checkbox',
);
