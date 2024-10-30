/*
 * Admin-side option management functionality for Clarify Password Reset.
 */
jQuery( document ).ready( function( $ ) {

  $( '#flizcpr_warn' ).change( function() {
    if ( this.checked ) {
      $( '#flizcpr_warntext' ).prop( 'disabled', false );
    }
    else {
      $( '#flizcpr_warntext' ).prop( 'disabled', true );
    }
  });

});
