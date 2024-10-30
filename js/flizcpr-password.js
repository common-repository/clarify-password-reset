/* Version 1.2 */
/*
 * Clears the auto-suggested password to avoid confusing users.
 *
 * Provides a button for requesting a suggested password.
 *
 * Turns the user_login hidden field into a read-only text field and
 * hides it with CSS instead. (This helps the browser to recognise the
 * submitted username and password so it can offer to save them.)
 */
jQuery( document ).ready( function( $ ) {

  /* Clear the initial password suggestion from WordPress */
  $( '#resetpassform input[name=\'pass1\']' ).attr( 'data-pw', '' )

  if ( 'off' !== flizcpr.savefix ) {
    /* Hide the username with CSS, instead of as a hidden form field. */  
      // Dumb-browser-friendly solution is to wrap field in hidden div
    $( '#resetpassform input[id=\'user_login\']' ).wrap( 
             '<div style="display:none">')
      // Browsers are more likely to recognise the user ID if it's a text field
    $( '#resetpassform input[id=\'user_login\']' ).attr( 'type', 'text' )
      // Just in case, make sure the textfield isn't editable by the user
    $( '#resetpassform input[id=\'user_login\']' ).attr( 'readonly', true )
  }

  /* Generate a random password if the user asks for one */
  $( '#flizcpr-button' ).click( function() {
    // First clear the box
    $( '#resetpassform input[name=\'pass1-text\']' ).val( '' )
    // Now ask WordPress for a new password
    $.ajax( {
      url: flizcpr.ajaxurl,
      dataType: 'text',
      data: {
        action: 'get_password_suggestion',
        security: flizcpr_pw_nonce.security
      },
      success:function( data ) {
        // NB: WP 5.3+ uses 'pass1', not 'pass1-text'
        if ( ( '-1' === data ) || ( '0' === data ) ) {
          // Something went wrong at the server
          $( '#resetpassform input[name=\'pass1-text\']' ).val( 'ERROR 1' )
          $( '#resetpassform input[name=\'pass1\']' ).val( 'ERROR 1' )
        }
        else {
          // Put suggested password in box
          $( '#resetpassform input[name=\'pass1-text\']' ).val( data )
          $( '#resetpassform input[name=\'pass1\']' ).val( data )

          // WP 4.3 uses 'keyup' to trigger strength check/fields sync 
          $( '#resetpassform input[name=\'pass1-text\']' ).trigger( 'keyup' )

          // WP 4.3.1+ uses 'input' to trigger strength check/fields sync 
          // (and WP 5.3+ uses 'pass1', not 'pass1-text')
          $( '#resetpassform input[name=\'pass1-text\']' ).trigger( 'input' )
          $( '#resetpassform input[name=\'pass1\']' ).trigger( 'input' )
        }
        // Release the pressed button
        $( '#flizcpr-button' ).blur()
      }, 
      error: function( errorThrown ) {
        // Something went wrong making the call 
        $( '#resetpassform input[name=\'pass1-text\']' ).val( 'ERROR 2' )

        // Release the pressed button
        $( '#flizcpr-button' ).blur()
      }
    });
  });
});
