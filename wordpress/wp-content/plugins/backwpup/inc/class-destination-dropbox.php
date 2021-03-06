<?php
/**
 * Documentation: https://www.dropbox.com/developers/reference/api
 */
class BackWPup_Destination_Dropbox extends BackWPup_Destinations {

	/**
	 * @return mixed
	 */
	public function __construct() {

		$this->info[ 'ID' ]        	 = 'DROPBOX';
		$this->info[ 'name' ]        = __( 'Dropbox', 'backwpup' );
		$this->info[ 'description' ] = __( 'Backup to Dropbox', 'backwpup' );
		$this->info[ 'URI' ]         = translate( BackWPup::get_plugin_data( 'PluginURI' ), 'backwpup' );
		$this->info[ 'author' ]      = BackWPup::get_plugin_data( 'Author' );
		$this->info[ 'authorURI' ]   = translate( BackWPup::get_plugin_data( 'AuthorURI' ), 'backwpup' );
		$this->info[ 'version' ]     = BackWPup::get_plugin_data( 'Version' );

	}

	/**
	 * @return array
	 */
	public function option_defaults() {

		return array( 'dropboxtoken' => '', 'dropboxsecret' => '', 'dropboxroot' => 'sandbox', 'dropboxmaxbackups' => 0, 'dropboxsyncnodelete' => TRUE, 'dropboxdir' => trailingslashit( sanitize_file_name( get_bloginfo( 'name' ) ) ) );
	}


	/**
	 * @param $jobid
	 */
	public function edit_tab( $jobid ) {
		//Dropbox auth keys from Dropbox
		if ( isset( $_GET[ 'uid' ] ) && (int)$_GET[ 'uid' ] > 1 && ! empty( $_GET[ 'oauth_token' ] ) ) {
			if ( isset( $_SESSION[ 'backwpup_jobedit'][ 'dropboxsandbox_auth' ][ 'oAuthRequestToken' ] ) && $_SESSION[ 'backwpup_jobedit'][ 'dropboxsandbox_auth' ][ 'oAuthRequestToken' ] == $_GET[ 'oauth_token' ] ) {
				//Get Access Tokens
				try {
					$dropbox    = new BackWPup_Destination_Dropbox_API( 'sandbox' );
					$oAuthStuff = $dropbox->oAuthAccessToken( $_SESSION[ 'backwpup_jobedit'][ 'dropboxsandbox_auth' ][ 'oAuthRequestToken' ], $_SESSION[ 'backwpup_jobedit'][ 'dropboxsandbox_auth' ][ 'oAuthRequestTokenSecret' ] );
					//Save Tokens
					echo '<input type="hidden" name="dropboxtoken" value="' . esc_attr( $oAuthStuff[ 'oauth_token' ] ) . '" />';
					echo '<input type="hidden" name="dropboxsecret" value="' . esc_attr( BackWPup_Encryption::encrypt( $oAuthStuff[ 'oauth_token_secret' ] ) ) . '" />';
					echo '<input type="hidden" name="dropboxroot" value="sandbox" />';
					echo '<div id="message" class="updated">' .  __( 'Dropbox authentication complete!', 'backwpup' ) . '</div>';
				}
				catch ( Exception $e ) {
					echo '<div  id=\"message\" class=\"updated\">' . sprintf( __( 'Dropbox API: %s', 'backwpup' ), $e->getMessage() ) . '</div>';
				}
			} elseif ( isset( $_SESSION[ 'backwpup_jobedit'][ 'dropboxdropbox_auth' ][ 'oAuthRequestToken' ] ) && $_SESSION[ 'backwpup_jobedit'][ 'dropboxdropbox_auth' ][ 'oAuthRequestToken' ] == $_GET[ 'oauth_token' ] ) {
				//Get Access Tokens
				try {
					$dropbox    = new BackWPup_Destination_Dropbox_API( 'dropbox' );
					$oAuthStuff = $dropbox->oAuthAccessToken(  $_SESSION[ 'backwpup_jobedit'][ 'dropboxdropbox_auth' ][ 'oAuthRequestToken' ],  $_SESSION[ 'backwpup_jobedit'][ 'dropboxdropbox_auth' ][ 'oAuthRequestTokenSecret' ] );
					//Save Tokens
					echo '<input type="hidden" name="dropboxtoken" value="' . esc_attr( $oAuthStuff[ 'oauth_token' ] ) . '" />';
					echo '<input type="hidden" name="dropboxsecret" value="' . esc_attr( BackWPup_Encryption::encrypt( $oAuthStuff[ 'oauth_token_secret' ] ) ) . '" />';
					echo '<input type="hidden" name="dropboxroot" value="dropbox" />';
					echo '<div class="notice">' .  __( 'Dropbox authentication complete!', 'backwpup' ) . '</div>';
				}
				catch ( Exception $e ) {
					echo '<div  id=\"message\" class=\"updated\">' . sprintf( __( 'Dropbox API: %s', 'backwpup' ), $e->getMessage() ) . '</div>';
				}
			} else {
				echo '<div  id=\"message\" class=\"updated\">' . __( 'Wrong Token for Dropbox authentication received!', 'backwpup' ) . '</div>';
			}
		}

		//get auth url sandbox
		try {
			$dropbox = new BackWPup_Destination_Dropbox_API( 'sandbox' );
			// let the user authorize (user will be redirected)
			$response_sandbox = $dropbox->oAuthAuthorize( network_admin_url( 'admin.php' ) . '?page=backwpupeditjob&jobid=' .$jobid .'&tab=dest-dropbox&_wpnonce=' . wp_create_nonce( 'edit-job' ) );
			// save oauth_token_secret
			$_SESSION[ 'backwpup_jobedit'][ 'dropboxsandbox_auth' ] = array(
																 'oAuthRequestToken'       => $response_sandbox[ 'oauth_token' ],
																 'oAuthRequestTokenSecret' => $response_sandbox[ 'oauth_token_secret' ]
																);
		}
		catch ( Exception $e ) {
			echo '<div  id=\"message\" class=\"updated\">' . sprintf( __( 'Dropbox API: %s', 'backwpup' ), $e->getMessage() ) . '</div>';
		}
		//get auth url dropbox
		try {
			$dropbox = new BackWPup_Destination_Dropbox_API( 'dropbox' );
			// let the user authorize (user will be redirected)
			$response_dropbox = $dropbox->oAuthAuthorize( network_admin_url( 'admin.php' ) . '?page=backwpupeditjob&jobid=' .$jobid .'&tab=dest-dropbox&_wpnonce=' . wp_create_nonce( 'edit-job' ) );
			// save oauth_token_secret
			$_SESSION[ 'backwpup_jobedit'][ 'dropboxdropbox_auth' ] = array(
																 'oAuthRequestToken'       => $response_dropbox[ 'oauth_token' ],
																 'oAuthRequestTokenSecret' => $response_dropbox[ 'oauth_token_secret' ]
																 );
		}
		catch ( Exception $e ) {
			echo '<div  id=\"message\" class=\"updated\">' . sprintf( __( 'Dropbox API: %s', 'backwpup' ), $e->getMessage() ) . '</div>';
		}
		?>

    <h3 class="title"><?php _e( 'Login', 'backwpup' ); ?></h3>
    <p></p>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php _e( 'Authenticate', 'backwpup' ); ?></th>
            <td><?php if ( ! BackWPup_Option::get( $jobid, 'dropboxtoken' ) && ! BackWPup_Option::get( $jobid, 'dropboxsecret' ) && ! isset( $oAuthStuff[ 'oauth_token' ] ) ) { ?>
                <span style="color:red;"><?php _e( 'Not authenticated!', 'backwpup' ); ?></span>&nbsp;<a href="http://db.tt/8irM1vQ0"><?php _e( 'Create Account', 'backwpup' ); ?></a><br />
                <a class="button secondary" href="<?php echo esc_url( $response_sandbox[ 'authurl' ] );?>"><?php _e( 'Authenticate (Sandbox)', 'backwpup' ); ?></a>&nbsp;
                <a class="button secondary" href="<?php echo esc_url( $response_dropbox[ 'authurl' ] );?>"><?php _e( 'Authenticate (full Dropbox)', 'backwpup' ); ?></a>
				<?php } else { ?>
                <span style="color:green;"><?php _e( 'Authenticated!', 'backwpup' ); ?></span><br />
                <a class="button secondary" href="<?php echo esc_url( $response_sandbox[ 'authurl' ] );?>"><?php _e( 'Reauthenticate (Sandbox)', 'backwpup' ); ?></a>&nbsp;
                <a class="button secondary" href="<?php echo esc_url( $response_dropbox[ 'authurl' ] );?>"><?php _e( 'Reauthenticate (full Dropbox)', 'backwpup' ); ?></a>
				<?php } ?>
            </td>
        </tr>
    </table>


    <h3 class="title"><?php _e( 'Backup settings', 'backwpup' ); ?></h3>
    <p></p>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><label for="iddropboxdir"><?php _e( 'Folder in Dropbox', 'backwpup' ); ?></label></th>
            <td>
                <input id="iddropboxdir" name="dropboxdir" type="text" value="<?php echo esc_attr( BackWPup_Option::get( $jobid, 'dropboxdir' ) ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e( 'File Deletion', 'backwpup' ); ?></th>
            <td>
				<?php
				if ( BackWPup_Option::get( $jobid, 'backuptype' ) == 'archive' ) {
					?>
                    <label for="iddropboxmaxbackups"><input id="iddropboxmaxbackups" name="dropboxmaxbackups" type="text" size="3" value="<?php echo esc_attr( BackWPup_Option::get( $jobid, 'dropboxmaxbackups' ) );?>" class="small-text" />&nbsp;
					<?php  _e( 'Number of files to hold in folder.', 'backwpup' ); BackWPup_Help::tip( __( 'Oldest files will be deleted first. 0 = no deletion', 'backwpup' ) ); ?></label>
					<?php } else { ?>
                    <label for="iddropboxsyncnodelete" ><input class="checkbox" value="1"
                           type="checkbox" <?php checked( BackWPup_Option::get( $jobid, 'dropboxsyncnodelete' ), TRUE ); ?>
                           name="dropboxsyncnodelete" id="iddropboxsyncnodelete" /> <?php _e( 'Do not delete files on sync to destination!', 'backwpup' ); ?></label>
					<?php } ?>
            </td>
        </tr>
    </table>

	<?php
	}


	/**
	 * @param $jobid
	 * @return string|void
	 */
	public function edit_form_post_save( $jobid ) {

		unset( $_SESSION[ 'backwpup_jobedit'] );

		if ( isset( $_POST[ 'dropboxtoken' ] ) ) {
			BackWPup_Option::update( $jobid, 'dropboxtoken', $_POST[ 'dropboxtoken' ] );
			BackWPup_Option::update( $jobid, 'dropboxsecret', $_POST[ 'dropboxsecret' ] );
			BackWPup_Option::update( $jobid, 'dropboxroot', ( isset( $_POST[ 'dropboxroot' ] ) && $_POST[ 'dropboxroot' ] == 'dropbox' ) ? 'dropbox' : 'sandbox' );
		}

		BackWPup_Option::update( $jobid, 'dropboxsyncnodelete', ( isset( $_POST[ 'dropboxsyncnodelete' ] ) && $_POST[ 'dropboxsyncnodelete' ] == 1 ) ? TRUE : FALSE );
		BackWPup_Option::update( $jobid, 'dropboxmaxbackups', isset( $_POST[ 'dropboxmaxbackups' ] ) ? (int)$_POST[ 'dropboxmaxbackups' ] : 0 );

		$_POST[ 'dropboxdir' ] = trailingslashit( str_replace( '//', '/', str_replace( '\\', '/', trim( stripslashes( $_POST[ 'dropboxdir' ] ) ) ) ) );
		if ( substr( $_POST[ 'dropboxdir' ], 0, 1 ) == '/' )
			$_POST[ 'dropboxdir' ] = substr( $_POST[ 'dropboxdir' ], 1 );
		if ( $_POST[ 'dropboxdir' ] == '/' )
			$_POST[ 'dropboxdir' ] = '';
		BackWPup_Option::update( $jobid, 'dropboxdir', $_POST[ 'dropboxdir' ] );

	}

	/**
	 * @param $jobdest
	 * @param $backupfile
	 */
	public function file_delete( $jobdest, $backupfile ) {

		$files = get_site_transient( 'backwpup_' . strtolower( $jobdest ) );
		list( $jobid, $dest ) = explode( '_', $jobdest );

		if ( BackWPup_Option::get( $jobid, 'dropboxtoken' ) && BackWPup_Option::get( $jobid, 'dropboxsecret' ) ) {
			try {
				$dropbox = new BackWPup_Destination_Dropbox_API( BackWPup_Option::get( $jobid, 'dropboxroot' ) );
				$dropbox->setOAuthTokens( BackWPup_Option::get( $jobid, 'dropboxtoken' ), BackWPup_Encryption::decrypt( BackWPup_Option::get( $jobid, 'dropboxsecret' ) ) );
				$dropbox->fileopsDelete( $backupfile );
				//update file list
				foreach ( $files as $key => $file ) {
					if ( is_array( $file ) && $file[ 'file' ] == $backupfile )
						unset( $files[ $key ] );
				}
				unset( $dropbox );
			}
			catch ( Exception $e ) {
				BackWPup_Admin::message( 'DROPBOX: ' . $e->getMessage()  );
			}
		}
		set_site_transient( 'backwpup_',strtolower( $jobdest ), $files, 60 * 60 * 24 * 7 );
	}

	/**
	 * @param $jobid
	 * @param $get_file
	 */
	public function file_download( $jobid, $get_file ) {

		try {
			$dropbox = new BackWPup_Destination_Dropbox_API( BackWPup_Option::get( $jobid, 'dropboxroot' ) );
			$dropbox->setOAuthTokens( BackWPup_Option::get( $jobid, 'dropboxtoken' ), BackWPup_Encryption::decrypt( BackWPup_Option::get( $jobid, 'dropboxsecret' ) ) );
			$media = $dropbox->media( $get_file );
			if ( ! empty( $media[ 'url' ] ) )
				header( "Location: " . $media[ 'url' ] );
			die();
		}
		catch ( Exception $e ) {
			die( $e->getMessage() );
		}
	}

	/**
	 * @param $jobdest
	 * @return mixed
	 */
	public function file_get_list( $jobdest ) {
		return get_site_transient( 'BackWPup_' . $jobdest );
	}

	/**
	 * @param $job_object
	 * @return bool
	 */
	public function job_run_archive( $job_object ) {

		$job_object->substeps_todo = 2 + $job_object->backup_filesize;
		$job_object->log( sprintf( __( '%d. Try to send backup file to Dropbox &hellip;', 'backwpup' ), $job_object->steps_data[ $job_object->step_working ][ 'STEP_TRY' ] ), E_USER_NOTICE );
		try {
			$dropbox = new BackWPup_Destination_Dropbox_API( $job_object->job[ 'dropboxroot' ] );
			// set the tokens
			$dropbox->setOAuthTokens( $job_object->job[ 'dropboxtoken' ], BackWPup_Encryption::decrypt( $job_object->job[ 'dropboxsecret' ] ) );
			//get account info
			$info = $dropbox->accountInfo();
			if ( ! empty( $info[ 'uid' ] ) ) {
				$job_object->log( sprintf( __( 'Authenticated with Dropbox from %s', 'backwpup' ), $info[ 'display_name' ] . ' (' . $info[ 'email' ] . ')' ), E_USER_NOTICE );
			}
			//Check Quota
			$dropboxfreespase = $info[ 'quota_info' ][ 'quota' ] - $info[ 'quota_info' ][ 'shared' ] - $info[ 'quota_info' ][ 'normal' ];
			if ( $job_object->backup_filesize > $dropboxfreespase ) {
				$job_object->log( __( 'No free space left on Dropbox!', 'backwpup' ), E_USER_ERROR );

				return TRUE;
			}
			else {
				$job_object->log( sprintf( __( '%s free on Dropbox', 'backwpup' ), size_format( $dropboxfreespase, 2 ) ), E_USER_NOTICE );
			}
			$job_object->substeps_done = 0;
			// put the file
			$job_object->log( __( 'Upload to Dropbox now started &hellip;', 'backwpup' ), E_USER_NOTICE );
			$response = $dropbox->upload( $job_object->backup_folder . $job_object->backup_file, $job_object->job[ 'dropboxdir' ] . $job_object->backup_file );
			if ( $response[ 'bytes' ] == filesize( $job_object->backup_folder . $job_object->backup_file ) ) {
				if ( ! empty( $job_object->job[ 'jobid' ] ) )
					BackWPup_Option::update(  $job_object->job[ 'jobid' ], 'lastbackupdownloadurl', network_admin_url( 'admin.php' ) . '?page=backwpupbackups&action=downloaddropbox&file=' . $job_object->job[ 'dropboxdir' ] . $job_object->backup_file . '&jobid=' . $job_object->job[ 'jobid' ] );
				$job_object->substeps_done = 1 + $job_object->backup_filesize;
				$job_object->log( sprintf( __( 'Backup transferred to %s', 'backwpup' ), 'https://api-content.dropbox.com/1/files/' . $job_object->job[ 'dropboxroot' ] . '/' . $job_object->job[ 'dropboxdir' ] . $job_object->backup_file ), E_USER_NOTICE );
			}
			else {
				if ( $response[ 'bytes' ] != filesize( $job_object->backup_folder . $job_object->backup_file ) )
					$job_object->log( __( 'Uploaded file size and local file size not the same!', 'backwpup' ), E_USER_ERROR );
				else
					$job_object->log( sprintf( __( 'Error on transfer backup to Dropbox: %s', 'backwpup' ), $response[ 'error' ] ), E_USER_ERROR );

				return FALSE;
			}
		}
		catch ( Exception $e ) {
			$job_object->log( E_USER_ERROR, sprintf( __( 'Dropbox API: %s', 'backwpup' ), htmlentities( $e->getMessage() ) ), $e->getFile(), $e->getLine() );

			return FALSE;
		}
		try {
			$backupfilelist = array();
			$filecounter    = 0;
			$files          = array();
			$metadata       = $dropbox->metadata( $job_object->job[ 'dropboxdir' ] );
			if ( is_array( $metadata ) ) {
				foreach ( $metadata[ 'contents' ] as $data ) {
					if ( $data[ 'is_dir' ] != TRUE ) {
						$file = basename( $data[ 'path' ] );
						if ( $job_object->is_backup_archive( $file ) )
							$backupfilelist[ strtotime( $data[ 'modified' ] ) ] = $file;
						$files[ $filecounter ][ 'folder' ]      = "https://api-content.dropbox.com/1/files/" . $job_object->job[ 'dropboxroot' ]  . dirname( $data[ 'path' ] ) . "/";
						$files[ $filecounter ][ 'file' ]        = $data[ 'path' ];
						$files[ $filecounter ][ 'filename' ]    = basename( $data[ 'path' ] );
						$files[ $filecounter ][ 'downloadurl' ] = network_admin_url( 'admin.php' ) . '?page=backwpupbackups&action=downloaddropbox&file=' . $data[ 'path' ] . '&jobid=' . $job_object->job[ 'jobid' ];
						$files[ $filecounter ][ 'filesize' ]    = $data[ 'bytes' ];
						$files[ $filecounter ][ 'time' ]        = strtotime( $data[ 'modified' ] ) + ( get_option( 'gmt_offset' ) * 3600 );
						$filecounter ++;
					}
				}
			}
			if ( $job_object->job[ 'dropboxmaxbackups' ] > 0 && is_object( $dropbox ) ) { //Delete old backups
				if ( count( $backupfilelist ) > $job_object->job[ 'dropboxmaxbackups' ] ) {
					ksort( $backupfilelist );
					$numdeltefiles = 0;
					while ( $file = array_shift( $backupfilelist ) ) {
						if ( count( $backupfilelist ) < $job_object->job[ 'dropboxmaxbackups' ] )
							break;
						$response = $dropbox->fileopsDelete( $job_object->job[ 'dropboxdir' ] . $file ); //delete files on Cloud
						if ( $response[ 'is_deleted' ] == 'true' ) {
							foreach ( $files as $key => $filedata ) {
								if ( $filedata[ 'file' ] == '/' .$job_object->job[ 'dropboxdir' ] . $file )
									unset( $files[ $key ] );
							}
							$numdeltefiles ++;
						}
						else
							$job_object->log( sprintf( __( 'Error on delete file on Dropbox: %s', 'backwpup' ), $file ), E_USER_ERROR );
					}
					if ( $numdeltefiles > 0 )
						$job_object->log( sprintf( _n( 'One file deleted on Dropbox', '%d files deleted on Dropbox', $numdeltefiles, 'backwpup' ), $numdeltefiles ), E_USER_NOTICE );
				}
			}
			set_site_transient( 'backwpup_' . $job_object->job[ 'jobid' ] . '_dropbox', $files, 60 * 60 * 24 * 7 );
		}
		catch ( Exception $e ) {
			$job_object->log( E_USER_ERROR, sprintf( __( 'Dropbox API: %s', 'backwpup' ), htmlentities( $e->getMessage() ) ), $e->getFile(), $e->getLine() );

			return FALSE;
		}
		$job_object->substeps_done ++;

		return TRUE;
	}

	/**
	 * @param $job_object
	 * @return bool
	 */
	public function can_run( $job_object ) {

		if ( empty( $job_object->job[ 'dropboxtoken' ] ) )
			return FALSE;

		if ( empty( $job_object->job[ 'dropboxsecret' ] ) )
			return FALSE;

		return TRUE;
	}

}


/**
 *
 */
final class BackWPup_Destination_Dropbox_API {

	/**
	 *
	 */
	const API_URL         = 'https://api.dropbox.com/';

	/**
	 *
	 */
	const API_CONTENT_URL = 'https://api-content.dropbox.com/';

	/**
	 *
	 */
	const API_WWW_URL     = 'https://www.dropbox.com/';

	/**
	 *
	 */
	const API_VERSION_URL = '1/';

	/**
	 * dropbox vars
	 *
	 * @var string
	 */
	private $root = 'sandbox';

	/**
	 * oAuth vars
	 *
	 * @var string
	 */
	private $oauth_app_key = '';

	/**
	 * @var string
	 */
	private $oauth_app_secret = '';
	/**
	 * @var string
	 */
	private $oauth_token = '';

	/**
	 * @var string
	 */
	private $oauth_token_secret = '';


	/**
	 * @param string $boxtype
	 */
	public function __construct( $boxtype = 'dropbox' ) {

		if ( $boxtype == 'dropbox' ) {
			$this->oauth_app_key 	= BackWPup_Option::get( 'cfg', 'dropboxappkey' );
			$this->oauth_app_secret = BackWPup_Encryption::decrypt( BackWPup_Option::get( 'cfg', 'dropboxappsecret' ) );
			$this->root             = 'dropbox';
		}
		else {
			$this->oauth_app_key 	= BackWPup_Option::get( 'cfg', 'dropboxsandboxappkey' );
			$this->oauth_app_secret = BackWPup_Encryption::decrypt( BackWPup_Option::get( 'cfg', 'dropboxsandboxappsecret' ) );
			$this->root             = 'sandbox';
		}

		if ( empty( $this->oauth_app_key ) or empty( $this->oauth_app_secret ) )
			throw new BackWPup_Destination_Dropbox_API_Exception( "No App key or App Secret specified." );
	}

	/**
	 * @param $token
	 * @param $secret
	 * @throws BackWPup_Destination_Dropbox_API_Exception
	 */
	public function setOAuthTokens( $token, $secret ) {

		$this->oauth_token        = $token;
		$this->oauth_token_secret = $secret;

		if ( empty( $this->oauth_token ) or empty( $this->oauth_token_secret ) )
			throw new BackWPup_Destination_Dropbox_API_Exception( "No oAuth token or secret specified." );
	}

	/**
	 * @return array|mixed|string
	 */
	public function accountInfo() {

		$url = self::API_URL . self::API_VERSION_URL . 'account/info';

		return $this->request( $url );
	}

	/**
	 * @param        $file
	 * @param string $path
	 * @param bool   $overwrite
	 * @return array|mixed|string
	 * @throws BackWPup_Destination_Dropbox_API_Exception
	 */
	public function upload( $file, $path = '', $overwrite = TRUE ) {

		$file = str_replace( "\\", "/", $file );

		if ( ! is_readable( $file ) or ! is_file( $file ) )
			throw new BackWPup_Destination_Dropbox_API_Exception( "Error: File \"$file\" is not readable or doesn't exist." );

		$filesize = filesize( $file );

		if ( $filesize < 8388608 ) { //chunk transfer on bigger uploads
			$filehandel = fopen( $file, 'r' );
			$url        = self::API_CONTENT_URL . self::API_VERSION_URL . 'files_put/' . $this->root . '/' . $this->encode_path( $path );
			$output     = $this->request( $url, array( 'overwrite' => ( $overwrite ) ? 'true' : 'false' ), 'PUT', $filehandel, $filesize );
			fclose( $filehandel );
		}
		else {
			$output = $this->chunked_upload( $file, $path, $overwrite );
		}

		return $output;
	}

	/**
	 * @param        $file
	 * @param string $path
	 * @param bool   $overwrite
	 * @return array|mixed|string
	 * @throws BackWPup_Destination_Dropbox_API_Exception
	 */
	public function chunked_upload( $file, $path = '', $overwrite = TRUE ) {

		$file = str_replace( "\\", "/", $file );

		if ( ! is_readable( $file ) or ! is_file( $file ) )
			throw new BackWPup_Destination_Dropbox_API_Exception( "Error: File \"$file\" is not readable or doesn't exist." );

		$file_handel      = fopen( $file, 'r' );
		$uploadid         = NULL;
		$offset           = 0;
		$job_object 	  = BackWPup_Job::getInstance();

		while ( $data = fread( $file_handel, 4194304 ) ) { //4194304 = 4MB
			$chunkHandle = fopen( 'php://temp', 'rw' );
			fwrite( $chunkHandle, $data );
			rewind( $chunkHandle );
			$url    = self::API_CONTENT_URL . self::API_VERSION_URL . 'chunked_upload';
			$output = $this->request( $url, array( 'upload_id' => $uploadid, 'offset' => $offset ), 'PUT', $chunkHandle, strlen( $data ) );
			fclose( $chunkHandle );
			$job_object->curl_read_callback( NULL, NULL, strlen( $data ) );
			//args for next chunk
			$offset   = $output[ 'offset' ];
			$uploadid = $output[ 'upload_id' ];
			fseek( $file_handel, $offset );
		}

		fclose( $file_handel );
		$url = self::API_CONTENT_URL . self::API_VERSION_URL . 'commit_chunked_upload/' . $this->root . '/' . $this->encode_path( $path );

		return $this->request( $url, array( 'overwrite' => ( $overwrite ) ? 'true' : 'false', 'upload_id' => $uploadid ), 'POST' );
	}

	/**
	 * @param      $path
	 * @param bool $echo
	 * @return array|mixed|string
	 */
	public function download( $path, $echo = FALSE ) {

		$url = self::API_CONTENT_URL . self::API_VERSION_URL . 'files/' . $this->root . '/' . $this->encode_path( $path );
		if ( ! $echo )
			return $this->request( $url );
		else
			$this->request( $url, NULL, 'GET', NULL, 0, TRUE );
	}

	/**
	 * @param string $path
	 * @param bool   $listContents
	 * @param int    $fileLimit
	 * @param string $hash
	 * @return array|mixed|string
	 */
	public function metadata( $path = '', $listContents = TRUE, $fileLimit = 10000, $hash = '' ) {

		$url = self::API_URL . self::API_VERSION_URL . 'metadata/' . $this->root . '/' . $this->encode_path( $path );

		return $this->request( $url, array(
										  'list'       => ( $listContents ) ? 'true' : 'false',
										  'hash'       => ( $hash ) ? $hash : '',
										  'file_limit' => $fileLimit
									 ) );
	}

	/**
	 * @param string $path
	 * @param        $query
	 * @param int    $fileLimit
	 * @return array|mixed|string
	 * @throws BackWPup_Destination_Dropbox_API_Exception
	 */
	public function search( $path = '', $query, $fileLimit = 1000 ) {

		if ( strlen( $query ) >= 3 )
			throw new BackWPup_Destination_Dropbox_API_Exception( "Error: Query \"$query\" must three characters long." );
		$url = self::API_URL . self::API_VERSION_URL . 'search/' . $this->root . '/' . $this->encode_path( $path );

		return $this->request( $url, array(
										  'query'      => $query,
										  'file_limit' => $fileLimit
									 ) );
	}

	/**
	 * @param string $path
	 * @return array|mixed|string
	 */
	public function shares( $path = '' ) {

		$url = self::API_URL . self::API_VERSION_URL . 'shares/' . $this->root . '/' . $this->encode_path( $path );

		return $this->request( $url );
	}

	/**
	 * @param string $path
	 * @return array|mixed|string
	 */
	public function media( $path = '' ) {

		$url = self::API_URL . self::API_VERSION_URL . 'media/' . $this->root . '/' . $this->encode_path( $path );

		return $this->request( $url );
	}

	/**
	 * @param $path
	 * @return array|mixed|string
	 */
	public function fileopsDelete( $path ) {

		$url = self::API_URL . self::API_VERSION_URL . 'fileops/delete';

		return $this->request( $url, array(
										  'path' => '/' . $this->encode_path( $path ),
										  'root' => $this->root
									 ) );
	}

	/**
	 * @param $path
	 * @return array|mixed|string
	 */
	public function fileopsCreate_folder( $path ) {

		$url = self::API_URL . self::API_VERSION_URL . 'fileops/create_folder';

		return $this->request( $url, array(
										  'path' => '/' . $this->encode_path( $path ),
										  'root' => $this->root
									 ) );
	}

	/**
	 * @param $callback_url
	 * @return array
	 * @throws BackWPup_Destination_Dropbox_API_Exception
	 */
	public function oAuthAuthorize( $callback_url ) {

		$headers[ ] = 'Authorization: OAuth oauth_version="1.0", oauth_signature_method="PLAINTEXT", oauth_consumer_key="' . $this->oauth_app_key . '", oauth_signature="' . $this->oauth_app_secret . '&"';
		$ch         = curl_init();
		curl_setopt( $ch, CURLOPT_URL, self::API_URL . self::API_VERSION_URL . 'oauth/request_token' );
		curl_setopt( $ch, CURLOPT_USERAGENT, BackWPup::get_plugin_data( 'User-Agent' ) );
		curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );
		curl_setopt( $ch, CURLOPT_SSLVERSION, 3 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, TRUE );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
		if ( is_file( BackWPup::get_plugin_data( 'plugindir' ) . '/inc/cacert.pem' ) )
			curl_setopt( $ch, CURLOPT_CAINFO, BackWPup::get_plugin_data( 'plugindir' ) . '/inc/cacert.pem' );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, TRUE );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		$content = curl_exec( $ch );
		$status  = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		if ( $status >= 200 && $status < 300 && 0 == curl_errno( $ch ) ) {
			parse_str( $content, $oauth_token );
		}
		else {
			$output = json_decode( $content, TRUE );
			if ( isset( $output[ 'error' ] ) && is_string( $output[ 'error' ] ) ) $message = $output[ 'error' ];
			elseif ( isset( $output[ 'error' ][ 'hash' ] ) && $output[ 'error' ][ 'hash' ] != '' ) $message = (string)$output[ 'error' ][ 'hash' ];
			elseif ( 0 != curl_errno( $ch ) ) $message = '(' . curl_errno( $ch ) . ') ' . curl_error( $ch );
			else $message = '(' . $status . ') Invalid response.';
			throw new BackWPup_Destination_Dropbox_API_Exception( $message );
		}
		curl_close( $ch );

		return array(
			'authurl'            => self::API_WWW_URL . self::API_VERSION_URL . 'oauth/authorize?oauth_token=' . $oauth_token[ 'oauth_token' ] . '&oauth_callback=' . urlencode( $callback_url ),
			'oauth_token'        => $oauth_token[ 'oauth_token' ],
			'oauth_token_secret' => $oauth_token[ 'oauth_token_secret' ]
		);
	}

	/**
	 * @param $oauth_token
	 * @param $oauth_token_secret
	 *
	 * @return array|null
	 * @throws BackWPup_Destination_Dropbox_API_Exception
	 */
	public function oAuthAccessToken( $oauth_token, $oauth_token_secret ) {

		$headers[ ] = 'Authorization: OAuth oauth_version="1.0", oauth_signature_method="PLAINTEXT", oauth_consumer_key="' . $this->oauth_app_key . '", oauth_token="' . $oauth_token . '", oauth_signature="' . $this->oauth_app_secret . '&' . $oauth_token_secret . '"';
		$ch         = curl_init();
		curl_setopt( $ch, CURLOPT_URL, self::API_URL . self::API_VERSION_URL . 'oauth/access_token' );
		curl_setopt( $ch, CURLOPT_USERAGENT, BackWPup::get_plugin_data( 'User-Agent' ) );
		curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );
		curl_setopt( $ch, CURLOPT_SSLVERSION, 3 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, TRUE );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
		if ( is_file( BackWPup::get_plugin_data( 'plugindir' ) . '/inc/cacert.pem' ) )
			curl_setopt( $ch, CURLOPT_CAINFO, BackWPup::get_plugin_data( 'plugindir' ) . '/inc/cacert.pem' );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, TRUE );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		$content = curl_exec( $ch );
		$status  = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		if ( $status >= 200 && $status < 300 && 0 == curl_errno( $ch ) ) {
			parse_str( $content, $oauth_token );
			$this->setOAuthTokens( $oauth_token[ 'oauth_token' ], $oauth_token[ 'oauth_token_secret' ] );

			return $oauth_token;
		}
		else {
			$output = json_decode( $content, TRUE );
			if ( isset( $output[ 'error' ] ) && is_string( $output[ 'error' ] ) ) $message = $output[ 'error' ];
			elseif ( isset( $output[ 'error' ][ 'hash' ] ) && $output[ 'error' ][ 'hash' ] != '' ) $message = (string)$output[ 'error' ][ 'hash' ];
			elseif ( 0 != curl_errno( $ch ) ) $message = '(' . curl_errno( $ch ) . ') ' . curl_error( $ch );
			else $message = '(' . $status . ') Invalid response.';
			throw new BackWPup_Destination_Dropbox_API_Exception( $message );
		}
	}

	/**
	 * @param        $url
	 * @param array  $args
	 * @param string $method
	 * @param null   $filehandel
	 * @param int    $filesize
	 * @param bool   $echo
	 *
	 * @throws BackWPup_Destination_Dropbox_API_Exception
	 * @internal param null $file
	 * @return array|mixed|string
	 */
	private function request( $url, $args = array(), $method = 'GET', $filehandel = NULL, $filesize = 0, $echo = FALSE ) {

		/* Header*/
		$headers[ ] = 'Authorization: OAuth oauth_version="1.0", oauth_signature_method="PLAINTEXT", oauth_consumer_key="' . $this->oauth_app_key . '", oauth_token="' . $this->oauth_token . '", oauth_signature="' . $this->oauth_app_secret . '&' . $this->oauth_token_secret . '"';
		$headers[ ] = 'Expect:';

		/* Build cURL Request */
		$ch = curl_init();
		if ( $method == 'POST' ) {
			curl_setopt( $ch, CURLOPT_POST, TRUE );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $args );
			curl_setopt( $ch, CURLOPT_URL, $url );
		}
		elseif ( $method == 'PUT' ) {
			curl_setopt( $ch, CURLOPT_PUT, TRUE );
			curl_setopt( $ch, CURLOPT_INFILE, $filehandel );
			curl_setopt( $ch, CURLOPT_INFILESIZE, $filesize );
			$args = ( is_array( $args ) ) ? '?' . http_build_query( $args, '', '&' ) : $args;
			curl_setopt( $ch, CURLOPT_URL, $url . $args );
		}
		else {
			$args = ( is_array( $args ) ) ? '?' . http_build_query( $args, '', '&' ) : $args;
			curl_setopt( $ch, CURLOPT_URL, $url . $args );
		}
		curl_setopt( $ch, CURLOPT_USERAGENT, BackWPup::get_plugin_data( 'User-Agent' ) );
		curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $ch, CURLOPT_SSLVERSION, 3 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, TRUE );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
		if ( is_file( BackWPup::get_plugin_data( 'plugindir' ) . '/inc/cacert.pem' ) )
			curl_setopt( $ch, CURLOPT_CAINFO, BackWPup::get_plugin_data( 'plugindir' ) . '/inc/cacert.pem' );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		$content = '';
		$output = '';
		$header = '';
		if ( $echo ) {
			echo curl_exec( $ch );
		}
		else {
			curl_setopt( $ch, CURLOPT_HEADER, TRUE );
			if ( 0 == curl_errno( $ch ) ) {
				list( $header, $content ) = explode( "\r\n\r\n", curl_exec( $ch ), 2 );
				$output = json_decode( $content, TRUE );
			}
		}
		$status = curl_getinfo( $ch );
		if ( isset( $datafilefd ) && is_resource( $datafilefd ) )
			fclose( $datafilefd );

		if ( $status[ 'http_code' ] == 503 ) {
			$wait = 1;
			trigger_error($header,E_USER_WARNING);
			if ( preg_match( "/retry-after:(.*?)\r/i", $header, $matches ) )
				$wait = trim( $matches[ 1 ] );
			trigger_error( sprintf( '(503) Your app is making too many requests and is being rate limited. 503s can trigger on a per-app or per-user basis. Wait for %d seconds.', $wait ), E_USER_WARNING );
			sleep( $wait );

			return $this->request( $url, $args, $method, $filehandel, $filesize, $echo );
		} elseif ( $status[ 'http_code' ] == 404 ) {
			trigger_error( '(' . $status[ 'http_code' ] . ') ' . $output[ 'error' ], E_USER_WARNING );

			return FALSE;
		}
		elseif ( isset( $output[ 'error' ] ) || $status[ 'http_code' ] >= 300 || $status[ 'http_code' ] < 200 || curl_errno( $ch ) > 0 ) {
			if ( isset( $output[ 'error' ] ) && is_string( $output[ 'error' ] ) ) $message = '(' . $status[ 'http_code' ] . ') ' . $output[ 'error' ];
			elseif ( isset( $output[ 'error' ][ 'hash' ] ) && $output[ 'error' ][ 'hash' ] != '' ) $message = (string)'(' . $status[ 'http_code' ] . ') ' . $output[ 'error' ][ 'hash' ];
			elseif ( 0 != curl_errno( $ch ) ) $message = '(' . curl_errno( $ch ) . ') ' . curl_error( $ch );
			elseif ( $status[ 'http_code' ] == 304 ) $message = '(304) The folder contents have not changed (relies on hash parameter).';
			elseif ( $status[ 'http_code' ] == 400 ) $message = '(400) Bad input parameter: ' . strip_tags( $content );
			elseif ( $status[ 'http_code' ] == 401 ) $message = '(401) Bad or expired token. This can happen if the user or Dropbox revoked or expired an access token. To fix that you should re-authenticate the user.';
			elseif ( $status[ 'http_code' ] == 403 ) $message = '(403) Bad OAuth request (wrong consumer key, bad nonce, expired timestamp, ...). Unfortunately, reauthenticating the user won\'t help here.';
			elseif ( $status[ 'http_code' ] == 404 ) $message = '(404) The file was not found at the specified path, or was not found at the specified rev.';
			elseif ( $status[ 'http_code' ] == 405 ) $message = '(405) Request method not expected (generally should be GET,PUT or POST).';
			elseif ( $status[ 'http_code' ] == 406 ) $message = '(406) There are too many file entries to return.';
			elseif ( $status[ 'http_code' ] == 411 ) $message = '(411) Chunked encoding was attempted for this upload, but is not supported by Dropbox.';
			elseif ( $status[ 'http_code' ] == 415 ) $message = '(415) The image is invalid and cannot be thumbnailed.';
			elseif ( $status[ 'http_code' ] == 503 ) $message = '(503) Your app is making too many requests and is being rate limited. 503s can trigger on a per-app or per-user basis.';
			elseif ( $status[ 'http_code' ] == 507 ) $message = '(507) User is over Dropbox storage quota.';
			else $message = '(' . $status[ 'http_code' ] . ') Invalid response.';
			throw new BackWPup_Destination_Dropbox_API_Exception( $message );
		}
		else {
			curl_close( $ch );
			if ( ! is_array( $output ) )
				return $content;
			else
				return $output;
		}
	}

	/**
	 * @param $path
	 *
	 * @return mixed
	 */
	private function encode_path( $path ) {

		$path = preg_replace( '#/+#', '/', trim( $path, '/' ) );
		$path = str_replace( '%2F', '/', rawurlencode( $path ) );

		return $path;
	}
}

/**
 *
 */
class BackWPup_Destination_Dropbox_API_Exception extends Exception {

}