<?php
/**
 *
 */
class BackWPup_Destination_Folder extends BackWPup_Destinations {

	/**
	 * @return mixed
	 */
	public function __construct() {

		$this->info[ 'ID' ]        	 = 'FOLDER';
		$this->info[ 'name' ]        = __( 'Folder', 'backwpup' );
		$this->info[ 'description' ] = __( 'Backup to Folder', 'backwpup' );
		$this->info[ 'URI' ]         = translate( BackWPup::get_plugin_data( 'PluginURI' ), 'backwpup' );
		$this->info[ 'author' ]      = BackWPup::get_plugin_data( 'Author' );
		$this->info[ 'authorURI' ]   = translate( BackWPup::get_plugin_data( 'AuthorURI' ), 'backwpup' );
		$this->info[ 'version' ]     = BackWPup::get_plugin_data( 'Version' );

	}


	/**
	 * @return array
	 */
	public function option_defaults() {

		$upload_dir = wp_upload_dir();

		return array( 'maxbackups' => 0, 'backupdir' => trailingslashit( str_replace( '\\', '/',$upload_dir[ 'basedir' ] ) ) . trailingslashit( sanitize_title_with_dashes( get_bloginfo( 'name' ) ) ), 'backupsyncnodelete' => TRUE );
	}


	/**
	 * @param $jobid
	 * @return void
	 * @internal param $main
	 */
	public function edit_tab( $jobid ) {
		?>
    <h3 class="title"><?php _e( 'Backup settings', 'backwpup' ); ?></h3>
    <p></p>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><label for="idbackupdir"><?php _e( 'Folder to store the backups in', 'backwpup' ); ?></label></th>
            <td>
                <input name="backupdir" id="idbackupdir" type="text" value="<?php echo esc_attr( BackWPup_Option::get( $jobid, 'backupdir' ) ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e( 'File Deletion', 'backwpup' ); ?></th>
            <td>
				<?php
				if ( BackWPup_Option::get( $jobid, 'backuptype' ) == 'archive' ) {
					?>
                    <label for="idmaxbackups"><input name="maxbackups" id="idmaxbackups" type="text" size="3" value="<?php echo esc_attr( BackWPup_Option::get( $jobid, 'maxbackups' ) ) ;?>" class="small-text" />&nbsp;
					<?php  _e( 'Number of files to hold in folder.', 'backwpup' ); BackWPup_Help::tip( __( 'Oldest files will be deleted first. 0 = no deletion', 'backwpup' ) ); ?></label>
					<?php } else { ?>
                    <label for="idbackupsyncnodelete"><input class="checkbox" value="1"
                           type="checkbox" <?php checked( BackWPup_Option::get( $jobid, 'backupsyncnodelete' ), TRUE ); ?>
                           name="backupsyncnodelete" id="idbackupsyncnodelete" /> <?php _e( 'Do not delete files on sync to destination!', 'backwpup' ); ?></label>
					<?php } ?>
            </td>
        </tr>
    </table>
	<?php
	}


	/**
	 * @param $jobid
	 */
	public function edit_form_post_save( $jobid ) {

		$_POST[ 'backupdir' ] = trailingslashit( str_replace( '//', '/', str_replace( '\\', '/', trim( stripslashes( $_POST[ 'backupdir' ] ) ) ) ) );
		if ( $_POST[ 'backupdir' ][ 0 ] == '.' || ( $_POST[ 'backupdir' ][ 0 ] != '/' && ! preg_match( '#^[a-zA-Z]:/#', $_POST[ 'backupdir' ] ) ) )
			$_POST[ 'backupdir' ] = trailingslashit( str_replace( '\\', '/', ABSPATH ) ) . $_POST[ 'backupdir' ];
		if ( $_POST[ 'backupdir' ] == '/' )
			$_POST[ 'backupdir' ] = '';
		BackWPup_Option::update( $jobid, 'backupdir', $_POST[ 'backupdir' ] );

		BackWPup_Option::update( $jobid, 'maxbackups', isset( $_POST[ 'maxbackups' ] ) ? (int)$_POST[ 'maxbackups' ] : 0 );
		BackWPup_Option::update( $jobid, 'backupsyncnodelete', ( isset( $_POST[ 'backupsyncnodelete' ] ) && $_POST[ 'backupsyncnodelete' ] == 1 ) ? TRUE : FALSE );
	}

	/**
	 * @param $jobdest
	 * @param $backupfile
	 */
	public function file_delete( $jobdest, $backupfile ) {

		$files = get_site_transient( 'backwpup_'. strtolower( $jobdest ), FALSE );
		if ( is_file( $backupfile ) ) {
			if ( unlink( $backupfile ) ) {
				//update file list
				foreach ( $files as $key => $file ) {
					if ( is_array( $file ) && $file[ 'file' ] == $backupfile )
						unset( $files[ $key ] );
				}
			}
		}

		set_site_transient( 'backwpup_'. strtolower( $jobdest ), $files, 60 * 60 * 24 * 7 );
	}

	/**
	 * @param $jobid
	 * @param $get_file
	 */
	public function file_download( $jobid, $get_file ) {

		if ( is_file( $get_file ) ) {
			header( "Pragma: public" );
			header( "Expires: 0" );
			header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
			header( "Content-Type: application/octet-stream" );
			header( "Content-Disposition: attachment; filename=" . basename( $get_file ) . ";" );
			header( "Content-Transfer-Encoding: binary" );
			header( "Content-Length: " . filesize( $get_file ) );
			@set_time_limit( 0 );
			//chunked readfile
			ob_end_clean();
			$handle = fopen( $get_file, 'r' );
			if ( $handle ) {
				while ( ! feof( $handle ) ) {
					$buffer = fread( $handle, 20482048 ); //2MB chunkes
					echo $buffer;
					ob_flush();
					flush();
				}
				fclose( $handle );
			}
			die();
		}
		else {
			header( $_SERVER[ "SERVER_PROTOCOL" ] . " 404 Not Found" );
			header( "Status: 404 Not Found" );
			die();
		}
	}

	/**
	 * @param $jobdest
	 * @return mixed
	 */
	public function file_get_list( $jobdest ) {

		return get_site_transient( 'backwpup_' . strtolower( $jobdest ) );
	}

	/**
	 * @param $job_object
	 * @return bool
	 */
	public function job_run_archive( $job_object ) {

		$job_object->substeps_todo = 1;
		if ( ! empty( $job_object->job[ 'jobid' ] ) )
			BackWPup_Option::update( $job_object->job[ 'jobid' ], 'lastbackupdownloadurl', add_query_arg( array(
																								  'page'   => 'backwpupbackups',
																								  'action' => 'downloadfolder',
																								  'file'   => $job_object->backup_folder . $job_object->backup_file
																							 ), network_admin_url( 'admin.php' ) ) );
		//Delete old Backupfiles
		$backupfilelist = array();
		$filecounter    = 0;
		$files          = array();
		if ( $dir = @opendir( $job_object->backup_folder ) ) { //make file list
			while ( ( $file = readdir( $dir ) ) !== FALSE ) {
				if ( is_file( $job_object->backup_folder . $file ) ) {
					//list for deletion
					if ( $job_object->is_backup_archive( $file ) )
						$backupfilelist[ filemtime( $job_object->backup_folder . $file ) ] = $file;
					//file list for backups
					$files[ $filecounter ][ 'folder' ]      = $job_object->backup_folder;
					$files[ $filecounter ][ 'file' ]        = $job_object->backup_folder . $file;
					$files[ $filecounter ][ 'filename' ]    = $file;
					$files[ $filecounter ][ 'downloadurl' ] = add_query_arg( array(
																				  'page'   => 'backwpupbackups',
																				  'action' => 'downloadfolder',
																				  'file'   => $job_object->backup_folder . $file
																			 ), network_admin_url( 'admin.php' ) );
					$files[ $filecounter ][ 'filesize' ]    = filesize( $job_object->backup_folder . $file );
					$files[ $filecounter ][ 'time' ]        = filemtime( $job_object->backup_folder . $file );
					$filecounter ++;
				}
			}
			@closedir( $dir );
		}
		if ( $job_object->job[ 'maxbackups' ] > 0 ) {
			if ( count( $backupfilelist ) > $job_object->job[ 'maxbackups' ] ) {
				ksort( $backupfilelist );
				$numdeltefiles = 0;
				while ( $file = array_shift( $backupfilelist ) ) {
					if ( count( $backupfilelist ) < $job_object->job[ 'maxbackups' ] )
						break;
					unlink( $job_object->backup_folder . $file );
					foreach ( $files as $key => $filedata ) {
						if ( $filedata[ 'file' ] == $job_object->backup_folder . $file )
							unset( $files[ $key ] );
					}
					$numdeltefiles ++;
				}
				if ( $numdeltefiles > 0 )
					$job_object->log( sprintf( _n( 'One backup file deleted', '%d backup files deleted', $numdeltefiles, 'backwpup' ), $numdeltefiles ), E_USER_NOTICE );
			}
		}
		set_site_transient( 'backwpup_' . $job_object->job[ 'jobid' ] . '_folder', $files, 60 * 60 * 24 * 7 );

		$job_object->substeps_done ++;

		return TRUE;
	}

	/**
	 * @param $job_object
	 * @return bool
	 */
	public function can_run( $job_object ) {

		if ( empty( $job_object->job[ 'backupdir' ] ) || $job_object->job[ 'backupdir' ] == '/' )
			return FALSE;

		return TRUE;
	}

}
