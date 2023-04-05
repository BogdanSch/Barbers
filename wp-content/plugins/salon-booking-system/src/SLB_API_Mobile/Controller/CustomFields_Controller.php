<?php

namespace SLB_API_Mobile\Controller;

use WP_REST_Server;
use SLN_Enum_CheckoutFields;

class CustomFields_Controller extends REST_Controller
{
    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'custom-fields';

    public function register_routes() {

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/booking', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_booking_items' ),
                'permission_callback' => '__return_true',
            ),
            'schema' => array( $this, 'get_public_item_schema' ),
        ) );
    }

    public function get_booking_items()
    {
        $fields          = array();
        $booking_fields  = SLN_Enum_CheckoutFields::forBooking();

        foreach ( $booking_fields as $field ) {
            if ($field->isAdditional()) {
                $fields[] = $this->prepare_response_for_collection($field);
            }
        }

        return $this->success_response(array('items' => $fields));
    }

    public function prepare_response_for_collection($field)
    {
        $_options   = $field->getSelectOptions();
        $options    = array();

        foreach($_options as $value => $label) {
            if ($value === '' && $label === '') {
                continue;
            }
            $options[] = array(
                'value' => trim($value, "\r\n"),
                'label' => trim($label, "\r\n"),
            );
        }

        $response = array(
            'key'               => $field['key'],
            'label'             => __($field['label'], 'salon-booking-system'),
            'type'              => $field['type'],
            'required'          => $field['required'],
            'hidden'            => $field['hidden'],
            'options'           => $options,
            'customer_profile'  => $field['customer_profile'],
            'booking_hidden'    => $field['booking_hidden'],
            'export_csv'        => $field['export_csv'],
            'additional'        => $field['additional'],
            'default_value'     => $field['default_value'],
            'file_type'         => $field['file_type'],
        );

	return apply_filters('sln_api_custom_fields_prepare_response_for_collection', $response, $field);
    }

    public function get_item_schema()
    {
        $schema = array(
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'custom_field',
            'type'       => 'object',
            'properties' => array(
                'key' => array(
                    'description' => __( 'Unique identifier for the resource.', 'salon-booking-system' ),
                    'type'        => 'string',
                    'context'     => array( 'view', 'edit' ),
                    'arg_options' => array(
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
                'label' => array(
                    'description' => __( 'Label for the resource.', 'salon-booking-system' ),
                    'type'        => 'string',
                    'context'     => array( 'view', 'edit' ),
                    'arg_options' => array(
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
                'type' => array(
                    'description' => __( 'Type for the resource.', 'salon-booking-system' ),
                    'type'        => 'string',
                    'context'     => array( 'view', 'edit' ),
                    'enum'        => array(
                        'text',
                        'textarea',
                        'checkbox',
                        'select',
                        'file',
                        'html',
                    ),
                    'arg_options' => array(
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
                'required' => array(
                    'description' => __( 'Required for the resource.', 'salon-booking-system' ),
                    'type'        => 'boolean',
                    'context'     => array( 'view', 'edit' ),
                ),
                'hidden' => array(
                    'description' => __( 'Hidden for the resource.', 'salon-booking-system' ),
                    'type'        => 'boolean',
                    'context'     => array( 'view', 'edit' ),
                ),
                'options' => array(
                    'description' => __( 'Options for the resource.', 'salon-booking-system' ),
                    'type'        => 'array',
                    'context'     => array( 'view', 'edit' ),
                    'items'  => array(
                        'description' => __( 'The options item.', 'salon-booking-system' ),
                        'type'        => 'object',
                        'context'     => array( 'view', 'edit' ),
                        'required'    => array( 'value', 'label' ),
                        'properties'  => array(
                            'value' => array(
                                'description' => __( 'The value', 'salon-booking-system' ),
                                'type'        => 'string',
                                'context'     => array( 'view', 'edit' ),
                                'arg_options' => array(
                                    'required' => true,
                                ),
                            ),
                            'label' => array(
                                'description' => __( 'The label', 'salon-booking-system' ),
                                'type'        => 'string',
                                'context'     => array( 'view', 'edit' ),
                                'arg_options' => array(
                                    'required' => true,
                                ),
                            ),
                        ),
                    ),
                    'arg_options' => array(
                        'default'           => array(),
                        'validate_callback' => array($this, 'rest_validate_request_arg'),
                    ),
                ),
                'customer_profile' => array(
                    'description' => __( 'The customer profile for the resource.', 'salon-booking-system' ),
                    'type'        => 'boolean',
                    'context'     => array( 'view', 'edit' ),
                    'arg_options' => array(
                        'default' => false,
                    ),
                ),
                'booking_hidden' => array(
                    'description' => __( 'The booking hidden for the resource.', 'salon-booking-system' ),
                    'type'        => 'boolean',
                    'context'     => array( 'view', 'edit' ),
                    'arg_options' => array(
                        'default' => false,
                    ),
                ),
                'export_csv' => array(
                    'description' => __( 'The export csv for the resource.', 'salon-booking-system' ),
                    'type'        => 'boolean',
                    'context'     => array( 'view', 'edit' ),
                    'arg_options' => array(
                        'default' => false,
                    ),
                ),
                'additional' => array(
                    'description' => __( 'The additional for the resource.', 'salon-booking-system' ),
                    'type'        => 'boolean',
                    'context'     => array( 'view', 'edit' ),
                    'arg_options' => array(
                        'default' => false,
                    ),
                ),
                'default_value' => array(
                    'description' => __( 'The default value for the resource.', 'salon-booking-system' ),
                    'type'        => 'string',
                    'context'     => array( 'view', 'edit' ),
                    'arg_options' => array(
                        'sanitize_callback' => 'sanitize_text_field',
                        'default'           => '',
                    ),
                ),
                'file_type' => array(
                    'description' => __( 'The file type for the resource.', 'salon-booking-system' ),
                    'type'        => 'string',
                    'context'     => array( 'view', 'edit' ),
                    'arg_options' => array(
                        'sanitize_callback' => 'sanitize_text_field',
                        'default'           => '',
                    ),
                ),
            )
        );

        return apply_filters('sln_api_custom_fields_get_item_schema', $schema);
    }

}