<?php

namespace RelationshipSync\Relationships;

class OneToMany {

    private $single_value_field;

    private $multi_value_field;

    public function __construct( $single_value_field, $multi_value_field ) {
        $this->single_value_field = acf_get_field( $single_value_field );
        $this->multi_value_field  = acf_get_field( $multi_value_field );
    }

    public function single_value_field() {
        return $this->single_value_field;
    }

    public function multi_value_field() {
        return $this->multi_value_field;
    }

    public function update_many_on_one( $reference_post_id, $post_id ) {
        if ( empty( $post_id ) ) {
            return;
        }

        $existing_references = get_field( $this->multi_value_field['name'], $post_id, FALSE );
        if ( is_array( $existing_references ) && in_array( $reference_post_id, $existing_references ) ) {
            return;
        }

        $existing_references = is_array( $existing_references ) ? $existing_references : array_filter( [ $existing_references ] );
        $updated_references  = $existing_references + [ $reference_post_id ];
        update_field( $this->multi_value_field['key'], $updated_references, $post_id );
    }

    public function remove_many_from_one( $new_value, $post_id ) {
        $old_value = get_field( $this->single_value_field['name'], $post_id, FALSE );
        if ( $old_value == $new_value ) {
            return;
        }

        if ( empty( $old_value ) ) {
            return;
        }

        $existing_references = get_field( $this->multi_value_field['name'], $old_value, FALSE );
        $existing_references = is_array( $existing_references ) ? $existing_references : array_filter( [ $existing_references ] );
        if ( empty( $existing_references ) ) {
            return;
        }

        $updated_references = array_diff( $existing_references, [ $post_id ] );
        update_field( $this->multi_value_field['key'], $updated_references, $old_value );
    }

    /**
     * Add references back to the current post in a single value field, based
     * on the posts referenced in a multi value field.
     *
     * @param $posts
     * @param $reference_post_id
     */
    public function update_one_on_many( $posts, $reference_post_id ) {
        if ( ! is_array( $posts ) ) {
            return;
        }

        // We only need to worry about new values, as old values should already
        // be linked.
        $old_values = get_field( $this->multi_value_field['name'], $reference_post_id, FALSE );
        $old_values = is_array( $old_values ) ? $old_values : array_filter( [ $old_values ] );
        $new_values = array_diff( $posts, $old_values );

        foreach ( $new_values as $post ) {
            update_field( $this->single_value_field['key'], $reference_post_id, $post );
        }
    }

    /**
     * Remove references back to the current post in a single value field, based
     * on any posts removed from a multi value field.
     *
     * @param $posts
     * @param $reference_post_id
     */
    public function remove_one_from_many( $posts, $reference_post_id ) {
        if ( ! is_array( $posts ) ) {
            $posts = array_filter( [ $posts ] );
        }

        $old_values    = get_field( $this->multi_value_field['name'], $reference_post_id, FALSE );
        $old_values    = is_array( $old_values ) ? $old_values : array_filter( [ $old_values ] );
        $removed_posts = array_diff( $old_values, $posts );

        foreach ( $removed_posts as $post ) {
            update_field( $this->single_value_field['key'], NULL, $post );
        }
    }
}
