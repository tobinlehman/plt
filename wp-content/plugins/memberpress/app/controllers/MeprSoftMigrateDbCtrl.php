<?php
if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

class MeprSoftMigrateDbCtrl extends MeprBaseCtrl {
  public function load_hooks() {
    // Set a wp-cron
    add_filter( 'cron_schedules', array($this,'intervals') );

    if(self::should_schedule_worker()) {
      if( !wp_next_scheduled( 'mepr_migration_worker' ) ) {
        wp_schedule_event( time(), 'mepr_migration_interval', 'mepr_migration_worker' );
      }

      add_action( 'mepr_migration_worker', array($this,'migration_worker') );
    }
    else if(($timestamp = wp_next_scheduled('mepr_migration_worker'))) {
      wp_unschedule_event($timestamp, 'mepr_migration_worker');
    }

    //if( !wp_next_scheduled( 'mepr_members_worker' ) ) {
    //  wp_schedule_event( time(), 'mepr_members_interval', 'mepr_members_worker' );
    //}

    //add_action( 'mepr_members_worker', array($this,'members_worker') );

    //Update subscriptions
    add_action('mepr_subscription_saved', array($this,'update_subscription'));
    add_action('mepr_subscription_deleted', array($this,'delete_subscription'));

    //Update listing meta
    add_action('mepr_txn_store', array($this, 'update_txn_meta'));
    add_action('mepr_txn_destroy', array($this, 'update_txn_meta'));
    add_action('mepr_event_store', array($this, 'update_event_meta'));
    add_action('mepr_event_destroy', array($this, 'update_event_meta'));
    add_action('user_register', array($this, 'update_member_meta'));
    add_action('profile_update', array($this, 'update_member_meta'));
    add_action('delete_user', array($this, 'delete_member_meta'));

    // Small UI to check on Migration Progress
    add_action('mepr_activate_license_page', array($this, 'activate_license_page'));
  }

  public function intervals($schedules) {
    $schedules['mepr_migration_interval'] = array(
      'interval' => MeprUtils::minutes(5),
      'display' => __('MemberPress Database Migration Worker', 'memberpress')
    );

    //$schedules['mepr_members_interval'] = array(
    //  'interval' => MeprUtils::minutes(10),
    //  'display' => __('MemberPress Members Worker', 'memberpress')
    //);

    return $schedules;
  }

  public function migration_worker() {
    error_log('***** Trying Database Migration Worker');
    if(false === get_transient('mepr_already_in_soft_db_migration')) {
      set_transient('mepr_already_in_soft_db_migration',true,MeprUtils::hours(2)); // Lock it down bro

      $start_time = time();
      error_log('***** Starting Database Migration Worker');

      if(self::subscriptions_need_upgrade()) {
        error_log('***** Migrating 1000 Subscriptions');
        self::upgrade_subscriptions(null,true,1000);
      }
      else if(self::members_need_upgrade()) {
        error_log('***** Migrating 1000 Members');
        self::update_all_member_data(true,1000);
      }

      $duration = sprintf('%0.2f', ((time() - $start_time) / 60));
      error_log("***** Ending Database Migration Worker ({$duration} Mins)");

      delete_transient('mepr_already_in_soft_db_migration');
    }
    else {
      error_log('***** Oops Database Migration Worker already crunching');
    }
  }

/*
  public function members_worker() {
    $start_time = time();
    error_log('***** Starting members worker');

    self::update_all_member_data(true,50);

    $duration = sprintf('%0.2f', ((time() - $start_time) / 60));
    error_log("***** Ending members worker ({$duration} Mins)");
  }
*/

  // Sync subscription on create & update
  public function update_subscription($sub) {
    // Just delete first
    self::delete_subscription($sub->ID);
    self::upgrade_subscriptions($sub->ID);
  }

  // Sync subscrtipion on delete
  public function delete_subscription_from_hook($res, $args) {
    $id = $args['id'];
    return self::delete_subscription($id);
  }

  // Sync member on create & update
  // This is purely for performance ... we don't want to do these queries during a listing
  public function update_txn_meta($txn) {
    self::update_member_data($txn->user_id);
  }

  public function update_event_meta($evt) {
    if($evt->evt_id_type === MeprEvent::$users_str && $evt->event === MeprEvent::$login_event_str) {
      self::update_member_data($evt->evt_id);
    }
  }

  public function update_member_meta($user_id) {
    self::update_member_data($user_id);
  }

  // Sync member on delete
  public function delete_member_meta($user_id) {
    self::delete_member_data($user_id);
  }

// ******************************** MEMBER METHODS
  /*

  Member Data is statically stored, dynamic data which is acquired by utilizing the member_data static
  method. This will run some moderately expensive queries which will be cached in the members table
  so that the expensive queries can be run once, at the point when individual members are updated.
  Utilizing this approach reduces the strain on the server and increases performance because these
  queries are only run once when a user is updated and are usually only run for one member at a time.

  */
  public static function member_data($u=null) {
    global $wpdb;

    $mepr_db = new MeprDb();

    $first_txn_idq = $wpdb->prepare("(
        SELECT t.id
          FROM {$mepr_db->transactions} AS t
         WHERE t.user_id = u.ID
           AND t.status IN (%s, %s)
         ORDER BY t.created_at ASC
         LIMIT 1
      )",
      MeprTransaction::$complete_str,
      MeprTransaction::$confirmed_str
    );

    $latest_txn_idq = $wpdb->prepare("(
        SELECT t.id
          FROM {$mepr_db->transactions} AS t
         WHERE t.user_id = u.ID
           AND t.status IN (%s, %s)
         ORDER BY t.created_at DESC
         LIMIT 1
      )",
      MeprTransaction::$complete_str,
      MeprTransaction::$confirmed_str
    );

    $txn_countq = $wpdb->prepare("(
        SELECT COUNT(*)
          FROM {$mepr_db->transactions} AS t
         WHERE t.user_id=u.ID
           AND t.status IN (%s,%s)
      )",
      MeprTransaction::$complete_str,
      MeprTransaction::$confirmed_str
    );

    $expired_txn_countq = $wpdb->prepare("(
        SELECT COUNT(*)
          FROM {$mepr_db->transactions} AS t
         WHERE t.user_id = u.ID
           AND t.status IN (%s,%s)
           AND ( (
               t.expires_at IS NOT NULL
               AND t.expires_at <> %s
               AND t.expires_at < %s
             )
           )
      )",
      MeprTransaction::$complete_str,
      MeprTransaction::$confirmed_str,
      MeprUtils::mysql_lifetime(),
      MeprUtils::mysql_now()
    );

    $active_txn_countq = $wpdb->prepare("(
        SELECT COUNT(*)
          FROM {$mepr_db->transactions} AS t
         WHERE t.user_id=u.ID
           AND t.status IN (%s,%s)
           AND (
             t.expires_at IS NULL
             OR t.expires_at = %s
             OR t.expires_at > %s
           )
      )",
      MeprTransaction::$complete_str,
      MeprTransaction::$confirmed_str,
      MeprUtils::mysql_lifetime(),
      MeprUtils::mysql_now()
    );

    $sub_countq = "(
      SELECT COUNT(*)
        FROM {$mepr_db->subscriptions} AS s
       WHERE s.user_id=u.ID
    )";

    $subq = "(
      SELECT COUNT(*)
        FROM {$mepr_db->subscriptions} AS s
       WHERE s.user_id=u.ID
         AND s.status = %s
    )";

    $pending_sub_countq   = $wpdb->prepare($subq, MeprSubscription::$pending_str);
    $active_sub_countq    = $wpdb->prepare($subq, MeprSubscription::$active_str);
    $suspended_sub_countq = $wpdb->prepare($subq, MeprSubscription::$suspended_str);
    $cancelled_sub_countq = $wpdb->prepare($subq, MeprSubscription::$cancelled_str);

    $membershipsq = $wpdb->prepare("(
        SELECT GROUP_CONCAT(
                 DISTINCT t.product_id
                 ORDER BY t.product_id
                 SEPARATOR ','
               )
          FROM {$mepr_db->transactions} AS t
         WHERE t.user_id = u.ID
           AND (
             t.expires_at > %s
             OR t.expires_at = %s
             OR t.expires_at IS NULL
           )
           AND ( (
                t.txn_type IN (%s,%s)
                AND t.status=%s
             ) OR (
                t.txn_type=%s
                AND t.status=%s
             )
           )
      )",
      MeprUtils::mysql_now(),
      MeprUtils::mysql_lifetime(),
      MeprTransaction::$payment_str,
      'sub_account',
      MeprTransaction::$complete_str,
      MeprTransaction::$subscription_confirmation_str,
      MeprTransaction::$confirmed_str
    );

    $last_login_idq = $wpdb->prepare("(
        SELECT e.id
          FROM {$mepr_db->events} AS e
         WHERE e.evt_id=u.ID
           AND e.evt_id_type=%s
           AND e.event=%s
         ORDER BY e.created_at DESC
         LIMIT 1
      )",
      MeprEvent::$users_str,
      MeprEvent::$login_event_str
    );

    $login_countq = $wpdb->prepare("(
        SELECT COUNT(*)
          FROM {$mepr_db->events} AS e
         WHERE e.evt_id=u.ID
           AND e.evt_id_type=%s
           AND e.event=%s
      )",
      MeprEvent::$users_str,
      MeprEvent::$login_event_str
    );

    $total_spentq = $wpdb->prepare("(
        SELECT sum(t.total)
          FROM {$mepr_db->transactions} AS t
         WHERE t.user_id=u.ID
           AND t.status IN (%s,%s)
      )",
      MeprTransaction::$complete_str,
      MeprTransaction::$confirmed_str
    );

    $where = self::get_member_where($u);

    $q = "
      SELECT
        u.ID AS user_id,
        {$first_txn_idq} AS first_txn_id,
        {$latest_txn_idq} AS latest_txn_id,
        {$txn_countq} AS txn_count,
        {$expired_txn_countq} AS expired_txn_count,
        {$active_txn_countq} AS active_txn_count,
        {$sub_countq} AS sub_count,
        {$pending_sub_countq} AS pending_sub_count,
        {$active_sub_countq} AS active_sub_count,
        {$suspended_sub_countq} AS suspended_sub_count,
        {$cancelled_sub_countq} AS cancelled_sub_count,
        {$membershipsq} AS memberships,
        {$last_login_idq} AS last_login_id,
        {$login_countq} AS login_count,
        {$total_spentq} AS total_spent
      FROM {$wpdb->users} AS u
      {$where}
    ";

    return $wpdb->get_results($q);
  }

  public static function update_member_data($user_id, $delete_first=true) {
    global $wpdb;

    $mepr_db = new MeprDb();

    if($delete_first) {
      self::delete_member_data($user_id);
    }

    $data = self::member_data($user_id);

    foreach($data as $args) {
      $mepr_db->create_record($mepr_db->members, $args);
    }
  }

  public static function update_all_member_data($exclude_already_upgraded=false, $limit='') {
    global $wpdb;
    $mepr_db = new MeprDb();

    $q = "SELECT ID FROM {$wpdb->users}";

    if($exclude_already_upgraded) {
      $q .= " WHERE ID NOT IN (SELECT user_id FROM {$mepr_db->members})";
    }
    else {
      self::delete_all_member_data();
    }

    $q .= " ORDER BY ID DESC";

    if(!empty($limit)) {
      $q .= " LIMIT {$limit}";
    }

    $uids = $wpdb->get_col($q);

    foreach($uids as $uid) {
      self::update_member_data($uid,false);
    }
  }

  public static function delete_member_data($user_id) {
    global $wpdb;
    $mepr_db = new MeprDb();
    $q = $wpdb->prepare("DELETE FROM {$mepr_db->members} WHERE user_id=%s", $user_id);
    return $wpdb->query($q);
  }

  public static function delete_all_member_data() {
    global $wpdb;
    $mepr_db = new MeprDb();
    $q = "DELETE FROM {$mepr_db->members}";
    return $wpdb->query($q);
  }

  private static function get_member_where($u=null, $id_col='u.ID') {
    global $wpdb;

    $where = '';

    if(!is_null($u)) {
      if(is_array($u)) {
        $uids = implode(',',$u);
        $where = "
          WHERE {$id_col} IN ({$uids})
        ";
      }
      else {
        $where = $wpdb->prepare("
            WHERE {$id_col} = %d
          ",
          $u
        );
      }
    }

    return $where;
  }


// ******************************** SUBSCRIPTION METHODS

  // SPECIFICALLY TO USE IN MEPRDB TO MIGRATE SUBSCRIPTIONS TO IT'S NEW TABLE
  public static function upgrade_attrs() {
    return array(
      'subscr_id'           => 'mp-sub-'.uniqid(),
      'response'            => '',
      'gateway'             => 'manual',
      'user_id'             => 0,
      'ip_addr'             => $_SERVER['REMOTE_ADDR'],
      'product_id'          => 0,
      'coupon_id'           => 0,
      'price'               => 0.00,
      'total'               => "{{price}}",
      'period'              => 1,
      'period_type'         => 'months',
      'limit_cycles'        => false,
      'limit_cycles_num'    => 0,
      'limit_cycles_action' => null,
      'prorated_trial'      => false,
      'trial'               => false,
      'trial_days'          => 0,
      'trial_amount'        => 0.00,
      'status'              => MeprSubscription::$pending_str,
      'created_at'          => null,
      'tax_rate'            => 0.00,
      'tax_amount'          => 0.00,
      'tax_desc'            => '',
      'tax_class'           => 'standard',
      'cc_last4'            => null,
      'cc_exp_month'        => null,
      'cc_exp_year'         => null,
    );
  }

  private static function col_stmt($slug,$default) {
    global $wpdb;

    if(is_null($default)) {
      // A left join will naturally produce a NULL value if not found...no IFNULL needed
      $col = "pm_{$slug}.meta_value";
    }
    else if(is_integer($default)) {
      $col = $wpdb->prepare("IFNULL(pm_{$slug}.meta_value,%d)", $default);
    }
    else if(is_float($default)) {
      $col = $wpdb->prepare("IFNULL(pm_{$slug}.meta_value,%f)", $default);
    }
    else {
      $col = $wpdb->prepare("IFNULL(pm_{$slug}.meta_value,%s)", $default);
    }

    return $col;
  }

  // SPECIFICALLY TO USE IN MEPRDB TO MIGRATE SUBSCRIPTIONS TO IT'S NEW TABLE
  public static function upgrade_query($subscription_id=null, $exclude_already_upgraded=false, $limit='') {
    global $wpdb;

    $mepr_options = MeprOptions::fetch();
    $mepr_db      = new MeprDb();
    $cols = array('id' => 'DISTINCT pst.ID');

    // Add postmeta columns
    // Must be the same order and name as the table itself
    $pms = self::upgrade_attrs();
    foreach( $pms as $slug => $default ) {
      // Use value from another column
      if(preg_match("/^\{\{([^\{\}]*)\}\}$/",$default,$m)) {
        $cols[$slug] = "IFNULL(pm_{$slug}.meta_value," . self::col_stmt($m[1],$pms[$m[1]]) . ')';
      }
      else {
        $cols[$slug] = self::col_stmt($slug,$default);
      }
    }

    // The database can handle these
    //$cols['tax_compound'] = 0;
    //$cols['tax_shipping'] = 1;

    $args = array($wpdb->prepare('pst.post_type = %s', 'mepr-subscriptions'));

    // Don't upgrade any that are already upgraded
    if($exclude_already_upgraded) {
      $args[] = "pst.ID NOT IN (SELECT id FROM {$mepr_db->subscriptions})";
    }

    if(!is_null($subscription_id)) {
      $args[] = $wpdb->prepare('pst.ID = %d', $subscription_id);
    }

    $joins = array();
    //$ignore_cols = array('tax_compound','tax_shipping');

    // Add postmeta joins
    foreach( $pms as $slug => $default ) {
      //if(!in_array($slug, $ignore_cols)) {
        $joins[] = self::join_pm($slug, 'LEFT JOIN');
      //}
    }

    if($limit===false) {
      $paged='';
      $perpage=0;
    }
    else {
      $paged=1;
      $perpage=$limit;
    }

    return MeprDb::list_table($cols, "{$wpdb->posts} AS pst", $joins,
                              $args, '', '', $paged, '',
                              $perpage, false, true);
  }

  public static function upgrade_subscriptions($subscription_id=null, $exclude_already_upgraded=false, $limit='') {
    global $wpdb;

    $mepr_db = new MeprDb();

    $subq = self::upgrade_query($subscription_id,$exclude_already_upgraded,$limit);
    $attrs = 'id,'.implode(',',array_keys(self::upgrade_attrs()));

    $subq = "INSERT IGNORE INTO {$mepr_db->subscriptions} ({$attrs}) {$subq['query']}";
    //error_log($subq);
    $res = $wpdb->query($subq);

    if(is_wp_error($res)) {
      throw new MeprDbMigrationException(sprintf(__('MemberPress database migration failed: %1$s %2$s', 'memberpress'),$res->get_error_message(),$subq));
    }
  }

  public static function delete_subscription($id) {
    global $wpdb;
    $mepr_db = new MeprDb();
    $q = $wpdb->prepare("DELETE FROM {$mepr_db->subscriptions} WHERE id=%d",$id);
    return $wpdb->query($q);
  }

  // STILL USING THIS TO MIGRATE THE DATABASE
  private static function join_pm( $slug, $join='LEFT JOIN', $post='pst' ) {
    global $wpdb;
    $vals = self::legacy_str_vals();

    $class = new ReflectionClass( 'MeprSubscription' );
    $val = $vals[$slug];

    return $wpdb->prepare( "{$join} {$wpdb->postmeta} AS pm_{$slug}
                                 ON pm_{$slug}.post_id = {$post}.ID
                                AND pm_{$slug}.meta_key = %s", $val );
  }

  // STILL USING THIS TO MIGRATE THE DATABASE
  private static function legacy_str_vals() {
    return array(
      'subscr_id'           => '_mepr_subscr_id',
      'response'            => '_mepr_subscr_response',
      'user_id'             => '_mepr_subscr_user_id',
      'gateway'             => '_mepr_subscr_gateway',
      'ip_addr'             => '_mepr_subscr_ip_addr',
      'product_id'          => '_mepr_subscr_product_id',
      'coupon_id'           => '_mepr_subscr_coupon_id',
      'price'               => '_mepr_subscr_price',
      'period'              => '_mepr_subscr_period',
      'period_type'         => '_mepr_subscr_period_type',
      'limit_cycles'        => '_mepr_subscr_limit_cycles',
      'limit_cycles_num'    => '_mepr_subscr_limit_cycles_num',
      'limit_cycles_action' => '_mepr_subscr_limit_cycles_action',
      'prorated_trial'      => '_mepr_subscr_prorated_trial',
      'trial'               => '_mepr_subscr_trial',
      'trial_days'          => '_mepr_subscr_trial_days',
      'trial_amount'        => '_mepr_subscr_trial_amount',
      'status'              => '_mepr_subscr_status',
      'created_at'          => '_mepr_subscr_created_at',
      'cc_last4'            => '_mepr_subscr_cc_last4',
      'cc_exp_month'        => '_mepr_subscr_cc_month_exp',
      'cc_exp_year'         => '_mepr_subscr_cc_year_exp',
      'total'               => '_mepr_subscr_total',
      'tax_rate'            => '_mepr_subscr_tax_rate',
      'tax_amount'          => '_mepr_subscr_tax_amount',
      'tax_desc'            => '_mepr_subscr_tax_desc',
      'tax_class'           => '_mepr_subscr_tax_class',
      'cpt'                 => 'mepr-subscriptions',
    );
  }

  private static function tables_exist() {
    $mepr_db = new MeprDb();

    return (
      $mepr_db->table_exists($mepr_db->members) &&
      $mepr_db->table_exists($mepr_db->subscriptions)
    );
  }

  private static function should_schedule_worker() {
    $mepr_db = new MeprDb();

    $tables_exist = self::tables_exist();

    return (
      $tables_exist &&
      ( self::subscriptions_need_upgrade() ||
        self::members_need_upgrade()
      )
    );
  }

  private static function subscriptions_need_upgrade() {
    $progress = self::subscriptions_progress();
    return ($progress < 100);
  }

  private static function members_need_upgrade() {
    $progress = self::members_progress();
    return ($progress < 100);
  }

  private static function subscriptions_progress() {
    $scounts = self::subscriptions_counts();
    extract($scounts);
    if($old_subs <= 0) {
      return 100;
    }
    else {
      return (int)(($new_subs / $old_subs) * 100);
    }
  }

  private static function members_progress() {
    $mcounts = self::members_counts();
    extract($mcounts);
    if($users <= 0) {
      return 100;
    }
    else {
      return (int)(($members / $users) * 100);
    }
  }

  private static function subscriptions_counts() {
    global $wpdb;
    $mepr_db = new MeprDb();

    $q = $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type=%s", 'mepr-subscriptions');
    $old_subs = $wpdb->get_var($q);

    $q = "SELECT COUNT(*) FROM {$mepr_db->subscriptions}";
    $new_subs = $wpdb->get_var($q);

    return compact('old_subs', 'new_subs');
  }

  private static function members_counts() {
    global $wpdb;
    $mepr_db = new MeprDb();

    $q = "SELECT COUNT(*) FROM {$wpdb->users}";
    $users = $wpdb->get_var($q);

    $q = "SELECT COUNT(*) FROM {$mepr_db->members}";
    $members = $wpdb->get_var($q);

    return compact('users','members');
  }

  public function activate_license_page() {
    if(isset($_GET['db-status']) && self::tables_exist()) {
      $mprogress = self::members_progress();
      $sprogress = self::subscriptions_progress();

      $mcount = self::members_counts();
      $scount = self::subscriptions_counts();

      extract($mcount);
      extract($scount);

      ?>
      <br/>
      <h3><?php _e('Database Upgrade Progress', 'memberpress'); ?></h3>
      <table>
        <tr><td><strong><?php _e('Members', 'memberpress'); ?></strong></td><td><?php echo "{$mprogress}% ({$members} / {$users})"; ?></td></tr>
        <tr><td><strong><?php _e('Subscriptions', 'memberpress'); ?></strong></td><td><?php echo "{$sprogress}% ({$new_subs} / {$old_subs})"; ?></td></tr>
      </table>
      <br/>
      <?php
    }
  }

} //End class

