<?php
/**
 * @author            Kona Macphee <kona@fidgetylizard.com>
 * @since             1.0.0
 * @package           Clarify_Password_Reset
 *
 * @wordpress-plugin
 * Plugin Name:       Clarify Password Reset
 * Plugin URI:        https://wordpress.org/plugins/clarify-password-reset/
 * Description:       Clears initial suggested password on WP 4.3+ password reset page, and adds a Generate Password button. Fixes new-password save bug (Firefox/Chrome).
 * Version:           2.0
 * Author:            Fidgety Lizard
 * Author URI:        https://fidgetylizard.com
 * Contributors:      fliz, kona
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       clarify-password-reset
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

if ( ! class_exists( 'Clarify_Password_Reset' ) )
{
  class Clarify_Password_Reset
  {
    /**
     * Construct the plugin object.
     */
    public function __construct()
    {
      // We want our front-end scripts queued on the login pages only
      add_action( 'login_enqueue_scripts', array( $this, 'add_scripts' ) );  

      // Enqueue admin scripts
      add_action( 'admin_enqueue_scripts', 
                array( $this, 'admin_add_scripts' ) );  

      // Make sure our new button is present
      add_filter( 'password_hint', array( $this, 'add_password_button' ), 20 );
      add_filter( 'password_hint', array( $this, 'add_password_warning' ), 21 );

      // Prepare for i18n translations
      add_action( 'plugins_loaded', array( $this, 'load_my_textdomain' ) );

      // Enable the plugin admin menu
      add_action( 'admin_menu', array( $this, 'admin_menu' ) );

      // Make sure admin-ajax.php knows about our password generator
      if ( is_admin() ) {

        // Register this method for not-logged-in users 
        add_action( 
          'wp_ajax_nopriv_get_password_suggestion', 
          array( $this, 'get_password_suggestion' ) 
        );
        // Register this method for logged-in users too - just in case e.g. 
        // an admin is doing some testing
        add_action( 
          'wp_ajax_get_password_suggestion', 
          array( $this, 'get_password_suggestion' ) 
        );
      }
    } // END public function __construct


    /**
     * Activate the plugin.
     */
    public static function activate()
    {
      // Initialise our admin preferences if not already configured 
      // Both optional features should be switched ON by default
      if (FALSE === get_option( 'flizcpr_savefix' ) ) {
        update_option( 'flizcpr_savefix', 'on' );
      }
      if ( FALSE === get_option( 'flizcpr_warn' ) ) {
        update_option( 'flizcpr_warn', 'on' );
      }
    } // END public static function activate


    /**
     * Deactivate the plugin.
     */
    public static function deactivate()
    {
      // Nothing to do here
    } // END public static function deactivate


    /**
     * Set things up for i18n.
     */
    public function load_my_textdomain() 
    {
      load_plugin_textdomain( 
        'clarify-password-reset', 
        FALSE, 
        basename( dirname( __FILE__ ) ) . '/languages/' 
      );
    } // END public function load_my_textdomain


    /**
     * Set up the necessary front-end CSS and JS.
     */
    public function add_scripts()
    {
      // Add the CSS that styles the "generate password" button
      wp_enqueue_style(
        'flizcpr-login-styles',
        plugin_dir_url( __FILE__ ) . 'css/flizcpr-login-styles.css',
        false
      );

      // Add the JS that does the JQuery/Ajax password field tweaking
      wp_enqueue_script(
        'flizcpr-password-js',
        plugin_dir_url( __FILE__ ) . 'js/flizcpr-password.js',
        array( 'jquery' ) // Depends on jquery
      );

      // Make sure that our JS can find the Ajax endpoint, and knows whether
      // to attempt the browser fix
      wp_localize_script( 
        'flizcpr-password-js', 
        'flizcpr', 
        array( 
          'ajaxurl' => admin_url( 'admin-ajax.php' ),
          'savefix' => get_option( 'flizcpr_savefix' )
         ) 
      );

      // Do the right thing and create a nonce for our Ajax access 
      $flizcpr_pw_nonce = wp_create_nonce('flizcpr_pw_nonce');
      echo 
        "<!-- Clarify Password Reset -->\n" .
        "<script type='text/javascript'>\n" .
        "var flizcpr_pw_nonce = { 'security': '" .
        esc_js($flizcpr_pw_nonce) ."' }\n" .
        "</script>\n".
        "<!-- Clarify Password Reset -->\n";
    } // END public function add_scripts
 

    /**
     * Add a translatable "generate password" button, to appear in the 
     * password reset form just before the password hint.
     */
    public function add_password_button( $hint )
    {
      $button = 
          '<div class="flizcpr-button-container">'.
            '<a href="#" id="flizcpr-button" class="flizcpr-button-style">'.
            esc_html__('Suggest a password', 'clarify-password-reset').'</a>'.
          '</div>';
      return( $button . $hint );
    } // END public function add_password_button


    /**
     * Add a warning to users that they should make a note of their password.
     */
    public function add_password_warning( $hint )
    {
      $warn = '';
      if ('off' !== get_option( 'flizcpr_warn' ) ) {
        // Warning is not disabled in the admin preferences, so proceed
        $warn = '<div class="flizcpr-browser-warn"><p>';
        $customWarning = stripslashes( get_option( 'flizcpr_warntext' ) );
        if ('' != $customWarning ) {
          // Use admin-specified warning if available
          $warn = $warn . esc_html( $customWarning );
        }
        else {
          $warn = $warn .
             esc_html__('Please make a note of your new password, because your browser might not offer to save it.',
                     'clarify-password-reset');
        }
        $warn = $warn .  '</p></div>';
      }
      return( $hint . $warn );
    } // END public function add_password_warning


    /**
     * Called via Ajax.  Generates a new password using standard WP method.
     */
    public function get_password_suggestion() 
    {
      check_ajax_referer( 'flizcpr_pw_nonce', 'security' );
      echo wp_generate_password();
      wp_die();
    } // END public function get_password_suggestion


    /**
     * Set up the necessary admin-end CSS and JS.
     */
    public function admin_add_scripts()
    {
      global $pagenow;

      // Only add the scripts on our actual admin page
      if ( ( $pagenow == 'options-general.php' ) &&
            ( isset( $_GET['page'] ) && 
                   ( 'clarify_password_reset' == $_GET[ 'page' ] ) 
            )
      ) {

        // Add the admin CSS 
        wp_enqueue_style(
          'flizcpr-admin-styles',
          plugin_dir_url( __FILE__ ) . 'css/flizcpr-admin-styles.css',
          false
        );

        // Add the admin JS
        wp_enqueue_script(
          'flizcpr-admin-js',
          plugin_dir_url( __FILE__ ) . 'js/flizcpr-admin.js',
          array( 'jquery' ) // Depends on jquery
        );
      }
    } // END public function admin_add_scripts


    /**
     * Create a menu entry for our administration page.
     */
    public function admin_menu() 
    {
      add_options_page( 
          esc_html__('Clarify Password Reset Settings', 
                        'clarify-password-reset' ),
          esc_html__('Clarify Password Reset', 
                        'clarify-password-reset' ),
          'manage_options',
          'clarify_password_reset', 
          array( $this, 'admin_settings_page')
      );
    } // END public function admin_menu


    /**
     * Create our administration page and manage its options.
     */
    public function admin_settings_page()
    {
      $showNotice = FALSE;
      // If we've got a form submission, set all options accordingly
      if ( isset( $_POST['flizcpr__s'] ) ) {
        $showNotice = TRUE;
        if ( isset( $_POST['flizcpr_savefix'] ) ) {
          update_option( 'flizcpr_savefix', 'on' );
        }
        else {
          update_option( 'flizcpr_savefix', 'off' );
        }
        if ( isset( $_POST['flizcpr_warn'] ) ) {
          update_option( 'flizcpr_warn', 'on' );
        }
        else {
          update_option( 'flizcpr_warn', 'off' );
        }
        if ( isset( $_POST['flizcpr_warntext'] ) ) {
          $newtext = sanitize_text_field( $_POST['flizcpr_warntext'] );
          update_option( 'flizcpr_warntext', $newtext );
        }
      }
      // If unset in database, these features default to enabled 
      $fixChecked = '';
      $warnChecked = '';
      $warnDisabled = ' disabled';
      if ('off' !== get_option( 'flizcpr_savefix' ) ) {
        $fixChecked = ' checked';
      }
      if ( 'off' !== get_option( 'flizcpr_warn' ) ) {
        $warnChecked = ' checked';
        $warnDisabled = '';
      }
      $warnText = stripslashes( get_option( 'flizcpr_warntext' ));
      ?>
      <div class='wrap'>
        <?php if ( TRUE === $showNotice) {
          echo '<div class="updated"><p>';
            esc_html_e( 'Your changes have been saved.', 
                          'clarify-password-reset' );
          echo '</p></div>';
        }
        ?>
        <h1> Clarify Password Reset preferences</h1>

        <form method="post" action="<?php admin_url( 
                'options-general.php?page=clarify-password-reset' ); ?>">
          <input type='hidden' name='flizcpr__s' value='flizcpr__s'>
          <table class='form-table'>
            <tbody>
              <tr>
                <th scope='row'><?php 
                  esc_html_e( 'Fix problems', 'clarify-password-reset' );?>
                </th>
                <td>
                  <input type='checkbox' id='flizcpr_savefix' name='flizcpr_savefix' value='flizcpr_savefix'<?php echo $fixChecked;?>><?php
                   esc_html_e(
                      'Try to fix browser-stored password problems', 
                      'clarify-password-reset' 
                  );?>
                  <div class='flizcpr-explainer'><?php
                    esc_html_e( 'Restructures login form to help Firefox and Chrome save correct values to stored passwords.', 
                          'clarify-password-reset' ); ?>
                  </div>
                </td>
              </tr>
              <tr>
                <th scope='row'><?php 
                  esc_html_e( 'Warn users', 'clarify-password-reset' );?>
                </th>
                <td>
                  <input type="checkbox" id='flizcpr_warn' name='flizcpr_warn' value='flizcpr_warn'<?php echo $warnChecked;?>><?php
                    esc_html_e(
                      'Warn users to make a note of their new password',
                      'clarify-password-reset' 
                  );?>
                  <div class='flizcpr-warntext-note'><?php
                    esc_html_e(
                      'Custom text for warning message:',
                      'clarify-password-reset' 
                  );?></em></div>

                  <textarea rows="2" cols="60" id='flizcpr_warntext' name='flizcpr_warntext' <?php echo $warnDisabled;?>><?php echo esc_html( $warnText );?></textarea>
                  <div class='flizcpr-explainer'><?php
                    esc_html_e( 'Text only (no HTML tags). Default text will be used if left blank.', 
                          'clarify-password-reset' ); ?>
                  </div>
                </td>
              </tr>
            </tbody>
           </table>
          <?php submit_button(); ?>
        </form>
      </div>
    <?php
    } // END public function admin_settings_page
  } // END class Clarify_Password_Reset
} // END if ( ! class_exists( 'Clarify_Password_Reset' ) )



if ( class_exists( 'Clarify_Password_Reset' ) )
{
  // Installation and uninstallation hooks
  register_activation_hook(
    __FILE__, 
    array( 'Clarify_Password_Reset', 'activate' )
  );
  register_deactivation_hook(
    __FILE__, 
    array( 'Clarify_Password_Reset', 'deactivate' )
  );
  // instantiate the plugin class
  $wp_plugin_template = new Clarify_Password_Reset();
}
?>
