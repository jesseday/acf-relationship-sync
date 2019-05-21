<?php

namespace RelationshipSync\Listeners;

class ManyToMany {


    const LOCK = 'many_to_many_field_is_updating';

    /**
     * An array of related field arrays.
     *
     * @var array
     */
    private $related_fields;

    public function __construct() {
        add_action( 'plugins_loaded', [ $this, 'get_related_fields' ] );
        $this->add_filters();
    }

    public function get_related_fields() {
        $fields               = apply_filters( 'acf_relationship_sync/related_fields/many_to_many', [] );
        $this->related_fields = $fields ?? [];
    }

    public function add_filters() {
        add_filter( 'acf/update_value', [
            $this,
            'sync',
        ], 10, 3 );
    }

    /**
     * When a multi value relationship field is being saved, check for
     * corresponding single value post object fields that need to be kept in
     * sync, and update.
     *
     * @param $value
     * @param $post_id
     * @param $field
     *
     * @return mixed
     */
    public function sync( $value, $post_id, $field ) {
        if ( ! $this->should_sync( $field, $post_id, $value ) ) {
            return $value;
        }

        // Bail early if this filter was triggered from this class.
        if ( ! empty( $GLOBALS[ self::LOCK ] ) ) {
            return $value;
        }

        // Set global variable to avoid inifinite loop
        $GLOBALS[ self::LOCK ] = 1;

        $related_fields = $this->get_related( $field['name'] );
        //        foreach ( $related_fields as $set ) {
        //            $field_set = new \RelationshipSync\Relationships\OneToMany( $set['single_value_field'], $set['multi_value_field'] );
        //
        //            $field_set->update_one_on_many( $value, $post_id );
        //            $field_set->remove_one_from_many( $value, $post_id );
        //        }


        // Reset global variable to allow this filter to function as per normal.
        $GLOBALS[ self::LOCK ] = 0;

        return $value;
    }

    /**
     * @param $field
     * @param $post_id
     * @param $value
     *
     * @return bool|mixed|void
     */
    private function should_sync( $field, $post_id, $value ) {
        $should_sync = FALSE;
        $should_sync = in_array( $field['name'], array_filter( $this->related_fields, function ( $field_set ) use ( $field ) {
            return in_array( $field['name'], $field_set );
        } ) );

        return apply_filters( 'acf_relationship_sync/should_sync/many_to_many', $should_sync, $field, $post_id, $value );
    }

    /**
     * Get the sets of fields that contain a field with the given name and type.
     *
     * @param $field
     *
     * @return array
     */
    private function get_related( $field ) {
        $related_sets =  array_filter( $this->related_fields, function ( $set ) use ( $field ) {
            return in_array( $field, $set );
        } );

        return apply_filters('acf_relationship_sync/get_related/many_to_many', $related_sets, $field );
    }
}

new ManyToMany();
