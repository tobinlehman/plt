<?php

/*
 * Service class for Workflow Reports
 *
 * @copyright   Copyright (c) 2015, Nugget Solutions, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.3
 *
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
   exit;

/*
 * OW_Report_Service Class
 *
 * @since 4.2
 */

class OW_Report_Service {
   
   /*
	 * Set things up.
	 *
	 * @since 4.2
	 */
	public function __construct() {
		
	}
   
   /*
	 * generate the table header for the Current Assignment Report page
	 *
	 * @return mixed HTML for Current Assignment Report page
	 *
	 * @since 4.2
	 */   
   public function get_current_assigment_table_header() {
      $sortby = ( isset( $_GET[ 'order' ] ) && sanitize_text_field( $_GET[ "order" ] ) == "desc" ) ? "asc" : "desc";
      $post_order_class = $wf_name_class = $due_date_class = '';
      if ( isset( $_GET[ 'orderby' ] ) && isset( $_GET[ 'order' ] ) ) {
         $orderby = sanitize_text_field( $_GET[ 'orderby' ] );
         switch ( $orderby ) {
            case 'post_title':
               $post_order_class = $sortby;
               break;
            case 'wf_name':
               $wf_name_class = $sortby;
               break;
            case 'due_date':
               $due_date_class = $sortby;
               break;
         }
      }

      $option = get_option( 'oasiswf_custom_workflow_terminology' );
      $due_date_title = ! empty( $option[ 'dueDateText' ] ) ? $option[ 'dueDateText' ] : __( 'Due Date', 'oasisworkflow' );

      echo "<tr>";

      echo "<th>" . __( "Assigned User", "oasisworkflow" ) . "</th>";

      $sorting_args = add_query_arg( array( 'orderby' => 'post_title', 'order' => $sortby ) );
      echo "<th width='300px' scope='col' class='sorted $post_order_class'>
                        <a href='$sorting_args'>
                        <span>" . __( "Post/Page", "oasisworkflow" ) . "</span>
                        <span class='sorting-indicator'></span>
                        </a>
                     </th>";

      $sorting_args = add_query_arg( array( 'orderby' => 'wf_name', 'order' => $sortby ) );
      echo "<th width='200px' scope='col' class='sorted $wf_name_class'>
                        <a href='$sorting_args'>
                        <span>" . __( "Workflow [Step]", "oasisworkflow" ) . "</span>
                        <span class='sorting-indicator'></span>
                        </a>
                     </th>";

      echo "<th>" . __( "Status", "oasisworkflow" ) . "</th>";

      $sorting_args = add_query_arg( array( 'orderby' => 'due_date', 'order' => $sortby ) );
      echo "<th scope='col' class='sorted $due_date_class'>
                        <a href='$sorting_args'>
                        <span>" . $due_date_title . "</span>
                        <span class='sorting-indicator'></span>
                        </a>
                     </th>";
      echo "</tr>";
   }
   
   /*
	 * generate the table header for Submission Report page 
	 *
	 * @return mixed HTML for Submission Report page
	 *
	 * @since 4.2
	 */   
   
   public function get_submission_report_table_header( $action ) {
      // sanitize data
      $report_action = sanitize_text_field( $action );
      
      echo "<tr>";
      if ( $report_action == 'in-workflow' ) {
         echo "<td scope='col' class='manage-column column-cb check-column'><input type='checkbox' name='abort-all'  /></td>";
      }
      echo "<th width='300px'>" . __( "Title" ) . "</th>";
      echo "<th class='report-header'>" . __( "Type" ) . "</th>";
      echo "<th class='report-header'>" . __( "Author" ) . "</th>";
      echo "<th class='report-header'>" . __( "Date" ) . "</th>";
      echo "</tr>";
   }
   
  
   /*
    * get the assigned posts to a Current Assignment Report
    *
    * @param int|null $post_id
    * @param int|null $user_id
    * @param mixed $return_format it could be rows or just a single row
    *
    * @since 4.2
    */
   
   public function get_assigned_post_to_report( $post_id = null, $user_id = null, $return_format = "rows" ) {
      global $wpdb;

      if ( ! empty( $post_id ) ) {
         $post_id = intval( $post_id );
      }

      if ( ! empty( $user_id ) ) {
         $user_id = intval( $user_id );
      }

      // use white list approach to set order by clause
      $order_by = array(
          'post_title' => 'post_title',
          'post_type' => 'post_type',
          'post_author' => 'post_author',
          'due_date' => 'due_date',
          'wf_name' => 'wf_name'
      );

      $sort_order = array(
          'asc' => 'ASC',
          'desc' => 'DESC',
      );

      // default order by
      $order_by_column = " ORDER BY A.due_date, posts.post_title"; // default order by column
      // if user provided any order by and order input, use that
      if ( isset( $_GET['orderby'] ) && $_GET['orderby'] ) {
         // sanitize data
         $user_provided_order_by = sanitize_text_field( $_GET['orderby'] );
         $user_provided_order = sanitize_text_field( $_GET['order'] );
         if ( array_key_exists( $user_provided_order_by, $order_by ) ) {
            $order_by_column = " ORDER BY " . $order_by[$user_provided_order_by] . " " . $sort_order[$user_provided_order];
         }
      }

      $sql = "SELECT A.*, B.review_status, B.actor_id,
      			B.next_assign_actors, B.step_id as review_step_id, B.action_history_id,C.workflow_id, C.wf_name, C.wf_version, C.step_info, posts.post_title, users.display_name as post_author, posts.post_type, posts.post_date
      			FROM " . OW_Utility::instance()->get_action_history_table_name() . " A
      			LEFT OUTER JOIN  " . OW_Utility::instance()->get_action_table_name() . " B ON A.ID = B.action_history_id
      			AND B.review_status = 'assignment'
					LEFT JOIN {$wpdb->posts} AS posts ON posts.ID = A.post_id
					LEFT JOIN {$wpdb->base_prefix}users AS users ON users.ID = posts.post_author
					LEFT JOIN (SELECT AA.*, BB.name as wf_name, BB.version as wf_version FROM " . OW_Utility::instance()->get_workflow_steps_table_name() . " AS AA LEFT JOIN " . OW_Utility::instance()->get_workflows_table_name() . " AS BB ON AA.workflow_id = BB.ID) AS C
					ON A.step_id = C.ID WHERE 1=1 AND A.action_status = 'assignment'";

      // generate the where clause and get the results
      if ( $post_id ) {
         $where_clause = "AND (assign_actor_id = %d OR actor_id = %d) AND A.post_id = %d " . $order_by_column;
         if ( $return_format == "rows" ) {
            $result = $wpdb->get_results( $wpdb->prepare( $sql . $where_clause, $user_id, $user_id, $post_id ) );
         } else {
            $result = $wpdb->get_row( $wpdb->prepare( $sql . $where_clause, $user_id, $user_id, $post_id ) );
         }
      } elseif ( isset( $user_id ) ) {
         $where_clause = "AND assign_actor_id = %d OR actor_id = %d  " . $order_by_column;
         if ( $return_format == "rows" ) {
            $result = $wpdb->get_results( $wpdb->prepare( $sql . $where_clause, $user_id, $user_id ) );
         } else {
            $result = $wpdb->get_row( $wpdb->prepare( $sql . $where_clause, $user_id, $user_id ) );
         }
      } else {
         $where_clause = $order_by_column;
         if ( $return_format == "rows" ) {
            $result = $wpdb->get_results( $sql . $where_clause );
         } else {
            $result = $wpdb->get_row( $sql . $where_clause );
         }
      }

      return $result;
   }

}

// construct an instance so that the actions get loaded
$ow_report_service = new OW_Report_Service();