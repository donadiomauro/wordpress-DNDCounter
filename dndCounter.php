<?php

/**
*	Plugin Name: DND Counter
*
*	Description: A page views counter IP based
*	Version: 1.0
*	Author: Mauro Donadio (donadiomauro@gmail.com)
*	Author URI: http://maurodonadio.wordpress.com/
*	License: GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007
*/

	class DNDCounter{
	
		const TABLE_NAME	= 'post_views';
		const TABLE_VIEWER	= 'post_viewers';
		// time interval in minutes (to delete an user entry)
		const TIME_INTARVAL = '1440';

		public function __construct(){

			register_activation_hook( __FILE__, array( __CLASS__, 'install' ) );

			// Check for requests to increment view
			add_action( 'wp_head', array( __CLASS__, 'new_view' ) );
			
			// Check for a CSV export request
			add_action( 'admin_init', array( __CLASS__, 'export_csv_request' ) );

			// Add the report page
			add_action( 'admin_menu', array( __CLASS__, 'add_menu_pages' ) );
			
			//add_action( 'the_content', array( __CLASS__, 'replace_content' ) );
		}
		/*
		public function replace_content($content){
			//$content	= str_replace("{DND:dominos_popular_posts}", 'asd', $content);
			return $content;
		}
		*/

		public static function add_menu_pages() {
			add_dashboard_page( __( 'DND Views Report', 'author-page-views' ), __( 'DND Views Report', 'author-page-views' ), 'edit_users', __FILE__, array( __CLASS__, 'report_page' ) );
		}

		/**
		*	Create table on installation process
		*/
		public static function install() {

			global $wpdb;
			$table_name = $wpdb->prefix . self::TABLE_NAME;
			$table_viewers = $wpdb->prefix . self::TABLE_VIEWER;

			$sql = "CREATE TABLE `$table_name` (
			  `post_id` bigint(20) NOT NULL,
			  `post_author` bigint(20) NOT NULL,
			  `view_date` date NOT NULL,
			  `view_count` int(11) NOT NULL,
			  PRIMARY KEY (`post_id`)
			);
			
			CREATE TABLE `$table_viewers` (
			  `user_ip` varchar(15) NOT NULL,
			  `post_id` bigint(20) NOT NULL,
			  `date` datetime NOT NULL,
			  PRIMARY KEY (`user_ip`)
			);";

			// rather than executing an SQL query directly,
			// we'll use the dbDelta function in wp-admin/includes/upgrade.php
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			dbDelta( $sql );
		}
		
		public function new_view(){

			// if not post
			if(!is_single())
				return;

			global $wpdb;
			$table_name 	= $wpdb->prefix . self::TABLE_NAME;
			$table_viewers	= $wpdb->prefix . self::TABLE_VIEWER;
			$time_interval	= self::TIME_INTARVAL;

			global $wp_query;

			// clean all the expired views
			//$a = $wpdb->query("SELECT * FROM `$table_viewers` WHERE `date` < (NOW() - INTERVAL $time_interval MINUTE)");
			$wpdb->query("DELETE FROM $table_viewers WHERE `date` < (NOW() - INTERVAL $time_interval MINUTE)");
			//var_dump($a);

			// GET

			// get current date / time in string
			$date = date( 'Y-m-d H:i:0', current_time('timestamp') );
			// get user IP
			$user_ip	= $_SERVER['REMOTE_ADDR'];
			
			// get page / post id
			$pID		= $wp_query->post->ID;
			$author		= $wp_query->post->post_author;

			// SET

			// set key to store
			//$key		= $user_ip . 'x' . $pID;
			// set key value to store
			//$value		= array($user_ip, $pID);

			// check if the current key exists
			//$visited	= get_transient($key);
			$data		= $wpdb->query("SELECT * FROM $table_viewers WHERE `user_ip` = '$user_ip' AND `post_id` = '$pID'");

			// Check if not already viewed from same IP
			//if ( false === ( $visited ) ) {
			if ( false === ( $data ) OR $data == 0 ) {

				// set a new record for current IP
				//set_transient( $key, $value, 60 );
				$wpdb->query("INSERT INTO $table_viewers VALUES ('$user_ip', '$pID', '$date')");

				// If a row doesn't exist, insert a new one
				$data		= $wpdb->get_results("SELECT * FROM wp_post_views WHERE `post_id` = '$pID'");
				
				$curr_count	= $data[0]->view_count;
				$curr_pID	= $data[0]->post_id;

				if(count($data))
					$wpdb->query("UPDATE $table_name SET `view_count` = ($curr_count + 1) WHERE `post_id` = '$curr_pID'");
				else
					$wpdb->query("INSERT INTO $table_name VALUES ('$pID', '$author', '$date', '1')");
/*
				$wpdb->insert( $table_name, array(
					'post_id' => $pID,
					'post_author' => $author,
					'view_date' => $date,
					'view_count' => 1
				), array( '%d', '%d', '%s', '%d' ) );
*/
			}

			return;
		}

		
		
		/**
		*	Dates management
		*/
		public static function report_page() {
			$args = array();
			$args['date_begin'] = date( 'Y-m-d 0:0:0', self::get_date_begin() );
			$args['date_end'] = date( 'Y-m-d 0:0:0', self::get_date_end() );
			self::show_pageview_report( $args );
		}
		public static function get_date_begin() {
			if ( isset( $_GET['begin_month'] ) && isset( $_GET['begin_day'] ) && isset( $_GET['begin_year'] ) ) {
				$month  = intval( $_GET['begin_month'] );
				$day    = intval( $_GET['begin_day'] );
				$year   = intval( $_GET['begin_year'] );
				return mktime(0, 0, 0, $month, $day, $year );
			}
			return strtotime( '-1 month', self::get_date_end() );
		}
		public static function get_date_end() {
			if ( isset( $_GET['end_month'] ) && isset( $_GET['end_day'] ) && isset( $_GET['end_year'] ) ) {
				$month  = intval( $_GET['end_month'] );
				$day    = intval( $_GET['end_day'] );
				$year   = intval( $_GET['end_year'] );
			} else {
				$current_time = current_time( 'timestamp' );
				$month  = intval( date( 'm', $current_time ) );
				$day    = intval( date( 'd', $current_time ) );
				$year   = intval( date( 'Y', $current_time ) );
			}

			return mktime(0, 0, 0, $month, $day, $year );
		}

		/**
		*	
		*/
		public static function get_reports( $args = array() ) {

			global $wpdb;
			$table_name = $wpdb->prefix . self::TABLE_NAME;

			$current_timestamp = current_time( 'timestamp' );

			$args = wp_parse_args( $args, array(
				'date_begin'    => self::get_date_end(),
				'date_end'      => self::get_date_end()
			) );

			if ( is_null( $args['date_begin'] ) ) {
				$current_day = mktime( 0, 0, 0, date( 'm', $current_timestamp), date( 'd', $current_timestamp ), date( 'Y', $current_timestamp ) );
				$args['date_begin'] = date( 'Y-m-d 0:0:0', strtotime( '-1 month', $current_day ) );
			}
/*
			$user_args = array();
			if ( is_null( $args['author'] ) ) {
				$authors = get_users( $user_args );
			} else {
				$authors = array( get_userdata( intval( $args['author'] ) ) );
			}
*/

			$page_views		= array();

			$data	= $wpdb->get_results('SELECT * FROM ' . $table_name . ' WHERE `view_date` BETWEEN "' . $args['date_begin'] . '" AND "' . $args['date_end'] . '" ORDER BY `view_count` DESC');

			foreach ( $data as $page ) {
				$page_views[$page->post_id]['pTitle']		= get_the_title($page->post_id);
				$page_views[$page->post_id]['view_count']	= $page->view_count;
			}

			return $page_views;
		}
		public static function show_date_dropdown( $date, $prefix ) {
			$months = range( 1, 12 );
			$days   = range( 1, 31 );
			$years  = range( '2014', intval( date( 'Y', $date ) ) + 1 );

			$date_month = date( 'm', $date );
			$date_day   = date( 'd', $date );
			$date_year  = date( 'Y', $date );

			include plugin_dir_path( __FILE__ ) . 'views/date-dropdown.php';
		}
		
		/**
		*	EXPORT
		*/
		public static function export_csv_request() {
			if ( isset( $_GET['page'] ) && plugin_basename( __FILE__) == $_GET['page'] && isset( $_GET['export_csv'] ) ) {
				$args = array(
					'date_begin' => date( 'Y-m-d 0:0:0', self::get_date_begin() ),
					'date_end' => date( 'Y-m-d 0:0:0', self::get_date_end() )
				);
				self::export_csv( $args );
			}
		}

		public static function export_csv( $args = array() ) {
			$page_views = self::get_reports( $args );
			header( 'Content-type: text/csv' );
			header( 'Content-Disposition: attachment; filename=pageview-report-' . date( 'Y-m-d', current_time('timestamp') ) . '.csv' );
			//$content = "User ID,Name,Login,Email,Rate/Thousand,View Count,Payment\n";
			$content = "Post ID,Post Title,Views\n";

			foreach ( $page_views as $page_id => $page_details ) {
				$content .= $page_id . ',' .
				'"' . str_replace( '"', '""', $page_details['pTitle'] ) . '",' .
				'"' . str_replace( '"', '""', $page_details['view_count'] ) . "\",\n";;
			}
			echo $content;
			die();
		}

		/******************************
		*	Functions
		*/
		function get_popular_posts($number = 3){
			global $wpdb;
			$table_name = $wpdb->prefix . self::TABLE_NAME;

			$data	= $wpdb->get_results('SELECT * FROM `' . $table_name . '` WHERE 1 ORDER BY `view_count` DESC LIMIT ' . $number);

			foreach ( $data as $page) {
				$popular_posts[$page->post_id]['image']		= get_the_post_thumbnail($page->post_id, 'thumbnail');
				$popular_posts[$page->post_id]['title']		= get_the_title($page->post_id);
				$popular_posts[$page->post_id]['link']		= get_page_link($page->post_id);
				$popular_posts[$page->post_id]['count']		= $page->view_count;
			}

			return $popular_posts;
		}

		/******************************
		*	VIEWS
		*/
		public static function show_pageview_report( $args = array() ) {

			$page_views = self::get_reports( $args );

			include plugin_dir_path( __FILE__ ) . 'views/admin-report.php';
		}
	}
	
	new DNDCounter();
?>
