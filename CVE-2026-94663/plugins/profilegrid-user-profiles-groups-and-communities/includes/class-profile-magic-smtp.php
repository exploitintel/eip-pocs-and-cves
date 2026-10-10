<?php

/**
 * The SMTP functionality of the plugin.
 *
 * @link       https://profilegrid.co
 * @since      1.0.0
 *
 * @package    Profile_Magic
 * @subpackage Profile_Magic/public
 */
class Profile_Magic_SMTP {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $profile_magic    The ID of this plugin.
	 */
	private $profile_magic;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $profile_magic       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $profile_magic, $version ) {

		$this->profile_magic = $profile_magic;
		$this->version       = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function pm_smtp_connection( $phpmailer ) {
         $dbhandler          = new PM_DBhandler();
		$phpmailer->Mailer   = 'smtp';
		$phpmailer->From     = $dbhandler->get_global_option_value( 'pm_smtp_from_email_address', get_option( 'admin_email' ) );
		$phpmailer->FromName = $dbhandler->get_global_option_value( 'pm_smtp_from_email_name', get_bloginfo( 'name' ) );
		$phpmailer->Sender   = $phpmailer->From; //Return-Path
		$phpmailer->AddReplyTo( $phpmailer->From, $phpmailer->FromName ); //Reply-To
		$phpmailer->Host       = $dbhandler->get_global_option_value( 'pm_smtp_host' );
		$phpmailer->SMTPSecure = $dbhandler->get_global_option_value( 'pm_smtp_encription' );
		$phpmailer->Port       = $dbhandler->get_global_option_value( 'pm_smtp_port' );
		$phpmailer->SMTPAuth   = ( $dbhandler->get_global_option_value( 'pm_smtp_authentication' )=='true' ) ? true : false;
		if ( $phpmailer->SMTPAuth ) {
			$phpmailer->Username = $dbhandler->get_global_option_value( 'pm_smtp_username' );
			$phpmailer->Password = $dbhandler->get_global_option_value( 'pm_smtp_password' );
		}
	}

}
